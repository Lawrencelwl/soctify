<?php
ini_set('max_execution_time', 0);
header('Content-Type: text/event-stream');
header('Cache-Control: no-store no-cache');
header('X-Accel-Buffering: no');
class ChatController
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

    public function check_followed($follower_id, $following_id)
    {
        $query = "SELECT following_id, follower_id
        FROM follows
        WHERE following_id = ? AND follower_id = ? 
        OR follower_id = ? AND following_id = ?;";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("ssss", $follower_id, $following_id, $follower_id, $following_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 2) {
            return true;
        } else {
            return false;
        }
    }

    public function check_followed_oneway($follower_id, $following_id, $type)
    {
        $query = "SELECT following_id, follower_id
        FROM follows
        WHERE follower_id = ? AND following_id = ?;";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("ss", $follower_id, $following_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($type == "add") {
            if ($result->num_rows == 1) {
                return false;
            } else {
                return true;
            }
        } else if ($type == "del") {
            if ($result->num_rows != 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function add_follow()
    {
        $uuid = $this->guidv4();
        $user_id = $_SESSION['user_id'];
        $following_id = $_POST['following_id'];
        if ($this->check_followed_oneway($user_id, $following_id, "add")) {
            $query = "INSERT INTO follows (id, follower_id, following_id) VALUES (?, ?, ?)";
            $stmt = $this->mysqli->prepare($query);
            $stmt->bind_param("sss", $uuid, $user_id, $following_id);
            $stmt->execute();
            echo "followed";
        } else {
            http_response_code(500); // Internal Server Error
            echo "Internet error, please try again";
        }
    }

    public function del_follow()
    {
        $user_id = $_SESSION['user_id'];
        $following_id = $_POST['following_id'];
        if ($this->check_followed_oneway($user_id, $following_id, "del")) {
            $query = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
            $stmt = $this->mysqli->prepare($query);
            $stmt->bind_param("ss", $user_id, $following_id);
            $stmt->execute();
            echo "unfollowed";
        } else {
            http_response_code(500); // Internal Server Error
            echo "Internet error, please try again";
        }
    }

    public function show_followed()
    {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT f.*, u.username, u.profile_picture_url
            FROM follows f
            INNER JOIN users u ON f.following_id = u.id
            WHERE f.follower_id = '$user_id';";
        $result = $this->mysqli->query($query);
        $followed = [];
        while ($row = $result->fetch_assoc()) {
            if ($this->check_existed_chatroom($user_id, $row['following_id'])) {
                if ($this->check_followed($row['follower_id'], $row['following_id'])) {
                    $followed[] = [
                        "follow_id" => $row['id'],
                        "follower_id" => $row['follower_id'],
                        "following_id" => $row['following_id'],
                        "user_name" => $row['username'],
                        "avatar" => $row['profile_picture_url']
                    ];
                }
            }
        }
        echo json_encode($followed);
    }


    public function check_existed_chatroom($user_id, $chating_id)
    {
        $query = "SELECT user_id, chating_id
        FROM chatrooms
        WHERE user_id = ? AND chating_id = ? 
        OR user_id = ? AND chating_id = ?;";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("ssss", $user_id, $chating_id, $chating_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function create_chatroom()
    {
        $uuid = $this->guidv4();
        $user_id = $_SESSION['user_id'];
        $chating_id = $_POST['chating_id'];
        if ($this->check_existed_chatroom($user_id, $chating_id)) {
            $query = "INSERT INTO chatrooms (id, user_id, chating_id) VALUES (?, ?, ?)";
            $stmt = $this->mysqli->prepare($query);
            $stmt->bind_param("sss", $uuid, $user_id, $chating_id);
            $stmt->execute();
        } else {
            http_response_code(500); // Internal Server Error
            echo "Internet error, please try again";
        }
    }

    public function load_chatroom()
    {
        $user_id = $_SESSION['user_id'];
        // $user_id = '55470f96-7d33-418a-afb7-d986953594da';
        $query = "SELECT c.*, u1.username AS user_username, u1.profile_picture_url AS user_avatar , u2.username AS chating_username, u2.profile_picture_url AS chating_avatar
        FROM chatrooms c
        INNER JOIN users u1 ON c.user_id = u1.id
        INNER JOIN users u2 ON c.chating_id = u2.id
        WHERE c.user_id = '$user_id' 
        OR c.chating_id = '$user_id';
        ";
        $result = $this->mysqli->query($query);
        $chatrooms = [];
        while ($row = $result->fetch_assoc()) {
            $chatrooms[] = [
                "chatroom_id" => $row['id'],
                "user_id" => $row['user_id'],
                "chating_id" => $row['chating_id'],
                "user_username" => $row['user_username'],
                "chating_username" => $row['chating_username'],
                "user_avatar" => $row['user_avatar'],
                "chating_avatar" => $row['chating_avatar'],
                "created_at" => $row['created_at'],
                "updated_at" => $row['updated_at']
            ];
        }
        echo json_encode($chatrooms);
    }

    public function send_message()
    {
        $uuid = $this->guidv4();
        $user_id = $_SESSION['user_id'];
        $chatroom_id = $_POST['chatroom_id'];
        $message = $_POST['message'];
        $query = "INSERT INTO message (id, chatroom_id, sender_id, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("ssss", $uuid, $chatroom_id, $user_id, $message);
        $stmt->execute();
        echo $uuid;
    }

    public function connect_chatroom()
    {
        $chatroom_id = $_SESSION['chatroom_id'];
        $instruction = $_SESSION['chatroom_instruction'];
        $query = '';
        if ($instruction == 'wait') {
            return;
        } else if ($instruction == "new") {
            $query = "SELECT m.*, u.profile_picture_url, u.username FROM message m JOIN users u ON m.sender_id = u.id WHERE chatroom_id = ? ORDER BY created_at ASC;";
            $_SESSION['chatroom_instruction'] = "sendNewMessage";
        } else {
            $query = "SELECT m.*, u.profile_picture_url, u.username FROM message m JOIN users u ON m.sender_id = u.id WHERE chatroom_id = ? AND m.created_at >= now() - interval 5 SECOND ORDER BY created_at ASC;";
        }
        // $query = "SELECT * FROM message WHERE chatroom_id = '$chatroom_id'";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $chatroom_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array();
        $message = array();
        while ($row = $result->fetch_assoc()) {
            $message[] = [
                "message_id" => $row['id'],
                "chatroom_id" => $row['chatroom_id'],
                "sender_id" => $row['sender_id'],
                "message" => $row['message'],
                "date" => $row['created_at'],
                "user_avatar" => $row['profile_picture_url'],
                "username" => $row['username']
            ];
        }
        $data = array(
            'message' => $message,
            'date' => date('Y-m-d H:i:s')
        );
        echo "id: \ndata: " . json_encode($data) . "\n";
        // echo "retry: 1000\n";
        echo "\n\n";
        ob_flush();
        flush();
        // }
    }

    public function set_chatroom()
    {
        $chatroom_id = $_POST['chatroom_id'];
        $instruction = $_POST['instruction'];
        // $_SESSION['chatroom_id'] = $chatroom_id;
        // $_SESSION['chatroom_instruction'] = "new";
        // echo $_SESSION['chatroom_id'];
        if ($instruction == " leaveChatRoom") {
            //kill session
            $_SESSION['chatroom_id'] = "";
            $_SESSION['chatroom_instruction'] = "wait";
            echo "kill";
        } else if ($instruction == "sendNewMessage") {
            $_SESSION['chatroom_id'] = $chatroom_id;
            $_SESSION['chatroom_instruction'] = "sendNewMessage";
            echo "send";
        } else {
            $_SESSION['chatroom_id'] = $chatroom_id;
            $_SESSION['chatroom_instruction'] = "new";
            echo "new";
        }
    }
}