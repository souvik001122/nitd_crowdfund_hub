<?php
session_start();

/**
 * NIT Delhi Crowdfunding Platform
 * Forgot Password Page
 */

// Include configuration files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Set page title
$page_title = 'Forgot Password';

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Forgot Password</h2>

                    <form action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="forgot_password">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="form-text">Enter your registered email address to receive password reset instructions.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                            <a href="login.php" class="btn btn-link">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>