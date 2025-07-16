<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Authentication API endpoints
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the incoming request data
error_log("AUTH API Request: " . print_r($_REQUEST, true));

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// Set content type for AJAX responses
header('Content-Type: application/json');

// Check if action is provided
if (!isset($_REQUEST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

$action = $_REQUEST['action'];

// Handle different actions
switch ($action) {
    case 'login':
        // Login action
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['csrf_token'])) {
            // Validate CSRF token
            if (!validateCsrfToken($_POST['csrf_token'])) {
                setFlashMessage('Invalid request. Please try again.', 'danger');
                redirect('../pages/login.php');
                exit;
            }
            
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;
            
            $result = loginUser($email, $password);
            
            if ($result['success'] === true) {
                // Redirect to dashboard or intended page
                setFlashMessage('Login successful! Welcome back.', 'success');
                redirect('../pages/dashboard.php');
            } else {
                // Return to login page with error
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/login.php');
            }
        } else {
            setFlashMessage('Please provide both email and password.', 'danger');
            redirect('../pages/login.php');
        }
        break;
        
    case 'register':
        // Registration action
        error_log("Processing registration request");
        
        if (
            isset($_POST['name']) && 
            isset($_POST['email']) && 
            isset($_POST['password']) && 
            isset($_POST['password_confirm']) &&
            isset($_POST['role']) &&
            isset($_POST['csrf_token'])
        ) {
            error_log("All required fields present for registration");
            
            // Validate CSRF token
            if (!validateCsrfToken($_POST['csrf_token'])) {
                setFlashMessage('Invalid request. Please try again.', 'danger');
                redirect('../pages/register.php');
                exit;
            }
            
            // Validate passwords match
            if ($_POST['password'] !== $_POST['password_confirm']) {
                setFlashMessage('Passwords do not match.', 'danger');
                redirect('../pages/register.php');
                exit;
            }
            
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Additional fields
            $department = isset($_POST['department']) ? $_POST['department'] : '';
            
            error_log("Calling registerUser with: $name, $email, [password], $role, $department");
            $result = registerUser($name, $email, $password, $role, $department);
            error_log("Registration result: " . print_r($result, true));
            
            if ($result['success'] === true) {
                setFlashMessage('Registration successful! Please check your email to verify your account.', 'success');
                redirect('../pages/login.php');
            } else {
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/register.php');
            }
        } else {
            error_log("Missing required fields for registration");
            setFlashMessage('Please fill in all required fields.', 'danger');
            redirect('../pages/register.php');
        }
        break;
        
    case 'logout':
        // Logout action
        logoutUser();
        redirect('../index.php');
        break;
        
    case 'forgot_password':
        // Forgot password action
        if (isset($_POST['email']) && isset($_POST['csrf_token'])) {
            // Validate CSRF token
            if (!validateCsrfToken($_POST['csrf_token'])) {
                setFlashMessage('Invalid request. Please try again.', 'danger');
                redirect('../pages/forgot_password.php');
                exit;
            }
            
            $email = trim($_POST['email']);
            
            $result = requestPasswordReset($email);
            
            if ($result['success'] === true) {
                setFlashMessage('Password reset instructions have been sent to your email.', 'success');
                redirect('../pages/login.php');
            } else {
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/forgot_password.php');
            }
        } else {
            setFlashMessage('Please provide your email address.', 'danger');
            redirect('../pages/forgot_password.php');
        }
        break;
        
    case 'reset_password':
        // Reset password action
        if (
            isset($_POST['token']) && 
            isset($_POST['password']) && 
            isset($_POST['password_confirm']) &&
            isset($_POST['csrf_token'])
        ) {
            // Validate CSRF token
            if (!validateCsrfToken($_POST['csrf_token'])) {
                setFlashMessage('Invalid request. Please try again.', 'danger');
                redirect('../pages/reset_password.php?token=' . urlencode($_POST['token']));
                exit;
            }
            
            $token = trim($_POST['token']);
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
            
            // Validate passwords match
            if ($password !== $password_confirm) {
                setFlashMessage('Passwords do not match.', 'danger');
                redirect('../pages/reset_password.php?token=' . urlencode($token));
                exit;
            }
            
            $result = resetPassword($token, $password);
            
            if ($result['success'] === true) {
                setFlashMessage('Your password has been reset successfully. You can now log in with your new password.', 'success');
                redirect('../pages/login.php');
            } else {
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/reset_password.php?token=' . urlencode($token));
            }
        } else {
            setFlashMessage('Invalid password reset request.', 'danger');
            redirect('../pages/forgot_password.php');
        }
        break;
        
    case 'verify_email':
        // Email verification action
        if (isset($_GET['token'])) {
            $token = trim($_GET['token']);
            
            $result = verifyEmail($token);
            
            if ($result['success'] === true) {
                setFlashMessage('Your email has been verified successfully. You can now log in.', 'success');
                redirect('../pages/login.php');
            } else {
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/login.php');
            }
        } else {
            setFlashMessage('Invalid verification request.', 'danger');
            redirect('../pages/login.php');
        }
        break;

    case 'resend_verification':
        // Resend verification email action
        if (isset($_POST['email']) && isset($_POST['csrf_token'])) {
            // Validate CSRF token
            if (!validateCsrfToken($_POST['csrf_token'])) {
                setFlashMessage('Invalid request. Please try again.', 'danger');
                redirect('../pages/resend_verification.php');
                exit;
            }
            
            $email = trim($_POST['email']);
            
            $result = resendVerificationEmail($email);
            
            if ($result['success'] === true) {
                setFlashMessage($result['message'], 'success');
                redirect('../pages/login.php');
            } else {
                setFlashMessage($result['message'], 'danger');
                redirect('../pages/resend_verification.php');
            }
        } else {
            setFlashMessage('Please provide your email address.', 'danger');
            redirect('../pages/resend_verification.php');
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
        exit;
}
?>