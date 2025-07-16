<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Donation Page
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
$campaign_id = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;
$preset_amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 0;

if ($campaign_id <= 0) {
    // Invalid campaign ID, redirect to campaigns page
    header('Location: campaigns.php');
    exit;
}

// Fetch campaign details
$stmt = $conn->prepare("
    SELECT c.*, u.name as creator_name, u.email as creator_email
    FROM campaigns c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = :id AND c.status = :status
");
$approved_status = CAMPAIGN_APPROVED;
$stmt->bindParam(':id', $campaign_id);
$stmt->bindParam(':status', $approved_status);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    // Campaign not found or not approved, redirect to campaigns page
    header('Location: campaigns.php');
    exit;
}

$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if campaign has ended
if (daysRemaining($campaign['end_date']) <= 0) {
    // Campaign has ended, redirect back to campaign details
    setFlashMessage('This campaign has ended and is no longer accepting donations.', 'warning');
    header('Location: campaign_details.php?id=' . $campaign_id);
    exit;
}

// Set page title
$page_title = 'Donate to ' . $campaign['title'];

// Set pre-filled user information if logged in
$user_info = [
    'name' => '',
    'email' => ''
];

if (isLoggedIn()) {
    $user = getUserById(getCurrentUserId());
    $user_info['name'] = $user['name'];
    $user_info['email'] = $user['email'];
}

