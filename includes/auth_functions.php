<?php
/**
 * Authentication-related functions for the NIT Delhi Crowdfunding Platform
 */

// Include database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Register a new user
 * 
 * @param string $name - User's name
 * @param string $email - User's email
 * @param string $password - User's password
 * @param string $role - User's role
 * @param string $department - User's department
 * @return array - Result information
 */
function registerUser($name, $email, $password, $role, $department) {
    $db = new Database();
    $conn = $db->connect();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return [
            'success' => false,
            'message' => 'Email already exists'
        ];
    }
    
    // Generate email verification token
    $verificationToken = generateRandomString(32);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Determine if email is from NIT Delhi domain
    $isNitDelhi = isNitDelhiEmail($email);
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role, department, verification_token, is_nit_delhi, created_at) 
            VALUES (:name, :email, :password, :role, :department, :verification_token, :is_nit_delhi, NOW())
        ");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':verification_token', $verificationToken);
        $stmt->bindParam(':is_nit_delhi', $isNitDelhi, PDO::PARAM_BOOL);
        
        $stmt->execute();
        $userId = $conn->lastInsertId();
        
        // Commit transaction
        $conn->commit();
        
        // Send verification email
        // Note: This would typically send an actual email in production
        // For now, we'll just return the verification token
        
        return [
            'success' => true,
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user_id' => $userId,
            'verification_token' => $verificationToken
        ];
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}



/**
 * Login user
 * 
 * @param string $email - User's email
 * @param string $password - User's password
 * @return array - Result information
 */
function loginUser($email, $password) {
    $db = new Database();
    $conn = $db->connect();
    
    // Find user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }
    
    // Check if email is verified
    if (!$user['email_verified']) {
        return [
            'success' => false,
            'message' => 'Please verify your email before logging in'
        ];
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        return [
            'success' => false,
            'message' => 'Your account has been deactivated. Please contact administrator.'
        ];
    }
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['is_verified'] = $user['is_verified']; // This is manual verification by admin
    $_SESSION['is_nit_delhi'] = $user['is_nit_delhi'];
    $_SESSION['logged_in'] = true;
    
    // Update last login time
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_verified' => $user['is_verified'],
            'is_nit_delhi' => $user['is_nit_delhi']
        ]
    ];
}

/**
 * Logout user
 */
function logoutUser() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 * 
 * @return bool - True if logged in, false otherwise
 */
function isLoggedIn() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin
 * 
 * @return bool - True if admin, false otherwise
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

/**
 * Check if user is verified
 * 
 * @return bool - True if verified, false otherwise
 */
function isVerified() {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['is_verified']) && $_SESSION['is_verified'] === 1;
}

/**
 * Check if user is from NIT Delhi
 * 
 * @return bool - True if from NIT Delhi, false otherwise
 */
function isNitDelhi() {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['is_nit_delhi']) && $_SESSION['is_nit_delhi'] === 1;
}

/**
 * Get current user ID
 * 
 * @return int|null - User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return $_SESSION['user_id'];
}

/**
 * Get user details by ID
 * 
 * @param int $userId - User ID
 * @return array|null - User details if found, null otherwise
 */
function getUserById($userId) {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return null;
    }
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Request password reset
 * 
 * @param string $email - User's email
 * @return array - Result information
 */
function requestPasswordReset($email) {
    $db = new Database();
    $conn = $db->connect();
    
    // Find user by email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'message' => 'Email not found'
        ];
    }
    
    // Generate reset token
    $resetToken = generateRandomString(32);
    $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Update user with reset token
    $stmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_expires = :expires WHERE email = :email");
    $stmt->bindParam(':token', $resetToken);
    $stmt->bindParam(':expires', $resetExpires);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        // Send password reset email
        // Note: This would typically send an actual email in production
        // For now, we'll just return the reset token
        
        return [
            'success' => true,
            'message' => 'Password reset instructions sent to your email',
            'reset_token' => $resetToken
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to process password reset request'
        ];
    }
}

/**
 * Reset password with token
 * 
 * @param string $token - Reset token
 * @param string $password - New password
 * @return array - Result information
 */
