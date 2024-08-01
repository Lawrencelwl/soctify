<?php
class AccountController
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


    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            // Invalid email or password
            http_response_code(400);
            echo ("Invalid email or password, please try again");
            exit();
        }

        // Prepare the query to prevent SQL injection
        $stmt = $this->mysqli->prepare("SELECT id, username, password, profile_picture_url FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // The email exists in the database
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // The email and password match a user in the database, so log in the user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['avatar_url'] = $user['profile_picture_url'];
                echo ("Success");
                exit();
            } else {
                // Invalid password
                http_response_code(401);
                echo ("Invalid password, please try again");
                exit();
            }
        } else {
            // Invalid email
            http_response_code(404);
            echo ("Invalid email, please try again");
            exit();
        }
    }

    public function logout()
    {
        // destroy session and redirect to login page
        session_destroy();
        header('Location: /');
        exit();
    }

    public function register()
    {
        // Check that required fields are present
        $requiredFields = array('username', 'email', 'password', 'password2');
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                http_response_code(400); // Bad Request
                echo ("Missing required fields");
                exit();
            }
        }

        // Sanitize and validate input
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        if ($password !== $password2) {
            http_response_code(400); // Bad Request
            echo ("Passwords do not match");
            exit();
        }

        // Check if user with the given email or username already exists
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            http_response_code(409); // Conflict
            echo ("This email or username is already registered");
            exit();
        }

        // Generate a unique id for the user
        $uuid = $this->guidv4();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        $stmt = $this->mysqli->prepare("INSERT INTO users (id, username, password, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $uuid, $username, $hashed_password, $email);
        if ($stmt->execute()) {
            echo ("Registration successful, Please login");
            exit();
        } else {
            http_response_code(401); // Unauthorized
            echo ("Registration failed, Please try again");
            exit();
        }
    }
}