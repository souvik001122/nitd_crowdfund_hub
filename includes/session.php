<?php
/**
 * Session management for NIT Delhi Crowdfunding Platform
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a value in the session
 * 
 * @param string $key - Session key
 * @param mixed $value - Value to store
 */
function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get a value from the session
 * 
 * @param string $key - Session key
 * @param mixed $default - Default value if key doesn't exist
 * @return mixed - Session value or default
 */
function getSession($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Check if a session key exists
 * 
 * @param string $key - Session key
 * @return bool - True if key exists, false otherwise
 */
function hasSession($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove a value from the session
 * 
 * @param string $key - Session key
 */
function removeSession($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Clear all session data
 */
function clearSession() {
    session_unset();
    session_destroy();
}

/**
 * Regenerate session ID
 * 
 * @param bool $deleteOldSession - Whether to delete the old session
 */
function regenerateSession($deleteOldSession = true) {
    session_regenerate_id($deleteOldSession);
}

/**
 * Set a session flash message (available only for the next request)
 * 
 * @param string $key - Flash message key
 * @param mixed $value - Flash message value
 */
function setFlash($key, $value) {
    $_SESSION['_flash'][$key] = $value;
}

/**
 * Get a session flash message and remove it
 * 
 * @param string $key - Flash message key
 * @param mixed $default - Default value if key doesn't exist
 * @return mixed - Flash message value or default
 */
function getFlash($key, $default = null) {
    if (isset($_SESSION['_flash'][$key])) {
        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    return $default;
}

/**
 * Check if a session flash message exists
 * 
 * @param string $key - Flash message key
 * @return bool - True if key exists, false otherwise
 */
function hasFlash($key) {
    return isset($_SESSION['_flash'][$key]);
}

/**
 * Set CSRF token in session
 * 
 * @return string - CSRF token
 */
function setCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Get CSRF token from session
 * 
 * @return string|null - CSRF token or null if not set
 */
function getCsrfToken() {
    return isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null;
}

/**
 * Validate CSRF token
 * 
 * @param string $token - CSRF token to validate
 * @return bool - True if valid, false otherwise
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Clear CSRF token from session
 */
function clearCsrfToken() {
    unset($_SESSION['csrf_token']);
}
?>
