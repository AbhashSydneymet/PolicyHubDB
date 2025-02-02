<?php
// auth/AzureADAuthenticator.php
require_once 'vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AzureADAuthenticator {
    private $config;

    public function __construct() {
        $this->config = [
            'tenant_id' => '', // Your Azure AD Tenant ID
            'client_id' => '',  // Azure AD Application (client) ID
            'client_secret' => '', // Azure AD Client Secret
            'redirect_uri' => '', // Redirect URI registered in Azure AD
            'scopes' => ['User.Read', 'Directory.Read.All']
        ];
    }
    public function getConfig() {
        return $this->config;
    }

    // Redirect to Azure AD for login
    public function authenticate() {
        session_start();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
    
            // Redirect to Azure AD login page
            header('Location: https://login.microsoftonline.com/' . $this->config['tenant_id'] . '/oauth2/v2.0/authorize?client_id=' . $this->config['client_id'] . '&response_type=code&redirect_uri=' . urlencode($this->config['redirect_uri']) . '&scope=' . urlencode('User.Read Directory.Read.All'));
            exit();
        } else {
            // Handle unexpected request method
            $_SESSION['error_message'] = 'Invalid request method.';
            header('Location: index.php?action=login');
            exit();
        }
    }

    // Get the token using the code received from Azure AD
    public function getTokenFromCode($code) {
        $guzzle = new \GuzzleHttp\Client();
        $url = 'https://login.microsoftonline.com/' . $this->config['tenant_id'] . '/oauth2/v2.0/token';

        $response = $guzzle->post($url, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'redirect_uri' => $this->config['redirect_uri'],
                'code' => $code,
                'scope' => 'User.Read Directory.Read.All',
            ],
        ]);

        $tokenData = json_decode($response->getBody()->getContents(), true);
        return $tokenData['access_token'] ?? null;
    }

    // Get user info using the token
    public function getUserInfoFromToken($token) {
        $guzzle = new \GuzzleHttp\Client();
        $url = 'https://graph.microsoft.com/v1.0/me';

        $response = $guzzle->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $userInfo = json_decode($response->getBody()->getContents(), true);
        return $userInfo;
    }
}

