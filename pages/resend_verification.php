<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Resend Verification Email Page
 */

// Start session
session_start();
session_regenerate_id(true);

// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// Set CSRF token
$csrf_token = setCsrfToken();

// Set page title
$page_title = 'Resend Verification Email';

// Process form submission
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $email = sanitizeInput($_POST['email']);
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // If no errors, resend verification email
        if (empty($errors)) {
            $result = resendVerificationEmail($email);
            
            if ($result['success']) {
                setFlashMessage($result['message'], 'success');
                redirect('login.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Resend Verification Email</h1>
                    
                    <?php displayFlashMessages(); ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <p class="mb-4">Enter your email address below to receive a new verification link.</p>
                    
                    <form action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="resend_verification">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Send Verification Email</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>