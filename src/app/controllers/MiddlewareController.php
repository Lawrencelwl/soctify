<?php
class MiddlewareController{

    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function check_login()
    {
        // Assuming you have already started the session
        if (isset($_SESSION['user_id'])) {
            // Get the user_id from the session
            $user_id = $_SESSION['user_id'];
            
            // Query the database to check if the user_id exists
            $query = "SELECT * FROM users WHERE id=?";
            $stmt = $this->mysqli->prepare($query);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                // The user_id matches a user in the database, so you can proceed with the user's information
                $user = $result->fetch_assoc();
                // Do something with the user's information
            } else {
                // The user_id does not match a user in the database, so you can redirect the user to the login page
                header('Location: /login');
                exit();
            }
        } else {
            // The user_id is not set in the session, so you can redirect the user to the login page
            header('Location: /login');
            exit();
        }
    }
    
}
?>