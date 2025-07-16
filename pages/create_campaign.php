<?php
// Ensure no output before session start
ob_start();
session_start();

/**
 * NIT Delhi Crowdfunding Platform
 * Campaign Creation Page
 */

// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Set flash message
    setFlashMessage('Please log in to create a campaign', 'warning');
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Database connection
$db = new Database();
$conn = $db->connect();

// Set page title
$page_title = 'Create Campaign';
$isEdit = false;
$campaign = null;

// Check if this is an edit request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $campaign_id = (int)$_GET['edit'];
    
    // Fetch campaign details
    $stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = :id");
    $stmt->bindParam(':id', $campaign_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user is the owner of this campaign
        if ($campaign['user_id'] != getCurrentUserId() && !isAdmin()) {
            setFlashMessage('You do not have permission to edit this campaign', 'danger');
            header('Location: dashboard.php?tab=campaigns');
            exit;
        }
        
        // Update page title and set edit mode
        $page_title = 'Edit Campaign';
        $isEdit = true;
    }
}

// Set page specific JavaScript
$page_specific_js = ['../assets/js/campaign.js'];

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h2 class="mb-0"><?php echo $isEdit ? 'Edit Campaign' : 'Create a Campaign'; ?></h2>
                </div>
                <div class="card-body p-4">
                    <form id="campaign-form" action="../api/campaigns.php" method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?php echo $isEdit ? 'update_campaign' : 'create_campaign'; ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="campaign_id" id="campaign-id" value="<?php echo $campaign['id']; ?>">
                        <?php endif; ?>
                        
                        <!-- Step Indicators -->
                        <div class="steps mb-4">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span class="step-label active" data-step="1">Basic Info</span>
                                <span class="step-label" data-step="2">Details</span>
                                <span class="step-label" data-step="3">Funding</span>
                                <span class="step-label" data-step="4">Team</span>
                                <span class="step-label" data-step="5">Review</span>
                            </div>
                        </div>
                        
                        <!-- Step 1: Basic Information -->
                        <div class="step-panel" id="step-1">
                            <div class="mb-4">
                                <h4>Basic Information</h4>
                                <p class="text-muted">Let's start with the essentials about your campaign</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="campaign-title" class="form-label">Campaign Title</label>
                                <input type="text" class="form-control" id="campaign-title" name="title" value="<?php echo $isEdit ? htmlspecialchars($campaign['title']) : ''; ?>" required>
                                <div class="form-text">Choose a clear, attention-grabbing title (max 100 characters)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="campaign-short-description" class="form-label">Short Description</label>
                                <textarea class="form-control" id="campaign-short-description" name="short_description" rows="2" maxlength="255" required><?php echo $isEdit ? htmlspecialchars($campaign['short_description']) : ''; ?></textarea>
                                <div class="form-text">Briefly describe your campaign in 1-2 sentences (max 255 characters)</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="campaign-category" class="form-label">Category</label>
                                    <select class="form-select" id="campaign-category" name="category" required>
                                        <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Select a category</option>
                                        <?php foreach ($CATEGORIES as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($isEdit && $campaign['category'] === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="campaign-department" class="form-label">Department</label>
                                    <select class="form-select" id="campaign-department" name="department" required>
                                        <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Select a department</option>
                                        <?php foreach ($DEPARTMENTS as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($isEdit && $campaign['department'] === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="campaign-images" class="form-label">Campaign Images</label>
                                <input type="file" class="form-control" id="campaign-images" name="images[]" accept="image/*" multiple <?php echo !$isEdit ? 'required' : ''; ?>>
                                <div class="form-text">Upload up to 5 high-quality images (.jpg, .png, .jpeg). First image will be the featured image.</div>
                            </div>
                            
                            <div id="image-preview-container" class="row mt-3">
                                <?php if ($isEdit && !empty($campaign['featured_image'])): ?>
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="card">
                                            <img src="<?php echo htmlspecialchars($campaign['featured_image']); ?>" class="card-img-top" alt="Campaign image preview">
                                            <div class="card-body">
                                                <button type="button" class="btn btn-sm btn-danger w-100 remove-image">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary next-step">Next: Campaign Details</button>
                            </div>
                        </div>
                        
                        <!-- Step 2: Campaign Details -->
                        <div class="step-panel" id="step-2" style="display: none;">
                            <div class="mb-4">
                                <h4>Campaign Details</h4>
                                <p class="text-muted">Tell potential donors more about your campaign</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="campaign-description" class="form-label">Campaign Description</label>
                                <textarea class="form-control" id="campaign-description" name="description" rows="10" required><?php echo $isEdit ? htmlspecialchars($campaign['description']) : ''; ?></textarea>
                                <div class="form-text">Provide detailed information about your campaign, its purpose, and impact (max 3000 characters)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="campaign-location" class="form-label">Location (Optional)</label>
                                <input type="text" class="form-control" id="campaign-location" name="location" value="<?php echo $isEdit && isset($campaign['location']) ? htmlspecialchars($campaign['location']) : ''; ?>">
                                <div class="form-text">Specify the location on campus if applicable</div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary prev-step">Previous</button>
                                <button type="button" class="btn btn-primary next-step">Next: Funding Details</button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Funding Details -->
                        <div class="step-panel" id="step-3" style="display: none;">
                            <div class="mb-4">
                                <h4>Funding Details</h4>
                                <p class="text-muted">Set your funding goal and campaign duration</p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="campaign-goal" class="form-label">Funding Goal (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="text" class="form-control" id="campaign-goal" name="goal" value="<?php echo $isEdit ? number_format($campaign['goal'], 0, '.', ',') : ''; ?>" required pattern="[0-9,]+" placeholder="10,000">
                                    </div>
                                    <div class="form-text">The total amount you need to raise (minimum ₹5,000)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="campaign-end-date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="campaign-end-date" name="end_date" value="<?php echo $isEdit ? $campaign['end_date'] : ''; ?>" required>
                                    <div class="form-text">Your campaign can run between 7 and 90 days</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Budget Breakdown</label>
                                <div id="budget-breakdown-container">
                                    <?php 
                                    if ($isEdit && !empty($campaign['budget_breakdown'])) {
                                        $budget = json_decode($campaign['budget_breakdown'], true);
                                        if (is_array($budget)) {
                                            foreach ($budget as $index => $item) {
                                                echo <<<HTML
                                                <div class="budget-item card mb-3">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-5 mb-3">
                                                                <label class="form-label">Item Description</label>
                                                                <input type="text" class="form-control budget-item-description" name="budget_items[{$index}][description]" value="{$item['description']}" required>
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Amount (₹)</label>
                                                                <input type="text" class="form-control budget-item-amount" name="budget_items[{$index}][amount]" value="{$item['amount']}" required pattern="[0-9,]+" placeholder="0">
                                                            </div>
                                                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                                                <button type="button" class="btn btn-danger w-100 remove-budget-item">Remove</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                HTML;
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                
                                <button type="button" id="add-budget-item" class="btn btn-outline-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Add Budget Item
                                </button>
                                
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total Budget:</span>
                                        <span class="fw-bold">₹<span id="budget-total">0</span></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary prev-step">Previous</button>
                                <button type="button" class="btn btn-primary next-step">Next: Team Information</button>
                            </div>
                        </div>
                        
                        <!-- Step 4: Team Information -->
                        <div class="step-panel" id="step-4" style="display: none;">
                            <div class="mb-4">
                                <h4>Team Information</h4>
                                <p class="text-muted">Tell us about the team behind this campaign</p>
                            </div>
                            
                            <div id="team-members-container">
                                <?php 
                                if ($isEdit && !empty($campaign['team_members'])) {
                                    $team = json_decode($campaign['team_members'], true);
                                    if (is_array($team)) {
                                        foreach ($team as $index => $member) {
                                            echo <<<HTML
                                            <div class="team-member card mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" class="form-control" name="team_members[{$index}][name]" value="{$member['name']}" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Role</label>
                                                            <input type="text" class="form-control" name="team_members[{$index}][role]" value="{$member['role']}" required>
                                                        </div>
                                                        <div class="col-md-3 mb-3 d-flex align-items-end">
                                                            <button type="button" class="btn btn-danger w-100 remove-team-member">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            HTML;
                                        }
                                    }
                                } else {
                                    // Add current user as a team member by default
                                    $userName = $_SESSION['user_name'];
                                    echo <<<HTML
                                    <div class="team-member card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" name="team_members[0][name]" value="{$userName}" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Role</label>
                                                    <input type="text" class="form-control" name="team_members[0][role]" value="Campaign Creator" required>
                                                </div>
                                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger w-100 remove-team-member" disabled>Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    HTML;
                                }
                                ?>
                            </div>
                            
                            <button type="button" id="add-team-member" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle me-1"></i> Add Team Member
                            </button>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary prev-step">Previous</button>
                                <button type="button" class="btn btn-primary next-step">Next: Review</button>
                            </div>
                        </div>
                        
                        <!-- Step 5: Review -->
                        <div class="step-panel" id="step-5" style="display: none;">
                            <div class="mb-4">
                                <h4>Review Your Campaign</h4>
                                <p class="text-muted">Please review your campaign details before submitting</p>
                            </div>
                            
                            <div class="review-section p-3 bg-light rounded mb-3">
                                <h5>Basic Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Title:</strong> <span id="review-title"></span></p>
                                        <p><strong>Category:</strong> <span id="review-category"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Department:</strong> <span id="review-department"></span></p>
                                        <p><strong>Images:</strong> <span id="review-images"></span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="review-section p-3 bg-light rounded mb-3">
                                <h5>Funding Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Goal:</strong> <span id="review-goal"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>End Date:</strong> <span id="review-end-date"></span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="review-section p-3 bg-light rounded mb-3">
                                <h5>Campaign Status</h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status-draft" value="draft" <?php echo (!$isEdit || ($isEdit && $campaign['status'] === CAMPAIGN_DRAFT)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status-draft">
                                        Save as Draft
                                    </label>
                                    <div class="form-text">You can edit and submit later</div>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="status" id="status-submit" value="pending" <?php echo ($isEdit && $campaign['status'] === CAMPAIGN_PENDING) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status-submit">
                                        Submit for Review
                                    </label>
                                    <div class="form-text">Your campaign will be reviewed by administrators before going live</div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms-checkbox" required>
                                <label class="form-check-label" for="terms-checkbox">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and confirm that all information provided is accurate
                                </label>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary prev-step">Previous</button>
                                <button type="submit" class="btn btn-success">
                                    <?php echo $isEdit ? 'Update Campaign' : 'Create Campaign'; ?>
                                </button>
                            </div>
                        </div>
                    </form>
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
                <h5 class="modal-title" id="termsModalLabel">Campaign Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>NIT Delhi Crowdfunding Campaign Agreement</h6>
                <p>By creating a campaign, you agree to the following terms:</p>
                
                <ol>
                    <li>All information provided must be accurate and truthful.</li>
                    <li>You must be affiliated with NIT Delhi as a student, faculty, staff, or alumni.</li>
                    <li>Campaigns must comply with all institute policies and applicable laws.</li>
                    <li>Campaigns must be for legitimate educational, research, cultural, or community purposes.</li>
                    <li>All funds raised must be used for the stated purpose of the campaign.</li>
                    <li>You agree to provide regular updates on the campaign's progress to donors.</li>
                    <li>You agree to fulfill any promised rewards or incentives to donors.</li>
                    <li>Campaigns that violate these terms may be removed, and you may be prohibited from future fundraising.</li>
                    <li>NIT Delhi Crowdfunding reserves the right to reject or remove campaigns at its discretion.</li>
                    <li>A platform fee of 5% will be deducted from the total amount raised to cover operational costs.</li>
                </ol>
                
                <h6>Campaign Content Guidelines</h6>
                <p>Your campaign must not contain:</p>
                
                <ul>
                    <li>Offensive, discriminatory, or inappropriate content</li>
                    <li>False or misleading information</li>
                    <li>Content that infringes on intellectual property rights</li>
                    <li>Content that promotes illegal activities</li>
                    <li>Personal fundraising for non-academic purposes</li>
                </ul>
                
                <p>By submitting your campaign, you acknowledge that you have read, understood, and agree to these terms.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form step navigation
    const stepButtons = document.querySelectorAll('.next-step, .prev-step');
    const progressBar = document.querySelector('.progress-bar');
    
    stepButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = parseInt(document.querySelector('.step-panel:not([style*="display: none"])').id.split('-')[1]);
            let nextStep;
            
            if (this.classList.contains('next-step')) {
                // Validate current step before proceeding
                if (!validateStep(currentStep)) {
                    return;
                }
                nextStep = currentStep + 1;
            } else {
                nextStep = currentStep - 1;
            }
            
            // Hide all step panels
            document.querySelectorAll('.step-panel').forEach(panel => {
                panel.style.display = 'none';
            });
            
            // Show next step panel
            document.getElementById(`step-${nextStep}`).style.display = 'block';
            
            // Update progress bar
            const progress = (nextStep - 1) * 20 + 20;
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
            
            // Update step labels
            document.querySelectorAll('.step-label').forEach(label => {
                const step = parseInt(label.getAttribute('data-step'));
                if (step <= nextStep) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });
            
            // Update review information if going to review step
            if (nextStep === 5) {
                updateReviewInfo();
            }
            
            // Scroll to top
            window.scrollTo(0, 0);
        });
    });
    
    // Add Team Member button
    const addTeamMemberButton = document.getElementById('add-team-member');
    if (addTeamMemberButton) {
        addTeamMemberButton.addEventListener('click', function() {
            const teamContainer = document.getElementById('team-members-container');
            const memberCount = teamContainer.querySelectorAll('.team-member').length;
            
            if (memberCount >= 10) {
                showToast('You can add maximum 10 team members', 'warning');
                return;
            }
            
            const newMember = document.createElement('div');
            newMember.className = 'team-member card mb-3';
            newMember.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="team_members[${memberCount}][name]" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" name="team_members[${memberCount}][role]" required>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100 remove-team-member">Remove</button>
                        </div>
                    </div>
                </div>
            `;
            
            teamContainer.appendChild(newMember);
            
            // Set up remove button
            newMember.querySelector('.remove-team-member').addEventListener('click', function() {
                newMember.remove();
            });
        });
    }
    
    // Set up remove buttons for existing team members
    document.querySelectorAll('.remove-team-member').forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', function() {
                this.closest('.team-member').remove();
            });
        }
    });
    
    // Validate each step
    function validateStep(step) {
        const stepPanel = document.getElementById(`step-${step}`);
        const requiredFields = stepPanel.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            showToast('Please fill in all required fields', 'warning');
        }
        
        return isValid;
    }
    
    // Update review information
    function updateReviewInfo() {
        const titleInput = document.getElementById('campaign-title');
        const categorySelect = document.getElementById('campaign-category');
        const departmentSelect = document.getElementById('campaign-department');
        const imagesInput = document.getElementById('campaign-images');
        const goalInput = document.getElementById('campaign-goal');
        const endDateInput = document.getElementById('campaign-end-date');
        
        document.getElementById('review-title').textContent = titleInput.value;
        document.getElementById('review-category').textContent = categorySelect.options[categorySelect.selectedIndex].text;
        document.getElementById('review-department').textContent = departmentSelect.options[departmentSelect.selectedIndex].text;
        
        // Format images info
        const imageCount = imagesInput.files.length;
        document.getElementById('review-images').textContent = imageCount > 0 ? `${imageCount} images selected` : 'None selected';
        
        document.getElementById('review-goal').textContent = `₹${goalInput.value}`;
        
        // Format end date
        if (endDateInput.value) {
            const endDate = new Date(endDateInput.value);
            document.getElementById('review-end-date').textContent = endDate.toLocaleDateString('en-IN', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } else {
            document.getElementById('review-end-date').textContent = 'Not set';
        }
    }
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>
