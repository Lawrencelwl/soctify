<?php
require __DIR__ . '/S3Controller.php';
class AuthController
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

  public function profile_update()
  {
    $password = $_POST['password'];
    $check_pw = $this->get_password($password);
    if ($check_pw) {
      echo ("Password is same as old password");
      return;
    }
    $user_name = $_POST['username'];
    $check_name = $this->get_username($user_name);
    if ($check_name) {
      echo ("Username already exists");
      return;
    }
    $user_id = $_SESSION['user_id'];
    $have_avatar = filter_var($_POST['haveAvatar'], FILTER_VALIDATE_BOOLEAN);
    $avatar_name = null;
    $avatar_type = null;
    $avatar_url = null;
    if ($have_avatar) {
      $avatar_name = $_FILES['avatar']['name'];
      $avatar_type = pathinfo($avatar_name, PATHINFO_EXTENSION);
      if ($avatar_type == "jpg" || $avatar_type == "png" || $avatar_type == "jpeg" || $avatar_type == "gif") {
        $media_type = "image";
      } else if ($avatar_type == "mp4" || $avatar_type == "mov" || $avatar_type == "avi" || $avatar_type == "wmv") {
        $media_type = "video";
      }
      $s3 = new S3Controller();
      $result = $s3->upload_public_file($avatar_name, $_FILES["avatar"]['tmp_name']);
      if ($result) {
        $avatar_url = $result;
      } else {
        http_response_code(500); // Internal Server Error
      }
    }
    $update_head = "UPDATE `users` SET";
    $set_user_name = "";
    $set_password = "";
    $set_bio = ", `bio` = null";
    $set_avatar = "";
    if ($user_name != null || $user_name != "") {
      $_SESSION['username'] = $user_name;
      $set_user_name .= " `username` = '$user_name'";
    }
    if ($password != null || $password != "") {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $set_password .= " ,`password` = '$hashed_password'";
    }
    if ($avatar_name != null || $avatar_name != "") {
      $_SESSION['avatar_url'] = $avatar_url;
      $set_avatar .= " ,`profile_picture_url` = '$result'";
    }
    $update_tail = " WHERE `users`.`id` = '$user_id'";
    $query = $update_head . $set_user_name . $set_password . $set_bio . $set_avatar . $update_tail;
    $resultt = $this->mysqli->query($query);
    if ($resultt) {
      // successful...
      echo "successful";
    } else {
      http_response_code(500); // Internal Server Error
      echo ("Registration failed, Please try again");
    }
  }

  public function get_password($password)
  {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT password FROM users WHERE id='$user_id'";
    $result = $this->mysqli->query($query);
    if ($result->num_rows == 1) {
      // The email and password match a user in the database, so log in the user
      $pwd = $result->fetch_assoc();
      if (password_verify($password, $pwd['password'])) {
        return true;
      } else {
        return false;
      }
    } else {
      http_response_code(404); // Not Found
      echo ("Invalid, Please try again");
      exit();
    }
  }

  public function get_username($username)
  {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT username FROM users WHERE username='$username' AND id != '$user_id' ";
    $result = $this->mysqli->query($query);
    if ($result->num_rows > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function get_relevent_user()
  {
    $user_id = $_SESSION['user_id'];
    $user_input = $_POST['value'];
    $query = "SELECT id,username,email,profile_picture_url FROM users WHERE username LIKE '%$user_input%' OR email LIKE '%$user_input%'";
    $result = $this->mysqli->query($query);
    $user = array();
    $following = false;
    while ($row = $result->fetch_assoc()) {
      if ($row['username'] == '' || $row['username'] == 'null') {
        continue;
      }
      if ($row['email'] == '' || $row['email'] == 'null') {
        continue;
      }
      if ($this->check_followed_oneway($user_id, $row['id'])) {
        $following = true;
      } else {
        $following = false;
      }
      // array_push($user, $row['username'], $row['email'], $row['profile_picture_url']);
      array_push($user, (object) [
        'id' => $row['id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'profile_picture_url' => $row['profile_picture_url'],
        'following' => $following,
      ]);
    }
    // echo $user;
    echo json_encode($user);
  }

  public function get_user_status($username = null)
  {
    if ($username === null) {
      if (!isset($_POST['username'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Username is required"]);
        exit();
      }
      $username = $_POST['username'];
    }

    $user_id = $_SESSION['user_id'];
    $following = false;

    $stmt = $this->mysqli->prepare("SELECT u.id, u.username, u.profile_picture_url,
              COUNT(DISTINCT p.id) AS num_posts, COUNT(DISTINCT f1.id) AS num_followers,
              COUNT(DISTINCT f2.id) AS num_followings
              FROM users u
              LEFT JOIN posts p ON u.id = p.user_id
              LEFT JOIN follows f1 ON u.id = f1.following_id
              LEFT JOIN follows f2 ON u.id = f2.follower_id
              WHERE u.username = ?
              GROUP BY u.id");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      if ($this->check_followed_oneway($user_id, $user['id'])) {
        $following = true;
      }
      $user['following'] = $following;
      echo json_encode($user);
    } else {
      http_response_code(404); // Not Found
      echo json_encode(["error" => "Invalid, please try again"]);
      exit();
    }
  }

  public function check_followed_oneway($follower_id, $following_id)
  {
    $query = "SELECT following_id, follower_id
      FROM follows
      WHERE follower_id = ? AND following_id = ?;";
    $stmt = $this->mysqli->prepare($query);
    $stmt->bind_param("ss", $follower_id, $following_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows != 0) {
      return true;
    } else {
      return false;
    }
  }

  public function get_recommand_user()
  {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from users table using LEFT JOIN and WHERE NOT EXISTS
    $query = "SELECT id, username, profile_picture_url FROM users
              WHERE NOT EXISTS (
                  SELECT 1 FROM follows
                  WHERE following_id = users.id AND follower_id = '$user_id'
              )
              AND id != '$user_id'
              ORDER BY RAND()
              LIMIT 4";

    $result = $this->mysqli->query($query);

    $user = array();
    if ($result && mysqli_num_rows($result) > 0) {
      // Store the results in an array
      $user_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
      foreach ($user_array as $user_data) {
        array_push($user, $user_data);
      }
    } else {
      // No records were found
      echo 'No records found';
    }

    echo json_encode($user);
  }
}