<?php
/**
 * General utility functions for the NIT Delhi Crowdfunding Platform
 */

/**
 * Generate and set a CSRF token in the session
 *
 * @return string - Generated CSRF token
 */
function setCsrfToken() {
    // Session should already be started
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    error_log("CSRF token generated and set: " . substr($token, 0, 10) . "...");
    return $token;
}

/**
 * Validate a CSRF token against the one stored in session
 *
 * @param string $token - CSRF token to validate
 * @return bool - True if valid, false otherwise
 */
function validateCsrfToken($token) {
    error_log("Validating CSRF token: " . substr($token, 0, 10) . "...");
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        error_log("No CSRF token found in session");
        return false;
    }
    
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    error_log("CSRF validation result: " . ($valid ? "valid" : "invalid"));
    
    return $valid;
}

/**
 * Sanitize and validate input data
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Set a flash message to be displayed once
 * 
 * @param string $message - Message text
 * @param string $type - Message type (success, danger, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Display flash messages and clear them from session
 */
function displayFlashMessages() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        
        echo '<div class="container mt-3">';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        echo '</div>';
        
        // Clear the flash message
        unset($_SESSION['flash_message']);
    }
}

/**
 * Format currency with INR symbol and thousands separator
 * 
 * @param int|float $amount - Amount to format
 * @return string - Formatted amount string
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 0, '.', ',');
}

/**
 * Calculate days remaining until a given date
 * 
 * @param string $endDate - End date in Y-m-d format
 * @return int - Number of days remaining (0 if passed)
 */
function daysRemaining($endDate) {
    $end = new DateTime($endDate);
    $now = new DateTime();
    
    // Set time to beginning of day for accurate calculation
    $end->setTime(0, 0, 0);
    $now->setTime(0, 0, 0);
    
    $diff = $now->diff($end);
    
    // If end date has passed, return 0
    if ($diff->invert) {
        return 0;
    }
    
    return $diff->days;
}

/**
 * Calculate funding percentage
 * 
 * @param float $raised - Amount raised
 * @param float $goal - Goal amount
 * @return float - Percentage completed (capped at 100)
 */
function calculateFundingPercentage($raised, $goal) {
    if ($goal <= 0) {
        return 0;
    }
    
    $percentage = ($raised / $goal) * 100;
    return min(100, $percentage);
}

/**
 * Generate a random string
 * 
 * @param int $length - Length of the string
 * @return string - Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Get user-friendly category name
 * 
 * @param string $categoryKey - Category key
 * @return string - User-friendly category name
 */
function getCategoryName($categoryKey) {
    global $CATEGORIES;
    
    return isset($CATEGORIES[$categoryKey]) ? $CATEGORIES[$categoryKey] : 'Other';
}

/**
 * Get user-friendly department name
 * 
 * @param string $departmentKey - Department key
 * @return string - User-friendly department name
 */
function getDepartmentName($departmentKey) {
    global $DEPARTMENTS;
    
    return isset($DEPARTMENTS[$departmentKey]) ? $DEPARTMENTS[$departmentKey] : 'Other';
}

/**
 * Format date to a user-friendly string
 * 
 * @param string $date - Date in Y-m-d format
 * @return string - Formatted date string
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Truncate text to a specific length
 * 
 * @param string $text - Text to truncate
 * @param int $length - Maximum length
 * @param string $append - String to append if truncated
 * @return string - Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Check if a string is a valid email address
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if an email is from NIT Delhi domain
 * 
 * @param string $email - Email address to check
 * @return bool - True if from NIT Delhi domain, false otherwise
 */
function isNitDelhiEmail($email) {
    return (substr(strrchr($email, "@"), 1) === 'nitdelhi.ac.in');
}

/**
 * Get campaign status badge HTML
 * 
 * @param string $status - Campaign status
 * @return string - Badge HTML
 */
function getCampaignStatusBadge($status) {
    $badges = [
        CAMPAIGN_PENDING => '<span class="badge bg-warning">Pending</span>',
        CAMPAIGN_APPROVED => '<span class="badge bg-success">Approved</span>',
        CAMPAIGN_REJECTED => '<span class="badge bg-danger">Rejected</span>',
        CAMPAIGN_COMPLETED => '<span class="badge bg-info">Completed</span>',
        CAMPAIGN_DRAFT => '<span class="badge bg-secondary">Draft</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Get payment status badge HTML
 * 
 * @param string $status - Payment status
 * @return string - Badge HTML
 */
function getPaymentStatusBadge($status) {
    $badges = [
        PAYMENT_PENDING => '<span class="badge bg-warning">Pending</span>',
        PAYMENT_COMPLETED => '<span class="badge bg-success">Completed</span>',
        PAYMENT_FAILED => '<span class="badge bg-danger">Failed</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Get user role badge HTML
 * 
 * @param string $role - User role
 * @return string - Badge HTML
 */
function getUserRoleBadge($role) {
    $badges = [
        ROLE_ADMIN => '<span class="badge bg-danger">Admin</span>',
        ROLE_STUDENT => '<span class="badge bg-primary">Student</span>',
        ROLE_ALUMNI => '<span class="badge bg-success">Alumni</span>',
        ROLE_FACULTY => '<span class="badge bg-info">Faculty</span>',
        ROLE_STAFF => '<span class="badge bg-warning">Staff</span>'
    ];
    
    return isset($badges[$role]) ? $badges[$role] : '<span class="badge bg-secondary">User</span>';
}

/**
 * Get verification badge HTML
 * 
 * @param bool $isVerified - Verification status
 * @return string - Badge HTML
 */
function getVerificationBadge($isVerified) {
    if ($isVerified) {
        return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Verified</span>';
    }
    
    return '<span class="badge bg-secondary">Unverified</span>';
}

/**
 * Convert bytes to human-readable file size
 * 
 * @param int $bytes - Bytes to convert
 * @return string - Human-readable file size
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get time elapsed string (e.g., "5 minutes ago")
 * 
 * @param string $datetime - Datetime string
 * @return string - Time elapsed string
 */
function getTimeElapsed($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    }
    
    $intervals = [
        1 => ['year', 31536000],
        2 => ['month', 2592000],
        3 => ['week', 604800],
        4 => ['day', 86400],
        5 => ['hour', 3600],
        6 => ['minute', 60]
    ];
    
    foreach ($intervals as $interval) {
        $name = $interval[0];
        $seconds = $interval[1];
        $count = floor($diff / $seconds);
        
        if ($count > 0) {
            return $count . ' ' . $name . ($count > 1 ? 's' : '') . ' ago';
        }
    }
}

/**
 * Generate a pagination control
 * 
 * @param int $currentPage - Current page number
 * @param int $totalPages - Total number of pages
 * @param string $baseUrl - Base URL for pagination links
 * @return string - Pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    } else {
        $html .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
        
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    } else {
        $html .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Redirect to a given URL
 * 
 * @param string $url - URL to redirect to
 * @return void
 */
function redirect($url) {
    error_log("Redirecting to: $url");
    header("Location: $url");
    exit;
}
?>