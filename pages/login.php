<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Login Page
 */

// Start session
session_start();
session_regenerate_id(true);

// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
    exit;
}

// Set CSRF token
$csrf_token = setCsrfToken();

// Set page title
$page_title = 'Login';

// Check if the form was submitted
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        // If no errors, attempt login
        if (empty($errors)) {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                // Redirect to dashboard
                redirect('dashboard.php');
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
                    <h1 class="text-center mb-4">Login</h1>
                    
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
                    
                    <form action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="forgot_password.php">Forgot your password?</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="resend_verification.php">Resend verification email</a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <small>For NIT Delhi students, faculty, and staff: <br>Use your institutional email to get verified status.</small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>