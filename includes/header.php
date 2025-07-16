
<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration files
$base_path = __DIR__ . '/../';
require_once $base_path . 'config/config.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth_functions.php';

// Make sure the global variables from config.php are available
global $CATEGORIES, $DEPARTMENTS;

// Initialize common variables if not set
if (!isset($page_title)) {
    $page_title = '';
}

if (!isset($page_specific_css)) {
    $page_specific_css = '';
}

if (!isset($use_chart_js)) {
    $use_chart_js = false;
}

// Determine if we're in the root directory or in a subdirectory for path resolution
$is_in_page_dir = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$base_url = $is_in_page_dir ? '../' : '';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Roboto and Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/style.css">

    <?php if (isset($page_specific_css)): ?>
    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="<?php echo $page_specific_css; ?>">
    <?php endif; ?>

    <!-- Chart.js (for admin dashboard and statistics) -->
    <?php if (isset($use_chart_js) && $use_chart_js): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>
<body>
    <!-- Toast Container for notifications -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11"></div>

    <?php include __DIR__ . '/navbar.php'; ?>

    <main>
        <?php
        // Display flash messages if any
        if (function_exists('displayFlashMessages')) {
            displayFlashMessages();
        }
        ?>
