<?php
class PostController
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function post()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        $content = $_POST['content'] ?? null;
        $have_media = filter_var($_POST['haveMedia'] ?? null, FILTER_VALIDATE_BOOLEAN);
        $mediaName = null;
        $media_type = null;
        $signUrl = null;

        if ($have_media) {
            $media = $_FILES['media'] ?? null;
            if ($media) {
                $mediaName = $media['name'];
                $media_type = pathinfo($mediaName, PATHINFO_EXTENSION);
                $s3 = new S3Controller();
                $result = $s3->upload_file($media_type, $media['tmp_name']);

                if (!$result) {
                    http_response_code(500); // Internal Server Error
                    echo ("Internet error, Please try again");
                    return;
                }

                $mediaName = $result["mediaName"];
                $signUrl = $result["signedUrl"];
                $allowed_image_extensions = ['jpg', 'png', 'jpeg', 'gif'];
                $allowed_video_extensions = ['mp4', 'mov', 'avi', 'wmv'];

                if (in_array($media_type, $allowed_image_extensions)) {
                    $media_type = "image";
                } else if (in_array($media_type, $allowed_video_extensions)) {
                    $media_type = "video";
                }
            }
        }

        if (!$user_id) {
            http_response_code(400); // Bad Request
            echo ("Invalid user or content");
            return;
        }

        $uuid = $this->guidv4();
        $query = "INSERT INTO `posts` (`id`, `user_id`, `caption`, `media_name`, `type`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("sssss", $uuid, $user_id, $content, $mediaName, $media_type);
        $result = $stmt->execute();
        $stmt->close();

        if (!$result) {
            http_response_code(500); // Internal Server Error
            echo ("Registration failed, Please try again");
            return;
        }

        // Registration successful...
        $post_data = [
            "post_id" => $uuid,
            "user_id" => $user_id,
            "caption" => $content,
            "media_name" => $mediaName,
            "media_url" => $signUrl,
            "type" => $media_type,
            "username" => $_SESSION['username'] ?? null,
            "profile_picture_url" => $_SESSION['avatar_url'] ?? null,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s"),
            "likes" => 0,
            "comments" => 0,
            "comments_array" => [],
            "liked" => false
        ];
        echo json_encode($post_data);
    }

    public function remove_post()
    {
        $post_id = $_POST['post_id'];

        // Use prepared statements to prevent SQL injection
        $query = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("i", $post_id); // Bind the parameter as an integer
        $result = $stmt->execute();
        $stmt->close();

        if (!$result) {
            http_response_code(500); // Internal Server Error
            echo "Deletion failed. Please try again.";
            return;
        }

        echo "success";
    }

    public function load_posts()
    {
        if (!isset($_POST['offset'], $_POST['limit'])) {
            $offset = 0;
            $limit = 10;
        } else {
            $offset = (int) $_POST['offset'];
            $limit = (int) $_POST['limit'];
        }

        $query = "SELECT p.id AS post_id, p.user_id, p.caption, p.media_name, p.type, 
                    u.username, u.profile_picture_url, p.created_at, p.updated_at,
                    COUNT(DISTINCT l.id) AS likes, 
                    COUNT(DISTINCT c.id) AS comments, 
                    EXISTS (SELECT * FROM likes WHERE user_id = ? AND post_id = p.id) AS liked 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                LEFT JOIN likes l ON p.id = l.post_id 
                LEFT JOIN comments c ON p.id = c.post_id 
                LEFT JOIN likes l2 ON p.id = l2.post_id AND l2.user_id = p.user_id 
                GROUP BY p.id 
                ORDER BY p.created_at DESC
                LIMIT ?, ?;
                ";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('iii', $_SESSION['user_id'], $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = array();
        $s3 = new S3Controller();

        $commentQuery = "SELECT c.id AS comment_id, c.user_id, c.post_id, c.comment, c.created_at, c.updated_at, 
                        u.username, u.profile_picture_url 
                    FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.post_id = ? 
                    ORDER BY c.created_at DESC
                    LIMIT ?, ?;
                    ";
        $stmt2 = $this->mysqli->prepare($commentQuery);

        while ($row = $result->fetch_assoc()) {
            if ($row['media_name'] !== null && $row['media_name'] !== "") {
                $row['media_url'] = $s3->get_file($row['media_name']);
            }
            $row['liked'] = ($row['liked'] == 1) ? true : false;

            if ($row['comments'] != 0) {
                $stmt2->bind_param('sii', $row['post_id'], $offset, $limit);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $comments = array();
                while ($row2 = $result2->fetch_assoc()) {
                    $row2['avatar'] = $row2['profile_picture_url'];
                    $comments[] = $row2;
                }
                $row['comments_array'] = $comments;
            } else {
                $row['comments_array'] = [];
            }
            $posts[] = $row;
        }

        echo json_encode($posts);
    }

    public function load_target_posts($username = null)
    {
        // Get the username from input or $_POST
        $user_name = $username ?? $_POST['username'];

        // Get offset and limit from $_POST or set default values
        $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
        $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;

        $query = "SELECT p.id AS post_id, p.user_id, p.caption, p.media_name, p.type, 
                    u.username, u.profile_picture_url, p.created_at, p.updated_at,
                    COUNT(DISTINCT l.id) AS likes, 
                    COUNT(DISTINCT c.id) AS comments, 
                    EXISTS (SELECT * FROM likes WHERE user_id = ? AND post_id = p.id) AS liked 
                FROM posts p 
                JOIN (
                  SELECT id, username, profile_picture_url FROM users WHERE username = ?
                ) u ON p.user_id = u.id 
                LEFT JOIN likes l ON p.id = l.post_id 
                LEFT JOIN comments c ON p.id = c.post_id 
                LEFT JOIN likes l2 ON p.id = l2.post_id AND l2.user_id = p.user_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ?, ?;
                ";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ssii', $_SESSION['user_id'], $user_name, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = array();
        $s3 = new S3Controller();
        while ($row = $result->fetch_assoc()) {
            if ($row['media_name'] !== null && $row['media_name'] !== "") {
                $row['media_url'] = $s3->get_file($row['media_name']);
            }
            // Convert liked value to boolean
            $row['liked'] = $row['liked'] == 1 ? true : false;

            if ($row['comments'] != 0) {
                $query2 = "SELECT c.id AS comment_id, c.user_id, c.post_id, c.comment, c.created_at, c.updated_at, 
                        u.username, u.profile_picture_url 
                    FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.post_id = ? 
                    ORDER BY c.created_at DESC
                    LIMIT ?, ?;
                    ";
                $stmt2 = $this->mysqli->prepare($query2);
                $stmt2->bind_param('sii', $row['post_id'], $offset, $limit);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $comments = array();
                while ($row2 = $result2->fetch_assoc()) {
                    $row2['avatar'] = $row2['profile_picture_url'];
                    $comments[] = $row2;
                }
                $row['comments_array'] = $comments;
            } else {
                $row['comments_array'] = [];
            }
            $posts[] = $row;
        }
        // Return JSON encoded response
        echo json_encode($posts);
    }

    public function comments()
    {
        $uuid = $this->guidv4();
        $user_id = $_SESSION['user_id'] ?? null;
        $post_id = $_POST['post_id'] ?? null;
        $comment = $_POST['comment'] ?? null;
        $username = null;
        $profile_picture_url = null;
        $comment_count = null;

        if (!$user_id || !$post_id || !$comment) {
            http_response_code(400); // Bad Request
            echo "Missing required parameters.";
            return;
        }

        $stmt_insert = $this->mysqli->prepare("INSERT INTO comments (id, user_id, post_id, comment) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $uuid, $user_id, $post_id, $comment);
        $stmt_insert->execute();
        $stmt_insert->close();

        if ($this->mysqli->affected_rows === 0) {
            http_response_code(500); // Internal Server Error
            echo "Database error, please try again.";
            return;
        }

        $stmt_select_count = $this->mysqli->prepare("SELECT COUNT(*) FROM `comments` WHERE `post_id` = ?;");
        $stmt_select_count->bind_param("s", $post_id);
        $stmt_select_count->execute();
        $stmt_select_count->bind_result($comment_count);
        $stmt_select_count->fetch();
        $stmt_select_count->close();
        $count_comment = $comment_count;

        $stmt_select = $this->mysqli->prepare(" SELECT u.username, u.profile_picture_url, COUNT(c.id) as comment_count
                                                FROM users u
                                                INNER JOIN comments c ON u.id = c.user_id
                                                WHERE c.post_id = ? AND c.user_id = ?
                                                GROUP BY u.id;");
        $stmt_select->bind_param("ss", $post_id, $user_id);
        $stmt_select->execute();
        $stmt_select->bind_result($username, $profile_picture_url, $comment_count);

        if ($stmt_select->fetch()) {
            $stmt_select->close();

            $command_data = [
                "command_id" => $uuid,
                "user_id" => $user_id,
                "username" => $username,
                "avatar" => $profile_picture_url,
                "post_id" => $post_id,
                "comment" => $comment,
                "comment_count" => $count_comment,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
            ];

            echo json_encode($command_data);
        } else {
            $stmt_select->close();
            http_response_code(500); // Internal Server Error
            echo "Database error, please try again.";
        }
    }

    public function like()
    {
        $uuid = $this->guidv4();
        $user_id = $_SESSION['user_id'] ?? null;
        $post_id = $_POST['post_id'] ?? null;

        $query = "INSERT INTO `likes` (`id`, `user_id`, `post_id`) VALUES (?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("sss", $uuid, $user_id, $post_id);

        if ($stmt->execute()) {
            $stmt->close();

            $query = "SELECT COUNT(*) AS `like_count` FROM `likes` WHERE `post_id` = ?";
            $stmt = $this->mysqli->prepare($query);
            $stmt->bind_param("s", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $like_count = $result->fetch_assoc()['like_count'];
            echo (int) $like_count;
        } else {
            $stmt->close();

            http_response_code(500); // Internal Server Error
            echo ("Internet error, Please try again");
        }
    }

    public function dislike()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        $post_id = $_POST['post_id'] ?? null;
        $query = "DELETE FROM `likes` WHERE `user_id` = ? AND `post_id` = ?";
        $stmt = $this->mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $post_id); // Bind parameters to prevent SQL injection
            if ($stmt->execute()) {
                $query = "SELECT COUNT(*) as like_count FROM `likes` WHERE `post_id` = ?"; // Use COUNT(*) to get the like count directly from the database
                $stmt = $this->mysqli->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("i", $post_id); // Bind parameter
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $like_count = $row['like_count']; // Retrieve like count from result
                    echo (int) $like_count;
                } else {
                    http_response_code(500); // Internal Server Error
                    echo ("Internet error, Please try again");
                }
            } else {
                http_response_code(500); // Internal Server Error
                echo ("Internet error, Please try again");
            }
            $stmt->close();
        } else {
            http_response_code(500); // Internal Server Error
            echo ("Internet error, Please try again");
        }
    }
}
?>