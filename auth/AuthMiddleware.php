<?php
// auth/AuthMiddleware.php
class AuthMiddleware {
    public static function authenticate() {
        if (!isset($_SESSION['user'])) {
            header('Location: login.php');
            exit();
        }
    }
}
?>
