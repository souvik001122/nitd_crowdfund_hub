<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Campaign Details Page
 */

// Start session
session_start();

// Include configuration files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../config/database.php';

// Database connection
$db = new Database();
$conn = $db->connect();

// Get campaign ID from URL
$campaign_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($campaign_id <= 0) {
    // Invalid campaign ID, redirect to campaigns page
    header('Location: campaigns.php');
    exit;
}

// Fetch campaign details
$stmt = $conn->prepare("
    SELECT c.*, u.name as creator_name, u.email as creator_email, u.is_verified as creator_verified, u.department as creator_department
    FROM campaigns c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = :id
");
$stmt->bindParam(':id', $campaign_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    // Campaign not found, redirect to campaigns page
    header('Location: campaigns.php');
    exit;
}

$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if campaign is viewable
$isOwner = isLoggedIn() && getCurrentUserId() == $campaign['user_id'];
$isAdmin = isLoggedIn() && isAdmin();

if (!$isOwner && !$isAdmin && $campaign['status'] != CAMPAIGN_APPROVED) {
    // Campaign not approved and user is not owner or admin
    header('Location: campaigns.php');
    exit;
}

// Set page title
$page_title = $campaign['title'];

// Get campaign images
$imagesStmt = $conn->prepare("
    SELECT image_path FROM campaign_images 
    WHERE campaign_id = :campaign_id 
    ORDER BY display_order ASC
");
$imagesStmt->bindParam(':campaign_id', $campaign_id);
$imagesStmt->execute();
$campaignImages = $imagesStmt->fetchAll(PDO::FETCH_COLUMN);

// If no images, use featured image
if (empty($campaignImages) && !empty($campaign['featured_image'])) {
    $campaignImages[] = $campaign['featured_image'];
}

// Get campaign updates
$updatesStmt = $conn->prepare("
    SELECT cu.*, u.name as author_name, u.is_verified as author_verified
    FROM campaign_updates cu
    JOIN users u ON cu.user_id = u.id
    WHERE cu.campaign_id = :campaign_id
    ORDER BY cu.created_at DESC
");
$updatesStmt->bindParam(':campaign_id', $campaign_id);
$updatesStmt->execute();
$campaignUpdates = $updatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get campaign comments
$commentsStmt = $conn->prepare("
    SELECT c.*, u.name as author_name, u.is_verified as author_verified
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.campaign_id = :campaign_id AND c.is_approved = 1
    ORDER BY c.created_at DESC
");
$commentsStmt->bindParam(':campaign_id', $campaign_id);
$commentsStmt->execute();
$campaignComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get donation count
$donationCountStmt = $conn->prepare("
    SELECT COUNT(*) as count FROM donations
    WHERE campaign_id = :campaign_id AND status = 'completed'
");
$donationCountStmt->bindParam(':campaign_id', $campaign_id);
$donationCountStmt->execute();
$donationCount = $donationCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

// Check if user has saved this campaign
$hasSaved = false;
if (isLoggedIn()) {
    $savedStmt = $conn->prepare("
        SELECT id FROM saved_campaigns
        WHERE user_id = :user_id AND campaign_id = :campaign_id
    ");
    $user_id = getCurrentUserId();
    $savedStmt->bindParam(':user_id', $user_id);
    $savedStmt->bindParam(':campaign_id', $campaign_id);
    $savedStmt->execute();
    $hasSaved = $savedStmt->rowCount() > 0;
}

// Include header
include_once '../includes/header.php';
?>

<!-- Campaign Header -->
<div class="campaign-header">
    <?php if (!empty($campaignImages)): ?>
        <img src="<?php echo htmlspecialchars($campaignImages[0]); ?>" alt="<?php echo htmlspecialchars($campaign['title']); ?>" id="main-campaign-image">
    <?php else: ?>
        <img src="https://images.unsplash.com/photo-1591901206069-ed60c4429a2e" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
    <?php endif; ?>
    
    <div class="campaign-overlay">
        <div class="container">
            <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($campaign['title']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($campaign['short_description']); ?></p>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Campaign Meta -->
            <div class="campaign-meta mb-4">
                <div class="meta-item">
                    <i class="fas fa-user me-1"></i> Created by <?php echo htmlspecialchars($campaign['creator_name']); ?>
                    <?php if ($campaign['creator_verified']): ?>
                        <i class="fas fa-check-circle text-primary ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified User"></i>
                    <?php endif; ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-folder me-1"></i> <?php echo getCategoryName($campaign['category']); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-building me-1"></i> <?php echo getDepartmentName($campaign['department']); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt me-1"></i> Created on <?php echo formatDate($campaign['created_at']); ?>
                </div>
            </div>
            
            <?php if (!empty($campaignImages) && count($campaignImages) > 1): ?>
                <!-- Image Gallery -->
                <div class="mb-4">
                    <div class="row">
                        <?php foreach ($campaignImages as $index => $image): ?>
                            <div class="col-md-2 col-3 mb-3">
                                <div class="gallery-thumbnail <?php echo ($index === 0) ? 'active' : ''; ?>" data-full-image="<?php echo htmlspecialchars($image); ?>">
                                    <img src="<?php echo htmlspecialchars($image); ?>" class="img-fluid rounded" alt="Campaign image">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Campaign Description -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">About This Campaign</h4>
                </div>
                <div class="card-body">
                    <div class="campaign-description">
                        <?php echo nl2br(htmlspecialchars($campaign['description'])); ?>
                    </div>
                </div>
            </div>
            
            <!-- Budget Breakdown -->
            <?php if (!empty($campaign['budget_breakdown'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">Budget Breakdown</h4>
                    </div>
                    <div class="card-body">
                        <div class="budget-breakdown">
                            <?php
                            $budget = json_decode($campaign['budget_breakdown'], true);
                            if (is_array($budget) && !empty($budget)):
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($budget as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                    <td class="text-end"><?php echo formatCurrency($item['amount']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th>Total</th>
                                                <th class="text-end"><?php echo formatCurrency($campaign['goal']); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Detailed budget breakdown not available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Team Members -->
            <?php if (!empty($campaign['team_members'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">Campaign Team</h4>
                    </div>
                    <div class="card-body">
                        <div class="team-members">
                            <?php
                            $team = json_decode($campaign['team_members'], true);
                            if (is_array($team) && !empty($team)):
                            ?>
                                <div class="row">
                                    <?php foreach ($team as $member): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                                                                <i class="fas fa-user fa-2x text-primary"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($member['role']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Team information not available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Campaign Updates -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Updates</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($campaignUpdates)): ?>
                        <p class="text-muted">No updates yet for this campaign.</p>
                    <?php else: ?>
                        <div class="campaign-updates">
                            <?php foreach ($campaignUpdates as $index => $update): ?>
                                <div class="update-item <?php echo ($index < count($campaignUpdates) - 1) ? 'border-bottom pb-3 mb-3' : ''; ?>">
                                    <h5><?php echo htmlspecialchars($update['title']); ?></h5>
                                    <div class="d-flex align-items-center mb-2 text-muted small">
                                        <div class="me-3">
                                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($update['author_name']); ?>
                                            <?php if ($update['author_verified']): ?>
                                                <i class="fas fa-check-circle text-primary ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified User"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo formatDate($update['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="update-content">
                                        <?php echo nl2br(htmlspecialchars($update['content'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($isOwner): ?>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                                <i class="fas fa-plus-circle me-1"></i> Add Update
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Comments (<?php echo count($campaignComments); ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <form id="comment-form" class="mb-4">
                            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                            <div class="mb-3">
                                <label for="comment-text" class="form-label">Add a Comment</label>
                                <textarea class="form-control" id="comment-text" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i> Please <a href="login.php">log in</a> to leave a comment.
                        </div>
                    <?php endif; ?>
                    
                    <div id="comments-list">
                        <?php if (empty($campaignComments)): ?>
                            <p class="text-muted">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                            <?php foreach ($campaignComments as $comment): ?>
                                <div class="comment-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="comment-avatar me-2">
                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo !empty($comment['author_name']) ? htmlspecialchars($comment['author_name']) : 'Anonymous'; ?></h6>
                                                <small class="text-muted"><?php echo getTimeElapsed($comment['created_at']); ?></small>
                                            </div>
                                        </div>
                                        <?php if ($isAdmin || $isOwner): ?>
                                            <div>
                                                <button class="btn btn-sm text-danger delete-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="campaign-sidebar">
                <!-- Campaign Progress -->
                <div class="card campaign-progress mb-4">
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 15px;">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="progress-stats row text-center mb-4">
                            <div class="col stat-item">
                                <div class="stat-value"><?php echo formatCurrency($campaign['amount_raised']); ?></div>
                                <div class="stat-label">Raised</div>
                            </div>
                            <div class="col stat-item">
                                <div class="stat-value"><?php echo formatCurrency($campaign['goal']); ?></div>
                                <div class="stat-label">Goal</div>
                            </div>
                            <div class="col stat-item">
                                <div class="stat-value"><?php echo $donationCount; ?></div>
                                <div class="stat-label">Donors</div>
                            </div>
                        </div>
                        
                        <?php if ($campaign['status'] === CAMPAIGN_APPROVED && daysRemaining($campaign['end_date']) > 0): ?>
                            <div class="d-grid mb-3">
                                <a href="donate.php?campaign_id=<?php echo $campaign_id; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-heart me-2"></i> Donate Now
                                </a>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary flex-grow-1 me-2 save-campaign" data-campaign-id="<?php echo $campaign_id; ?>" data-saved="<?php echo $hasSaved ? 'true' : 'false'; ?>">
                                    <i class="<?php echo $hasSaved ? 'fas' : 'far'; ?> fa-bookmark me-1"></i> <?php echo $hasSaved ? 'Saved' : 'Save'; ?>
                                </button>
                                <button class="btn btn-outline-primary flex-grow-1 share-button" data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <i class="fas fa-share-alt me-1"></i> Share
                                </button>
                            </div>
                        <?php elseif ($campaign['status'] === CAMPAIGN_COMPLETED || daysRemaining($campaign['end_date']) == 0): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i> This campaign has ended.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i> This campaign is not active.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <?php 
                                $days = daysRemaining($campaign['end_date']);
                                if ($days > 0): ?>
                                    <i class="fas fa-clock me-1"></i> <?php echo $days; ?> days left
                                <?php else: ?>
                                    <i class="fas fa-calendar-check me-1"></i> Campaign Ended
                                <?php endif; ?>
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i> Ends <?php echo formatDate($campaign['end_date']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Donation Tiers -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Donation Tiers</h5>
                    </div>
                    <div class="card-body">
                        <div class="donation-tiers">
                            <div class="donation-tier mb-3 p-3 border rounded">
                                <h6 class="mb-2">Supporter <span class="badge bg-primary ms-1">₹500</span></h6>
                                <p class="mb-0 small">Every contribution helps bring this project to life.</p>
                                <div class="mt-2">
                                    <a href="donate.php?campaign_id=<?php echo $campaign_id; ?>&amount=500" class="btn btn-sm btn-outline-primary">Donate ₹500</a>
                                </div>
                            </div>
                            
                            <div class="donation-tier mb-3 p-3 border rounded">
                                <h6 class="mb-2">Backer <span class="badge bg-primary ms-1">₹1,000</span></h6>
                                <p class="mb-0 small">A significant contribution to help reach our goals faster.</p>
                                <div class="mt-2">
                                    <a href="donate.php?campaign_id=<?php echo $campaign_id; ?>&amount=1000" class="btn btn-sm btn-outline-primary">Donate ₹1,000</a>
                                </div>
                            </div>
                            
                            <div class="donation-tier p-3 border rounded">
                                <h6 class="mb-2">Champion <span class="badge bg-primary ms-1">₹5,000</span></h6>
                                <p class="mb-0 small">Be a champion for this cause with a major contribution.</p>
                                <div class="mt-2">
                                    <a href="donate.php?campaign_id=<?php echo $campaign_id; ?>&amount=5000" class="btn btn-sm btn-outline-primary">Donate ₹5,000</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="donate.php?campaign_id=<?php echo $campaign_id; ?>" class="btn btn-link">Customize your donation</a>
                        </div>
                    </div>
                </div>
                
                <!-- Creator Info -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">About the Creator</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                                    <i class="fas fa-user fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-1">
                                    <?php echo htmlspecialchars($campaign['creator_name']); ?>
                                    <?php if ($campaign['creator_verified']): ?>
                                        <i class="fas fa-check-circle text-primary ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified User"></i>
                                    <?php endif; ?>
                                </h5>
                                <p class="mb-0 text-muted"><?php echo getDepartmentName($campaign['creator_department']); ?></p>
                            </div>
                        </div>
                        
                        <?php if ($isOwner || $isAdmin): ?>
                            <div class="creator-actions mt-3">
                                <?php if ($isOwner): ?>
                                    <a href="create_campaign.php?edit=<?php echo $campaign_id; ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-edit me-1"></i> Edit Campaign
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($isAdmin): ?>
                                    <div class="btn-group w-100">
                                        <button class="btn btn-sm btn-outline-success campaign-approval-btn" data-action="approve" data-campaign-id="<?php echo $campaign_id; ?>">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger campaign-approval-btn" data-action="reject" data-campaign-id="<?php echo $campaign_id; ?>">
                                            <i class="fas fa-times me-1"></i> Reject
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Report Campaign -->
                <?php if (isLoggedIn() && !$isOwner): ?>
                    <div class="card">
                        <div class="card-body">
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="fas fa-flag me-1"></i> Report Campaign
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Share Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Share this campaign with your network:</p>
                
                <div class="d-flex justify-content-between mb-4">
                    <a href="#" class="btn btn-outline-primary share-button" data-platform="facebook" data-title="<?php echo htmlspecialchars($campaign['title']); ?>">
                        <i class="fab fa-facebook-f me-2"></i> Facebook
                    </a>
                    <a href="#" class="btn btn-outline-info share-button" data-platform="twitter" data-title="<?php echo htmlspecialchars($campaign['title']); ?>">
                        <i class="fab fa-twitter me-2"></i> Twitter
                    </a>
                    <a href="#" class="btn btn-outline-success share-button" data-platform="whatsapp" data-title="<?php echo htmlspecialchars($campaign['title']); ?>">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp
                    </a>
                </div>
                
                <div class="input-group">
                    <input type="text" class="form-control" id="campaign-url" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copy-url">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="report-form">
                    <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                    
                    <div class="mb-3">
                        <label for="report-reason" class="form-label">Reason for Reporting</label>
                        <select class="form-select" id="report-reason" name="reason" required>
                            <option value="" selected disabled>Select a reason</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="duplicate">Duplicate campaign</option>
                            <option value="fraud">Suspected fraud</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="report-details" class="form-label">Additional Details</label>
                        <textarea class="form-control" id="report-details" name="details" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="report-form" class="btn btn-danger">Submit Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Update Modal -->
<?php if ($isOwner): ?>
    <div class="modal fade" id="addUpdateModal" tabindex="-1" aria-labelledby="addUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUpdateModalLabel">Add Campaign Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="update-form" action="../api/campaigns.php" method="POST">
                        <input type="hidden" name="action" value="add_update">
                        <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                        
                        <div class="mb-3">
                            <label for="update-title" class="form-label">Update Title</label>
                            <input type="text" class="form-control" id="update-title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="update-content" class="form-label">Update Content</label>
                            <textarea class="form-control" id="update-content" name="content" rows="5" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="update-form" class="btn btn-primary">Post Update</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy campaign URL
    const copyUrlButton = document.getElementById('copy-url');
    if (copyUrlButton) {
        copyUrlButton.addEventListener('click', function() {
            const campaignUrl = document.getElementById('campaign-url');
            campaignUrl.select();
            document.execCommand('copy');
            showToast('Link copied to clipboard', 'success');
        });
    }
});
</script>

<?php
// Page specific JavaScript
$page_specific_js = ['../assets/js/campaign.js'];

// Include footer
include_once '../includes/footer.php';
?>