// Include page specific JavaScript
$page_specific_js = ['../assets/js/donation.js'];

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h2 class="mb-0">Support This Campaign</h2>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="donation-form-container">
                                <form id="donation-form" action="../api/donations.php" method="POST" novalidate>
                                    <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                                    
                                    <div class="mb-4">
                                        <h4>Donation Amount</h4>
                                        <div class="donation-presets d-flex flex-wrap mb-3">
                                            <button type="button" class="donation-preset btn btn-outline-primary me-2 mb-2 <?php echo ($preset_amount === 500) ? 'active' : ''; ?>" data-amount="500">₹500</button>
                                            <button type="button" class="donation-preset btn btn-outline-primary me-2 mb-2 <?php echo ($preset_amount === 1000) ? 'active' : ''; ?>" data-amount="1000">₹1,000</button>
                                            <button type="button" class="donation-preset btn btn-outline-primary me-2 mb-2 <?php echo ($preset_amount === 2000) ? 'active' : ''; ?>" data-amount="2000">₹2,000</button>
                                            <button type="button" class="donation-preset btn btn-outline-primary me-2 mb-2 <?php echo ($preset_amount === 5000) ? 'active' : ''; ?>" data-amount="5000">₹5,000</button>
                                            <button type="button" class="donation-preset btn btn-outline-primary mb-2 <?php echo ($preset_amount === 10000) ? 'active' : ''; ?>" data-amount="10000">₹10,000</button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="donation-amount" class="form-label">Custom Amount (₹)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="text" class="form-control" id="donation-amount" name="amount" value="<?php echo $preset_amount > 0 ? number_format($preset_amount, 0, '.', ',') : ''; ?>" required pattern="[0-9,]+" placeholder="Enter amount">
                                            </div>
                                            <div class="form-text">Minimum donation: ₹100</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h4>Your Information</h4>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <label for="donation-name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="donation-name" name="name" value="<?php echo htmlspecialchars($user_info['name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="donation-email" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="donation-email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="donation-anonymous" name="anonymous">
                                            <label class="form-check-label" for="donation-anonymous">
                                                Make my donation anonymous
                                            </label>
                                            <div class="form-text">Your name will not be displayed publicly, but we'll still collect your information for receipt purposes.</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="donation-tribute" name="is_tribute">
                                            <label class="form-check-label" for="donation-tribute">
                                                Donate in honor or memory of someone
                                            </label>
                                        </div>
                                        
                                        <div id="tribute-section" class="card p-3 mb-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="tribute-name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="tribute-name" name="tribute_name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tribute-type" class="form-label">Type</label>
                                                    <select class="form-select" id="tribute-type" name="tribute_type">
                                                        <option value="honor">In Honor Of</option>
                                                        <option value="memory">In Memory Of</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label for="tribute-message" class="form-label">Message (Optional)</label>
                                                    <textarea class="form-control" id="tribute-message" name="tribute_message" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="donation-recurring" name="is_recurring">
                                            <label class="form-check-label" for="donation-recurring">
                                                Make this a recurring donation
                                            </label>
                                        </div>
                                        
                                        <div id="recurring-options" class="card p-3 mb-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="recurring-frequency" class="form-label">Frequency</label>
                                                    <select class="form-select" id="recurring-frequency" name="recurring_frequency">
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h4>Payment Method</h4>
                                        
                                        <div class="payment-methods mb-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment-card" value="card" checked>
                                                <label class="form-check-label" for="payment-card">
                                                    <i class="far fa-credit-card me-2"></i> Credit/Debit Card
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment-netbanking" value="netbanking">
                                                <label class="form-check-label" for="payment-netbanking">
                                                    <i class="fas fa-university me-2"></i> Net Banking
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="payment-upi" value="upi">
                                                <label class="form-check-label" for="payment-upi">
                                                    <i class="fas fa-mobile-alt me-2"></i> UPI
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- Credit/Debit Card Details -->
                                        <div class="payment-details" id="card-details">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="card-number" class="form-label">Card Number</label>
                                                    <input type="text" class="form-control" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="card-expiry" class="form-label">Expiry Date</label>
                                                    <input type="text" class="form-control" id="card-expiry" name="card_expiry" placeholder="MM/YY" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="card-cvv" class="form-label">CVV</label>
                                                    <input type="text" class="form-control" id="card-cvv" name="card_cvv" placeholder="123" required>
                                                </div>
                                                <div class="col-12">
                                                    <label for="card-name" class="form-label">Name on Card</label>
                                                    <input type="text" class="form-control" id="card-name" name="card_name" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Net Banking Details -->
                                        <div class="payment-details" id="netbanking-details" style="display: none;">
                                            <div class="mb-3">
                                                <label for="bank-name" class="form-label">Select Bank</label>
                                                <select class="form-select" id="bank-name" name="bank_name">
                                                    <option value="" selected disabled>Select your bank</option>
                                                    <option value="sbi">State Bank of India</option>
                                                    <option value="hdfc">HDFC Bank</option>
                                                    <option value="icici">ICICI Bank</option>
                                                    <option value="axis">Axis Bank</option>
                                                    <option value="pnb">Punjab National Bank</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- UPI Details -->
                                        <div class="payment-details" id="upi-details" style="display: none;">
                                            <div class="mb-3">
                                                <label for="upi-id" class="form-label">UPI ID</label>
                                                <input type="text" class="form-control" id="upi-id" name="upi_id" placeholder="yourname@upi">
                                                <div class="form-text">Enter your UPI ID (e.g., name@okaxis, name@ybl)</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="donation-terms" name="agree_terms" required>
                                        <label class="form-check-label" for="donation-terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#donationTermsModal">Terms and Conditions</a> for donations
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg">Complete Donation</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div class="campaign-summary card sticky-top" style="top: 100px;">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Donation Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6>Campaign</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($campaign['title']); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Your Donation</h6>
                                        <p class="mb-0 fw-bold" id="donation-summary-amount">₹<?php echo $preset_amount > 0 ? number_format($preset_amount, 0, '.', ',') : '0'; ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Campaign Progress</h6>
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['amount_raised'], $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="small"><?php echo formatCurrency($campaign['amount_raised']); ?> raised</span>
                                            <span class="small"><?php echo formatCurrency($campaign['goal']); ?> goal</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Campaign Creator</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($campaign['creator_name']); ?></p>
                                    </div>
                                    
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i> Your donation will directly support this campaign and help it reach its funding goal.
                                    </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="campaign_details.php?id=<?php echo $campaign_id; ?>" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Campaign
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Donation Terms Modal -->
<div class="modal fade" id="donationTermsModal" tabindex="-1" aria-labelledby="donationTermsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="donationTermsModalLabel">Donation Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>NIT Delhi Crowdfunding Donation Agreement</h6>
                <p>By making a donation, you agree to the following terms:</p>
                
                <ol>
                    <li>All donations are voluntary contributions to support the campaign.</li>
                    <li>The payment information you provide is accurate and complete.</li>
                    <li>You authorize the charging of your payment method for the specified donation amount.</li>
                    <li>Donations are generally non-refundable unless a campaign is canceled by the platform.</li>
                    <li>A platform fee of 5% will be deducted from your donation to cover operational costs.</li>
                    <li>For recurring donations, you authorize regular charges until you cancel the subscription.</li>
                    <li>You understand that while campaign creators are expected to use funds as described, the platform cannot guarantee how funds will be used.</li>
                    <li>NIT Delhi Crowdfunding is not liable for any misrepresentation by campaign creators.</li>
                    <li>Your contact information may be shared with the campaign creator for acknowledgment purposes.</li>
                    <li>Your name and donation amount will be publicly displayed unless you choose to remain anonymous.</li>
                </ol>
                
                <h6>Payment Processing</h6>
                <p>Payment processing is handled securely through our payment partners. By proceeding with a donation, you also agree to the terms and conditions of our payment processor.</p>
                
                <h6>Tax Receipts</h6>
                <p>You will receive a receipt for your donation which may be eligible for tax benefits under applicable laws. NIT Delhi Crowdfunding does not provide tax advice, and you should consult a tax professional regarding the tax-deductibility of your donation.</p>
                
                <p>By clicking "Complete Donation," you acknowledge that you have read, understood, and agree to these terms.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script>
// For demonstration purposes only - do not use in production
document.addEventListener('DOMContentLoaded', function() {
    // Credit card field formatting
    const cardNumberInput = document.getElementById('card-number');
    const cardExpiryInput = document.getElementById('card-expiry');
    const cardCvvInput = document.getElementById('card-cvv');
    
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            
            // Add spaces after every 4 digits
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            
            // Limit to 19 characters (16 digits + 3 spaces)
            this.value = value.substring(0, 19);
        });
    }
    
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function() {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            
            // Format as MM/YY
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value.substring(0, 5);
        });
    }
    
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function() {
            // Remove non-digits and limit to 3-4 digits
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
        });
    }
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>
