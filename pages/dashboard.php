<?php
/**
 * NIT Delhi Crowdfunding Platform
 * User Dashboard
 */

// Start session
session_start();

// Include configuration files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Database connection
$db = new Database();
$conn = $db->connect();

// Get current user ID
$user_id = getCurrentUserId();

// Get current tab from query parameter (default to overview)
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'overview';

// Set page title
$page_title = 'Dashboard';

// Include chart.js for dashboard charts
$use_chart_js = true;

// Set page specific JavaScript
$page_specific_js = [
    '../assets/js/dashboard.js',
    '../assets/js/campaign.js',
    '../assets/js/donation.js'
];

// Fetch user data
$user = getUserById($user_id);

// Fetch user's campaigns
$campaigns_stmt = $conn->prepare("
    SELECT * FROM campaigns 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
");
$campaigns_stmt->bindParam(':user_id', $user_id);
$campaigns_stmt->execute();
$campaigns = $campaigns_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's donations
$donations_stmt = $conn->prepare("
    SELECT d.*, c.title as campaign_title 
    FROM donations d
    JOIN campaigns c ON d.campaign_id = c.id
    WHERE d.user_id = :user_id 
    ORDER BY d.created_at DESC
");
$donations_stmt->bindParam(':user_id', $user_id);
$donations_stmt->execute();
$donations = $donations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate dashboard stats
$total_campaigns = count($campaigns);
$total_donations = count($donations);

$total_raised = 0;
foreach ($campaigns as $campaign) {
    $total_raised += $campaign['amount_raised'];
}

$total_donated = 0;
foreach ($donations as $donation) {
    $total_donated += $donation['amount'];
}

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="dashboard-sidebar sticky-top">
                <div class="text-center mb-4">
                    <div class="avatar-placeholder rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-primary"></i>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div>
                        <?php echo getUserRoleBadge($user['role']); ?>
                        <?php echo getVerificationBadge($user['is_verified']); ?>
                    </div>
                </div>
                
                <div class="list-group">
                    <a href="?tab=overview" class="list-group-item list-group-item-action <?php echo ($current_tab === 'overview') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i> Overview
                    </a>
                    <a href="?tab=campaigns" class="list-group-item list-group-item-action <?php echo ($current_tab === 'campaigns') ? 'active' : ''; ?>">
                        <i class="fas fa-bullhorn me-2"></i> My Campaigns
                    </a>
                    <a href="?tab=donations" class="list-group-item list-group-item-action <?php echo ($current_tab === 'donations') ? 'active' : ''; ?>">
                        <i class="fas fa-heart me-2"></i> My Donations
                    </a>
                    <a href="?tab=saved" class="list-group-item list-group-item-action <?php echo ($current_tab === 'saved') ? 'active' : ''; ?>">
                        <i class="fas fa-bookmark me-2"></i> Saved Campaigns
                    </a>
                    <a href="?tab=profile" class="list-group-item list-group-item-action <?php echo ($current_tab === 'profile') ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit me-2"></i> Edit Profile
                    </a>
                    <a href="../api/auth.php?action=logout" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if ($current_tab === 'overview'): ?>
                <!-- Overview Tab -->
                <div class="dashboard-tab active">
                    <h2 class="mb-4">Dashboard Overview</h2>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">My Campaigns</h6>
                                    <h2 class="mb-0"><?php echo $total_campaigns; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Total Raised</h6>
                                    <h2 class="mb-0"><?php echo formatCurrency($total_raised); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">My Donations</h6>
                                    <h2 class="mb-0"><?php echo $total_donations; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Total Donated</h6>
                                    <h2 class="mb-0"><?php echo formatCurrency($total_donated); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Donation History</h5>
                                </div>
                                <div class="card-body">
                                    <div style="height: 250px;">
                                        <canvas id="donation-history-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Campaign Performance</h5>
                                </div>
                                <div class="card-body">
                                    <div style="height: 250px;">
                                        <canvas id="campaign-performance-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-feed">
                                <?php if (empty($donations) && empty($campaigns)): ?>
                                    <p class="text-center text-muted">No recent activity to display</p>
                                <?php else: ?>
                                    <?php
                                    // Combine donations and campaigns for activity feed
                                    $activities = [];
                                    
                                    foreach ($donations as $donation) {
                                        $activities[] = [
                                            'type' => 'donation',
                                            'date' => $donation['created_at'],
                                            'data' => $donation
                                        ];
                                    }
                                    
                                    foreach ($campaigns as $campaign) {
                                        $activities[] = [
                                            'type' => 'campaign',
                                            'date' => $campaign['created_at'],
                                            'data' => $campaign
                                        ];
                                    }
                                    
                                    // Sort by date, most recent first
                                    usort($activities, function($a, $b) {
                                        return strtotime($b['date']) - strtotime($a['date']);
                                    });
                                    
                                    // Display 5 most recent activities
                                    $count = 0;
                                    foreach ($activities as $activity):
                                        if ($count >= 5) break;
                                        $count++;
                                    ?>
                                        <div class="activity-item d-flex pb-3 mb-3 <?php echo ($count < 5) ? 'border-bottom' : ''; ?>">
                                            <?php if ($activity['type'] === 'donation'): ?>
                                                <div class="activity-icon me-3 bg-primary text-white rounded-circle p-2">
                                                    <i class="fas fa-heart"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p class="mb-1">You donated <strong><?php echo formatCurrency($activity['data']['amount']); ?></strong> to <a href="campaign_details.php?id=<?php echo $activity['data']['campaign_id']; ?>"><?php echo htmlspecialchars($activity['data']['campaign_title']); ?></a></p>
                                                    <small class="text-muted"><?php echo getTimeElapsed($activity['data']['created_at']); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <div class="activity-icon me-3 bg-success text-white rounded-circle p-2">
                                                    <i class="fas fa-bullhorn"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p class="mb-1">You <?php echo ($activity['data']['status'] === CAMPAIGN_DRAFT) ? 'created a draft campaign' : 'launched a campaign'; ?> <a href="campaign_details.php?id=<?php echo $activity['data']['id']; ?>"><?php echo htmlspecialchars($activity['data']['title']); ?></a></p>
                                                    <small class="text-muted"><?php echo getTimeElapsed($activity['data']['created_at']); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <a href="create_campaign.php" class="card h-100 text-decoration-none">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="me-3 text-primary">
                                                <i class="fas fa-plus-circle fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Create New Campaign</h6>
                                                <p class="mb-0 text-muted small">Start fundraising for your project</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <a href="campaigns.php" class="card h-100 text-decoration-none">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="me-3 text-success">
                                                <i class="fas fa-search fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Explore Campaigns</h6>
                                                <p class="mb-0 text-muted small">Discover projects to support</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <a href="?tab=profile" class="card h-100 text-decoration-none">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="me-3 text-info">
                                                <i class="fas fa-user-edit fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Update Profile</h6>
                                                <p class="mb-0 text-muted small">Keep your information current</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <a href="?tab=donations" class="card h-100 text-decoration-none">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="me-3 text-warning">
                                                <i class="fas fa-file-alt fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Download Receipts</h6>
                                                <p class="mb-0 text-muted small">Get donation certificates</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($current_tab === 'campaigns'): ?>
                <!-- Campaigns Tab -->
                <div class="dashboard-tab active">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">My Campaigns</h2>
                        <a href="create_campaign.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Create New Campaign
                        </a>
                    </div>
                    
                    <?php if (empty($campaigns)): ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                                <h4>No Campaigns Yet</h4>
                                <p class="text-muted">You haven't created any campaigns yet.</p>
                                <a href="create_campaign.php" class="btn btn-primary mt-3">Create Your First Campaign</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header bg-white">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#all-campaigns">All Campaigns</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#active-campaigns">Active</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#draft-campaigns">Drafts</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#completed-campaigns">Completed</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="all-campaigns">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Campaign</th>
                                                        <th>Status</th>
                                                        <th>Progress</th>
                                                        <th>End Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($campaigns as $campaign): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <?php if (!empty($campaign['featured_image'])): ?>
                                                                            <img src="<?php echo htmlspecialchars($campaign['featured_image']); ?>" class="rounded" width="50" height="50" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                                                        <?php else: ?>
                                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                <i class="fas fa-image text-muted"></i>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars(truncateText($campaign['title'], 30)); ?></h6>
                                                                        <small class="text-muted"><?php echo getCategoryName($campaign['category']); ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?php echo getCampaignStatusBadge($campaign['status']); ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 6px; width: 100px;">
                                                                    <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <small class="text-muted"><?php echo formatCurrency($campaign['amount_raised']); ?> of <?php echo formatCurrency($campaign['goal']); ?></small>
                                                            </td>
                                                            <td>
                                                                <?php if ($campaign['status'] === CAMPAIGN_DRAFT): ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php else: ?>
                                                                    <?php
                                                                    $days = daysRemaining($campaign['end_date']);
                                                                    if ($days > 0): ?>
                                                                        <span class="text-nowrap"><?php echo $days; ?> days left</span>
                                                                    <?php else: ?>
                                                                        <span class="text-danger">Ended</span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li><a class="dropdown-item" href="create_campaign.php?edit=<?php echo $campaign['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                                                        <?php if ($campaign['status'] === CAMPAIGN_DRAFT || $campaign['status'] === CAMPAIGN_REJECTED): ?>
                                                                            <li><a class="dropdown-item campaign-action" href="#" data-action="publish" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-paper-plane me-2"></i> Publish</a></li>
                                                                        <?php elseif ($campaign['status'] === CAMPAIGN_APPROVED): ?>
                                                                            <li><a class="dropdown-item campaign-action" href="#" data-action="unpublish" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-pause me-2"></i> Unpublish</a></li>
                                                                        <?php endif; ?>
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li><a class="dropdown-item text-danger campaign-action" href="#" data-action="delete" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="active-campaigns">
                                        <?php
                                        $activeCampaigns = array_filter($campaigns, function($campaign) {
                                            return $campaign['status'] === CAMPAIGN_APPROVED;
                                        });
                                        
                                        if (empty($activeCampaigns)):
                                        ?>
                                            <div class="text-center py-4">
                                                <p class="text-muted">No active campaigns</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Campaign</th>
                                                            <th>Progress</th>
                                                            <th>End Date</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($activeCampaigns as $campaign): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <?php if (!empty($campaign['featured_image'])): ?>
                                                                                <img src="<?php echo htmlspecialchars($campaign['featured_image']); ?>" class="rounded" width="50" height="50" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                                                            <?php else: ?>
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                    <i class="fas fa-image text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="mb-0"><?php echo htmlspecialchars(truncateText($campaign['title'], 30)); ?></h6>
                                                                            <small class="text-muted"><?php echo getCategoryName($campaign['category']); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="progress" style="height: 6px; width: 100px;">
                                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                    <small class="text-muted"><?php echo formatCurrency($campaign['amount_raised']); ?> of <?php echo formatCurrency($campaign['goal']); ?></small>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    $days = daysRemaining($campaign['end_date']);
                                                                    if ($days > 0): ?>
                                                                        <span class="text-nowrap"><?php echo $days; ?> days left</span>
                                                                    <?php else: ?>
                                                                        <span class="text-danger">Ended</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li><a class="dropdown-item" href="create_campaign.php?edit=<?php echo $campaign['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                                                            <li><a class="dropdown-item campaign-action" href="#" data-action="unpublish" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-pause me-2"></i> Unpublish</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="draft-campaigns">
                                        <?php
                                        $draftCampaigns = array_filter($campaigns, function($campaign) {
                                            return $campaign['status'] === CAMPAIGN_DRAFT;
                                        });
                                        
                                        if (empty($draftCampaigns)):
                                        ?>
                                            <div class="text-center py-4">
                                                <p class="text-muted">No draft campaigns</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Campaign</th>
                                                            <th>Last Updated</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($draftCampaigns as $campaign): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <?php if (!empty($campaign['featured_image'])): ?>
                                                                                <img src="<?php echo htmlspecialchars($campaign['featured_image']); ?>" class="rounded" width="50" height="50" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                                                            <?php else: ?>
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                    <i class="fas fa-image text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="mb-0"><?php echo htmlspecialchars(truncateText($campaign['title'], 30)); ?></h6>
                                                                            <small class="text-muted"><?php echo getCategoryName($campaign['category']); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="text-nowrap"><?php echo getTimeElapsed($campaign['updated_at']); ?></span>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <a href="create_campaign.php?edit=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li><a class="dropdown-item campaign-action" href="#" data-action="publish" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-paper-plane me-2"></i> Publish</a></li>
                                                                            <li><hr class="dropdown-divider"></li>
                                                                            <li><a class="dropdown-item text-danger campaign-action" href="#" data-action="delete" data-campaign-id="<?php echo $campaign['id']; ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="completed-campaigns">
                                        <?php
                                        $completedCampaigns = array_filter($campaigns, function($campaign) {
                                            return $campaign['status'] === CAMPAIGN_COMPLETED;
                                        });
                                        
                                        if (empty($completedCampaigns)):
                                        ?>
                                            <div class="text-center py-4">
                                                <p class="text-muted">No completed campaigns</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Campaign</th>
                                                            <th>Final Amount</th>
                                                            <th>Ended</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($completedCampaigns as $campaign): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <?php if (!empty($campaign['featured_image'])): ?>
                                                                                <img src="<?php echo htmlspecialchars($campaign['featured_image']); ?>" class="rounded" width="50" height="50" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                                                            <?php else: ?>
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                    <i class="fas fa-image text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="mb-0"><?php echo htmlspecialchars(truncateText($campaign['title'], 30)); ?></h6>
                                                                            <small class="text-muted"><?php echo getCategoryName($campaign['category']); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <strong><?php echo formatCurrency($campaign['amount_raised']); ?></strong>
                                                                    <div class="progress mt-1" style="height: 6px; width: 100px;">
                                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                    <small class="text-muted"><?php echo round(calculateFundingPercentage($campaign['amount_raised'], $campaign['goal'])); ?>% of goal</small>
                                                                </td>
                                                                <td>
                                                                    <span class="text-nowrap"><?php echo formatDate($campaign['end_date']); ?></span>
                                                                </td>
                                                                <td>
                                                                    <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_tab === 'donations'): ?>
                <!-- Donations Tab -->
                <div class="dashboard-tab active">
                    <h2 class="mb-4">My Donations</h2>
                    
                    <?php if (empty($donations)): ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                                <h4>No Donations Yet</h4>
                                <p class="text-muted">You haven't made any donations yet.</p>
                                <a href="campaigns.php" class="btn btn-primary mt-3">Explore Campaigns</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Donation Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <h3><?php echo formatCurrency($total_donated); ?></h3>
                                        <p class="text-muted mb-0">Total Donated</p>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <h3><?php echo count($donations); ?></h3>
                                        <p class="text-muted mb-0">Campaigns Supported</p>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $recurring_count = 0;
                                        foreach ($donations as $donation) {
                                            if (isset($donation['is_recurring']) && $donation['is_recurring']) {
                                                $recurring_count++;
                                            }
                                        }
                                        ?>
                                        <h3><?php echo $recurring_count; ?></h3>
                                        <p class="text-muted mb-0">Recurring Donations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Donation History</h5>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                                
                                <div class="collapse mt-3" id="filterCollapse">
                                    <form id="donation-filter-form" action="" method="GET">
                                        <input type="hidden" name="tab" value="donations">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="filter-date-from" class="form-label">From Date</label>
                                                <input type="date" class="form-control" id="filter-date-from" name="date_from">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="filter-date-to" class="form-label">To Date</label>
                                                <input type="date" class="form-control" id="filter-date-to" name="date_to">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="filter-amount" class="form-label">Min Amount</label>
                                                <input type="number" class="form-control" id="filter-amount" name="min_amount">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                                <button type="button" id="clear-filters" class="btn btn-outline-secondary">Clear</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Campaign</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($donations as $donation): ?>
                                                <tr>
                                                    <td>
                                                        <a href="campaign_details.php?id=<?php echo $donation['campaign_id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars(truncateText($donation['campaign_title'], 30)); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo formatCurrency($donation['amount']); ?></strong>
                                                        <?php if (isset($donation['is_recurring']) && $donation['is_recurring']): ?>
                                                            <span class="badge bg-info ms-1">Recurring</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo formatDate($donation['created_at']); ?></td>
                                                    <td><?php echo getPaymentStatusBadge($donation['status']); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary download-receipt" data-donation-id="<?php echo $donation['id']; ?>">
                                                                <i class="fas fa-download me-1"></i> Receipt
                                                            </button>
                                                            <?php if (isset($donation['is_recurring']) && $donation['is_recurring'] && $donation['subscription_status'] !== 'cancelled'): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-danger cancel-recurring" data-subscription-id="<?php echo $donation['subscription_id']; ?>">
                                                                    Cancel
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_tab === 'saved'): ?>
                <!-- Saved Campaigns Tab -->
                <div class="dashboard-tab active">
                    <h2 class="mb-4">Saved Campaigns</h2>
                    
                    <?php
                    // Fetch saved campaigns
                    $saved_stmt = $conn->prepare("
                        SELECT c.* FROM saved_campaigns sc
                        JOIN campaigns c ON sc.campaign_id = c.id
                        WHERE sc.user_id = :user_id AND c.status = :status
                        ORDER BY sc.created_at DESC
                    ");
                    $saved_stmt->bindParam(':user_id', $user_id);
                    $approved_status = CAMPAIGN_APPROVED;
                    $saved_stmt->bindParam(':status', $approved_status);
                    $saved_stmt->execute();
                    $savedCampaigns = $saved_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($savedCampaigns)):
                    ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-bookmark fa-4x text-muted mb-3"></i>
                                <h4>No Saved Campaigns</h4>
                                <p class="text-muted">You haven't saved any campaigns yet.</p>
                                <a href="campaigns.php" class="btn btn-primary mt-3">Explore Campaigns</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($savedCampaigns as $campaign): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card campaign-card h-100">
                                        <div class="position-relative">
                                            <img src="<?php echo !empty($campaign['featured_image']) ? htmlspecialchars($campaign['featured_image']) : 'https://images.unsplash.com/photo-1591901206069-ed60c4429a2e'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                            <span class="badge bg-primary category-badge"><?php echo getCategoryName($campaign['category']); ?></span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars(truncateText($campaign['short_description'], 100)); ?></p>
                                            <div class="progress mb-3">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <span class="fw-bold"><?php echo formatCurrency($campaign['amount_raised']); ?> raised</span>
                                                <span class="text-muted"><?php echo round(calculateFundingPercentage($campaign['amount_raised'], $campaign['goal'])); ?>% of <?php echo formatCurrency($campaign['goal']); ?></span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <?php
                                                    $days = daysRemaining($campaign['end_date']);
                                                    if ($days > 0): ?>
                                                        <i class="fas fa-clock me-1"></i> <?php echo $days; ?> days left
                                                    <?php else: ?>
                                                        <span class="text-danger"><i class="fas fa-clock me-1"></i> Ended</span>
                                                    <?php endif; ?>
                                                </span>
                                                <div>
                                                    <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                                    <button class="btn btn-sm btn-outline-danger unsave-campaign" data-campaign-id="<?php echo $campaign['id']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_tab === 'profile'): ?>
                <!-- Profile Tab -->
                <div class="dashboard-tab active">
                    <h2 class="mb-4">Edit Profile</h2>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <form id="profile-form" action="../api/users.php" method="POST">
                                        <input type="hidden" name="action" value="update_profile">
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                            <small class="text-muted">Email address cannot be changed</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-select" id="department" name="department">
                                                <?php foreach ($DEPARTMENTS as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo ($user['department'] === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : ''; ?></textarea>
                                            <small class="text-muted">Tell us a bit about yourself</small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <form id="password-form" action="../api/users.php" method="POST">
                                        <input type="hidden" name="action" value="change_password">
                                        
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <small class="text-muted">Minimum 8 characters</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Account Status</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Account Type
                                            <span><?php echo getUserRoleBadge($user['role']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Verification Status
                                            <span><?php echo getVerificationBadge($user['is_verified']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Member Since
                                            <span><?php echo formatDate($user['created_at']); ?></span>
                                        </li>
                                    </ul>
                                    
                                    <?php if (!$user['is_verified'] && $user['is_nit_delhi']): ?>
                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="fas fa-info-circle me-2"></i> Your account is pending verification. This typically takes 1-2 business days.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Account Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?php if (!$user['is_verified'] && !$user['is_nit_delhi']): ?>
                                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#verificationModal">
                                                <i class="fas fa-check-circle me-2"></i> Request Verification
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                            <i class="fas fa-user-slash me-2"></i> Deactivate Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Request Verification Modal -->
                <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="verificationModalLabel">Request Account Verification</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Account verification is available for NIT Delhi students, faculty, and staff. Please provide your NIT Delhi affiliation details below:</p>
                                
                                <form id="verification-form" action="../api/users.php" method="POST">
                                    <input type="hidden" name="action" value="request_verification">
                                    
                                    <div class="mb-3">
                                        <label for="id_number" class="form-label">ID Number / Roll Number</label>
                                        <input type="text" class="form-control" id="id_number" name="id_number" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="affiliation" class="form-label">Affiliation</label>
                                        <select class="form-select" id="affiliation" name="affiliation" required>
                                            <option value="student">Student</option>
                                            <option value="faculty">Faculty</option>
                                            <option value="staff">Staff</option>
                                            <option value="alumni">Alumni</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="verification_proof" class="form-label">Additional Information</label>
                                        <textarea class="form-control" id="verification_proof" name="verification_proof" rows="3" placeholder="Please provide any additional information that may help in the verification process."></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" form="verification-form" class="btn btn-primary">Submit Request</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Deactivate Account Modal -->
                <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deactivateModalLabel">Deactivate Account</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Warning: Deactivating your account is permanent and cannot be undone.
                                </div>
                                
                                <p>Deactivating your account will:</p>
                                <ul>
                                    <li>Remove your access to the platform</li>
                                    <li>Archive all your campaigns and donations</li>
                                    <li>Delete your personal information from our system</li>
                                </ul>
                                
                                <p>Are you sure you want to proceed?</p>
                                
                                <form id="deactivate-form" action="../api/users.php" method="POST">
                                    <input type="hidden" name="action" value="deactivate_account">
                                    
                                    <div class="mb-3">
                                        <label for="deactivate_reason" class="form-label">Reason for leaving (optional)</label>
                                        <textarea class="form-control" id="deactivate_reason" name="reason" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="confirm_deactivate" name="confirm" required>
                                        <label class="form-check-label" for="confirm_deactivate">I understand that this action is permanent</label>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirm" class="form-label">Enter your password to confirm</label>
                                        <input type="password" class="form-control" id="password_confirm" name="password" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" form="deactivate-form" class="btn btn-danger">Deactivate Account</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>
