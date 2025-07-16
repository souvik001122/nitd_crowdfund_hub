<?php
// Determine if we're in the root directory or in a subdirectory
$is_in_page_dir = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$base_url = $is_in_page_dir ? '../' : '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
            <span class="text-primary">NIT Delhi</span> Crowdfunding
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'campaigns.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>pages/campaigns.php">Explore Campaigns</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'create_campaign.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>pages/create_campaign.php">Start a Campaign</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>pages/how_it_works.php">How It Works</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> 
                            <?php echo $_SESSION['user_name']; ?>
                            <?php if (isVerified()): ?>
                                <i class="fas fa-check-circle text-primary ms-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Verified"></i>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>pages/dashboard.php?tab=campaigns"><i class="fas fa-bullhorn me-2"></i> My Campaigns</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>pages/dashboard.php?tab=donations"><i class="fas fa-heart me-2"></i> My Donations</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>pages/dashboard.php?tab=profile"><i class="fas fa-user-edit me-2"></i> Edit Profile</a></li>
                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>pages/admin/index.php"><i class="fas fa-user-shield me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>api/auth.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>pages/login.php" class="btn btn-outline-primary me-2">Login</a>
                    <a href="<?php echo $base_url; ?>pages/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
