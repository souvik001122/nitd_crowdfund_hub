<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Registration Page
 */

// Start session
session_start();
session_regenerate_id(true);

// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';
// No direct inclusion of database.php needed as it's included in auth_functions.php

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
    exit;
}

// Set CSRF token
$csrf_token = setCsrfToken();

// Set page title
$page_title = 'Register';

// Initialize variables
$errors = [];
$name = '';
$email = '';
$role = '';
$department = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $role = sanitizeInput($_POST['role']);
        $department = sanitizeInput($_POST['department']);
        
        // Validate name
        if (empty($name)) {
            $errors[] = 'Name is required';
        } elseif (strlen($name) < 2 || strlen($name) > 50) {
            $errors[] = 'Name must be between 2 and 50 characters';
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match';
        }
        
        // Validate role
        if (empty($role) || !in_array($role, [ROLE_STUDENT, ROLE_ALUMNI, ROLE_FACULTY, ROLE_STAFF])) {
            $errors[] = 'Please select a valid role';
        }
        
        // Validate department
        if (empty($department)) {
            $errors[] = 'Department is required';
        }
        
        // Check if email is from NIT Delhi for faculty/student
        if (($role === ROLE_STUDENT || $role === ROLE_FACULTY || $role === ROLE_STAFF) && !isNitDelhiEmail($email)) {
            $errors[] = 'NIT Delhi institutional email is required for students, faculty, and staff';
        }
        
        // If no errors, register the user
        if (empty($errors)) {
            error_log("Proceeding with user registration: $name, $email, $role, $department");
            $result = registerUser($name, $email, $password, $role, $department);
            
            if ($result['success']) {
                // Set flash message and redirect to login page
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
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Create an Account</h1>
                    
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
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <small id="emailHelp" class="form-text text-muted">Use @nitdelhi.ac.in for NIT Delhi members</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small id="passwordHelp" class="form-text text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="" selected disabled>Select your role</option>
                                    <option value="<?php echo ROLE_STUDENT; ?>" <?php echo ($role === ROLE_STUDENT) ? 'selected' : ''; ?>>Student</option>
                                    <option value="<?php echo ROLE_FACULTY; ?>" <?php echo ($role === ROLE_FACULTY) ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="<?php echo ROLE_STAFF; ?>" <?php echo ($role === ROLE_STAFF) ? 'selected' : ''; ?>>Staff</option>
                                    <option value="<?php echo ROLE_ALUMNI; ?>" <?php echo ($role === ROLE_ALUMNI) ? 'selected' : ''; ?>>Alumni</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="" selected disabled>Select your department</option>
                                    <?php foreach ($DEPARTMENTS as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($department === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Register</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>NIT Delhi Crowdfunding Platform Terms of Use</h6>
                <p>By creating an account on the NIT Delhi Crowdfunding Platform, you agree to the following terms:</p>
                
                <ol>
                    <li>All information provided during registration must be accurate and truthful.</li>
                    <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                    <li>You will use the platform for legitimate crowdfunding purposes related to NIT Delhi.</li>
                    <li>All campaigns must comply with institute policies and applicable laws.</li>
                    <li>The platform administrators reserve the right to review and reject campaigns that violate policies.</li>
                    <li>Funds collected through the platform must be used for the stated purpose of the campaign.</li>
                    <li>Campaign creators are responsible for fulfilling any promised rewards or incentives.</li>
                    <li>The platform is not responsible for disputes between campaign creators and donors.</li>
                    <li>The platform reserves the right to suspend accounts that violate these terms.</li>
                    <li>You agree to receive communications related to your account and campaigns.</li>
                </ol>
                
                <h6>Privacy Policy</h6>
                <p>We collect personal information to provide and improve our service. We do not sell or share your personal information with third parties except as described in our Privacy Policy.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>