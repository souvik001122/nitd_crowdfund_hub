<?php
// Application configuration settings
define('SITE_NAME', 'NIT Delhi Crowdfunding');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', 'http://' . $_SERVER['HTTP_HOST']);
define('SESSION_NAME', 'nit_delhi_crowdfunding');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_STUDENT', 'student');
define('ROLE_ALUMNI', 'alumni');
define('ROLE_FACULTY', 'faculty');
define('ROLE_STAFF', 'staff');

// Campaign statuses
define('CAMPAIGN_PENDING', 'pending');
define('CAMPAIGN_APPROVED', 'approved');
define('CAMPAIGN_REJECTED', 'rejected');
define('CAMPAIGN_COMPLETED', 'completed');
define('CAMPAIGN_DRAFT', 'draft');

// Payment statuses
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');

// Default pagination limit
define('DEFAULT_PAGINATION_LIMIT', 10);

// Allowed campaign image types
$ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];

// Categories
$CATEGORIES = [
    'infrastructure' => 'Infrastructure',
    'research' => 'Research Projects',
    'events' => 'Campus Events',
    'student_initiatives' => 'Student Initiatives',
    'sports' => 'Sports Equipment',
    'technology' => 'Technology',
    'community_service' => 'Community Service',
    'scholarships' => 'Scholarships',
    'cultural' => 'Cultural Activities',
    'other' => 'Other'
];

// Departments
$DEPARTMENTS = [
    'computer_science' => 'Computer Science & Engineering',
    'electrical' => 'Electrical Engineering',
    'mechanical' => 'Mechanical Engineering', 
    'civil' => 'Civil Engineering',
    'electronics' => 'Electronics & Communication',
    'humanities' => 'Humanities & Social Sciences',
    'mathematics' => 'Mathematics',
    'physics' => 'Physics',
    'chemistry' => 'Chemistry',
    'admin' => 'Administration',
    'other' => 'Other'
];
?>
