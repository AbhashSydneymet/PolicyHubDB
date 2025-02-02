<?php
// controllers/AuthController.php
require_once 'auth/AzureADAuthenticator.php';

class AuthController {
    private $authenticator;

    public function __construct() {
        $this->authenticator = new AzureADAuthenticator();
    }

    public function authenticate() {
        session_start(); // Start the session to manage session variables
        
        // Check if the user is already authenticated
        if (isset($_SESSION['access_token'])) {
            // If already logged in, redirect to home/dashboard
            header('Location: index.php?action=home');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle POST request - check username and password
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Check if either username or password is empty
            if (empty($username) || empty($password)) {
                $_SESSION['error_message'] = 'Please provide both username and password.';
                header('Location: index.php?action=login');
                exit();
            }

            // Redirect to Azure AD login page
            header('Location: https://login.microsoftonline.com/' . $this->authenticator->getConfig()['tenant_id'] . '/oauth2/v2.0/authorize?client_id=' . $this->authenticator->getConfig()['client_id'] . '&response_type=code&redirect_uri=' . urlencode($this->authenticator->getConfig()['redirect_uri']) . '&scope=' . urlencode('User.Read Directory.Read.All'));
            exit();
        } else {
            // If not a POST request, redirect to the login page with an error message
            $_SESSION['error_message'] = 'Invalid request method.';
            header('Location: index.php?action=login');
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?action=login');
        exit();
    }
}