function resetPassword($token, $password) {
    $db = new Database();
    $conn = $db->connect();
    
    // Find user with this token and check if it's still valid
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ];
    }
    
    // Update user password
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userId = $user['id'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':id', $userId);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Password reset successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to reset password'
        ];
    }
}

/**
 * Update user profile
 * 
 * @param int $userId - User ID
 * @param array $data - Profile data to update
 * @return array - Result information
 */
function updateUserProfile($userId, $data) {
    $db = new Database();
    $conn = $db->connect();
    
    // Build update query based on provided data
    $updateFields = [];
    $params = [':id' => $userId];
    
    // Only update fields that are provided
    if (isset($data['name'])) {
        $updateFields[] = "name = :name";
        $params[':name'] = $data['name'];
    }
    
    if (isset($data['department'])) {
        $updateFields[] = "department = :department";
        $params[':department'] = $data['department'];
    }
    
    if (isset($data['bio'])) {
        $updateFields[] = "bio = :bio";
        $params[':bio'] = $data['bio'];
    }
    
    if (isset($data['phone'])) {
        $updateFields[] = "phone = :phone";
        $params[':phone'] = $data['phone'];
    }
    
    // If no fields to update, return error
    if (empty($updateFields)) {
        return [
            'success' => false,
            'message' => 'No fields to update'
        ];
    }
    
    // Build and execute update query
    $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $conn->prepare($updateQuery);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to update profile'
        ];
    }
}

/**
 * Change user password
 * 
 * @param int $userId - User ID
 * @param string $currentPassword - Current password
 * @param string $newPassword - New password
 * @return array - Result information
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = new Database();
    $conn = $db->connect();
    
    // Get user's current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Current password is incorrect'
        ];
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':id', $userId);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to change password'
        ];
    }
}

/**
 * Verify user email using token
 * 
 * @param string $token - Verification token
 * @return array - Result array with success status and message
 */
function verifyEmail($token) {
    error_log("Attempting to verify email with token: " . substr($token, 0, 10) . "...");
    
    $db = new Database();
    $conn = $db->connect();
    
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = :token AND email_verified = 0");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            error_log("Invalid verification token or already verified");
            return [
                'success' => false,
                'message' => 'Invalid verification link or account already verified.'
            ];
        }
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $userData['id'];
        
        // Update user to set email as verified
        $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = :id");
        $updateStmt->bindParam(':id', $userId);
        $updateStmt->execute();
        
        error_log("Email successfully verified for user ID: $userId");
        
        return [
            'success' => true,
            'message' => 'Your email has been verified successfully. You can now log in.'
        ];
    } catch (PDOException $e) {
        error_log("Email verification error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred during verification. Please try again later.'
        ];
    }
}

/**
 * Resend verification email
 * 
 * @param string $email - User email address
 * @return array - Result array with success status and message
 */
function resendVerificationEmail($email) {
    error_log("Attempting to resend verification email to: $email");
    
    $db = new Database();
    $conn = $db->connect();
    
    try {
        // Check if email exists and is not verified
        $stmt = $conn->prepare("SELECT id, name, email_verified FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'Email address not found.'
            ];
        }
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData['email_verified'] == 1) {
            return [
                'success' => false,
                'message' => 'This email is already verified. You can log in.'
            ];
        }
        
        // Generate new verification token
        $verificationToken = bin2hex(random_bytes(32));
        
        // Update user with new token
        $updateStmt = $conn->prepare("UPDATE users SET verification_token = :token WHERE id = :id");
        $updateStmt->bindParam(':token', $verificationToken);
        $updateStmt->bindParam(':id', $userData['id']);
        $updateStmt->execute();
        
        // Send verification email (for now just log it)
        $verificationUrl = URL_ROOT . '/api/auth.php?action=verify_email&token=' . $verificationToken;
        
        // For testing purposes, log the verification URL
        error_log("Verification URL: $verificationUrl");
        
        return [
            'success' => true,
            'message' => 'Verification email has been sent. Please check your inbox.'
        ];
    } catch (PDOException $e) {
        error_log("Error resending verification email: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ];
    }
}


?>


