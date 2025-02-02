<?php
// index.php
// index.php

session_start();
require_once 'controllers/PolicyController.php';
require_once 'auth/AzureADAuthenticator.php';
require_once 'controllers/AuthController.php';
require_once 'auth/RoleManager.php';
require_once 'auth/AuthMiddleware.php';

$action = $_GET['action'] ?? 'index';
$id = $_POST['id'] ?? $_GET['id'] ?? null;

$policyController = new PolicyController();
$authController = new AuthController();  

// Actions that don't require authentication
$publicActions = ['login', 'authenticate', 'callback'];  // Add 'callback' to the list of public actions

if (!isset($_SESSION['user']) && !in_array($action, $publicActions)) {
    header('Location: index.php?action=login');
    exit();
}

switch ($action) {
    case 'login':
        include 'views/login.php';
        break;
    case 'authenticate':
        $authController->authenticate();
        break;
    case 'logout':
        $authController->logout();
        break;
    // case 'callback':  // Add a new case for 'callback'
    //     $authController->callback();  // Make sure the callback is handled here
    //     break;
    case 'index':
        $policyController->index();
        break;
    case 'search':
        $policyController->search();
        break;
    case 'create':
        $policyController->create();
        break;
    case 'store':
        $policyController->store();
        break;
    case 'edit':
        if ($id) {
            $policyController->edit($id);
        }
        break;
    case 'update':
        if ($id) {
            $policyController->update($id);
        }
        break;
    case 'delete':
        if ($id) {
            $policyController->delete($id);
        }
        break;
    default:
        echo "Page not found";
        break;
}
