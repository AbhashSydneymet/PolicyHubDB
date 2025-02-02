<?php
// callback.php
session_start();

require_once 'auth/AzureADAuthenticator.php';

$authenticator = new AzureADAuthenticator();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Debugging: Check if the code is received
    error_log("Authorization code received: " . $code);
    
    $token = $authenticator->getTokenFromCode($code);
    
    if ($token) {
        $_SESSION['access_token'] = $token;
        
        $userInfo = $authenticator->getUserInfoFromToken($token);
        
        if ($userInfo) {
            $_SESSION['user'] = [
                'username' => $userInfo['userPrincipalName'],
                'displayName' => $userInfo['displayName'],
            ];
            header('Location: index.php?action=index');
            exit();
        } else {
            $_SESSION['error_message'] = 'Unable to fetch user info.';
            header('Location: index.php?action=login');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Authentication failed: Unable to get access token.';
        header('Location: index.php?action=login');
        exit();
    }
} else {
    // Debugging: Log if the code parameter is missing
    error_log("No authorization code received.");
    
    $_SESSION['error_message'] = 'No authorization code received.';
    header('Location: index.php?action=login');
    exit();
} 