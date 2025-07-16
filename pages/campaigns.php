<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Campaigns Listing Page
 */

// Start session
session_start();

// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/database.php';

// Add helper functions if they don't exist
if (!function_exists('getCategoryName')) {
    function getCategoryName($category_key) {
        global $CATEGORIES;
        return isset($CATEGORIES[$category_key]) ? $CATEGORIES[$category_key] : 'Unknown';
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'INR') {
        if ($currency === 'INR') {
            return '₹' . number_format($amount, 0, '.', ',');
        }
        return '$' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('calculateFundingPercentage')) {
    function calculateFundingPercentage($raised, $goal) {
        if ($goal <= 0) return 0;
        $percentage = ($raised / $goal) * 100;
        return min($percentage, 100); // Cap at 100%
    }
}

if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
}

if (!function_exists('daysRemaining')) {
    function daysRemaining($endDate) {
        $today = new DateTime();
        $end = new DateTime($endDate);
        $interval = $today->diff($end);
        
        if ($interval->invert) {
            return 0; // Date has passed
        }
        
        return $interval->days;
    }
}

if (!function_exists('generatePagination')) {
    function generatePagination($currentPage, $totalPages, $baseUrl = '') {
        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
        $html .= '<li class="page-item ' . $prevDisabled . '">
                  <a class="page-link" href="' . ($currentPage > 1 ? $baseUrl . '&page=' . ($currentPage - 1) : '#') . '" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                  </a>
                </li>';
        
        // Page numbers
        $startPage = max(1, min($currentPage - 2, $totalPages - 4));
        $endPage = min($totalPages, max(5, $currentPage + 2));
        
        if ($startPage > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }
        
        // Next button
        $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
        $html .= '<li class="page-item ' . $nextDisabled . '">
                  <a class="page-link" href="' . ($currentPage < $totalPages ? $baseUrl . '&page=' . ($currentPage + 1) : '#') . '" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                  </a>
                </li>';
        
        $html .= '</ul></nav>';
        
        return $html;
    }
}

// Set page title
$page_title = 'Explore Campaigns';

// Sample categories and departments if not defined in config
if (!isset($CATEGORIES)) {
    $CATEGORIES = [
        'research' => 'Research Projects',
        'infrastructure' => 'Infrastructure',
        'events' => 'Campus Events',
        'student_initiatives' => 'Student Initiatives',
        'technology' => 'Technology',
        'other' => 'Other'
    ];
}

if (!isset($DEPARTMENTS)) {
    $DEPARTMENTS = [
        'CSE' => 'Computer Science & Engineering',
        'EEE' => 'Electrical & Electronics Engineering',
        'ECE' => 'Electronics & Communication Engineering',
        'ME' => 'Mechanical Engineering',
        'CE' => 'Civil Engineering',
        'BT' => 'Biotechnology',
        'MBA' => 'Management Studies',
        'Student Affairs' => 'Student Affairs',
        'other' => 'Other'
    ];
}

// Database connection
try {
    $db = new Database();
    $conn = $db->connect();
} catch(Exception $e) {
    die("Database connection failed. Please try again later.");
}

// Get filter parameters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : '';
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = DEFAULT_PAGINATION_LIMIT;
$offset = ($page - 1) * $limit;

// Prepare base query
$query = "
    SELECT c.*, u.name as creator_name, u.is_verified as creator_verified
    FROM campaigns c
    JOIN users u ON c.user_id = u.id
    WHERE c.status = :status
";
$countQuery = "SELECT COUNT(*) FROM campaigns WHERE status = :status";
// Define CAMPAIGN_APPROVED constant if not already defined
if (!defined('CAMPAIGN_APPROVED')) {
    define('CAMPAIGN_APPROVED', 'approved');
}

// Define DEFAULT_PAGINATION_LIMIT if not already defined
if (!defined('DEFAULT_PAGINATION_LIMIT')) {
    define('DEFAULT_PAGINATION_LIMIT', 9);
}

$params = [':status' => CAMPAIGN_APPROVED];

// Add filters to query
if (!empty($category)) {
    $query .= " AND c.category = :category";
    $countQuery .= " AND category = :category";
    $params[':category'] = $category;
}

if (!empty($department)) {
    $query .= " AND c.department = :department";
    $countQuery .= " AND department = :department";
    $params[':department'] = $department;
}

if (!empty($search)) {
    $query .= " AND (c.title LIKE :search OR c.short_description LIKE :search)";
    $countQuery .= " AND (title LIKE :search OR short_description LIKE :search)";
    $params[':search'] = "%{$search}%";
}

// Add sorting
switch ($sort) {
    case 'goal':
        $query .= " ORDER BY c.amount_raised/c.goal DESC";
        break;
    case 'ending':
        $query .= " ORDER BY c.end_date ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.created_at DESC";
        break;
}

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Get total count for pagination
$countStmt = $conn->prepare($countQuery);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalCampaigns = $countStmt->fetchColumn();
$totalPages = ceil($totalCampaigns / $limit);

// Try to get campaigns from database
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If database error, use sample data for demonstration
    error_log("Database error: " . $e->getMessage());
    $campaigns = [
        [
            'id' => 1,
            'title' => 'Robotics Innovation Lab',
            'description' => 'Help us build a state-of-the-art robotics lab for students to explore cutting-edge technology and innovation.',
            'featured_image' => '../assets/images/campaigns/robotics_lab.jpg',
            'category' => 'research',
            'goal_amount' => 500000,
            'current_amount' => 325000,
            'start_date' => '2023-01-15',
            'end_date' => '2023-12-31',
            'creator_name' => 'Prof. Rajesh Kumar',
            'creator_verified' => 1,
            'is_featured' => 1,
            'department' => 'CSE'
        ],
        [
            'id' => 2,
            'title' => 'Solar Powered Campus Initiative',
            'description' => 'Support our initiative to install solar panels across campus buildings to reduce our carbon footprint.',
            'featured_image' => '../assets/images/campaigns/solar_campus.jpg',
            'category' => 'infrastructure',
            'goal_amount' => 750000,
            'current_amount' => 450000,
            'start_date' => '2023-02-10',
            'end_date' => '2023-11-30',
            'creator_name' => 'Dr. Sunita Sharma',
            'creator_verified' => 1,
            'is_featured' => 0,
            'department' => 'EEE'
        ],
        [
            'id' => 3,
            'title' => 'Annual Technical Festival',
            'description' => 'Help us organize the biggest technical festival in Delhi NCR with workshops, competitions, and guest speakers.',
            'featured_image' => '../assets/images/campaigns/tech_fest.jpg',
            'category' => 'events',
            'goal_amount' => 300000,
            'current_amount' => 150000,
            'start_date' => '2023-03-01',
            'end_date' => '2023-09-15',
            'creator_name' => 'Student Technical Council',
            'creator_verified' => 1,
            'is_featured' => 1,
            'department' => 'Student Affairs'
        ],
        [
            'id' => 4,
            'title' => 'Smart Classroom Initiative',
            'description' => 'Help us transform traditional classrooms into smart learning spaces with interactive technologies.',
            'featured_image' => '../assets/images/campaigns/smart_classroom.jpg',
            'category' => 'infrastructure',
            'goal_amount' => 450000,
            'current_amount' => 175000,
            'start_date' => '2023-03-15',
            'end_date' => '2023-10-30',
            'creator_name' => 'Dr. Amit Patel',
            'creator_verified' => 1,
            'is_featured' => 0,
            'department' => 'CSE'
        ],
        [
            'id' => 5,
            'title' => 'AI Research Project',
            'description' => 'Support groundbreaking research in artificial intelligence and machine learning applications.',
            'featured_image' => '../assets/images/campaigns/ai_research.jpg',
            'category' => 'research',
            'goal_amount' => 600000,
            'current_amount' => 400000,
            'start_date' => '2023-01-20',
            'end_date' => '2023-12-20',
            'creator_name' => 'Prof. Vikram Singh',
            'creator_verified' => 1,
            'is_featured' => 1,
            'department' => 'CSE'
        ],
        [
            'id' => 6,
            'title' => 'College Cultural Fest',
            'description' => 'Help us organize the annual cultural festival with performances, competitions, and celebrity guests.',
            'featured_image' => '../assets/images/campaigns/cultural_fest.jpg',
            'category' => 'events',
            'goal_amount' => 350000,
            'current_amount' => 200000,
            'start_date' => '2023-04-01',
            'end_date' => '2023-10-15',
            'creator_name' => 'Cultural Committee',
            'creator_verified' => 1,
            'is_featured' => 0,
            'department' => 'Student Affairs'
        ]
    ];
    
    // Set total for pagination
    $totalCampaigns = count($campaigns);
    $totalPages = 1;
}

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-3">Explore Campaigns</h1>
            <p class="text-muted">Discover and support innovative projects at NIT Delhi</p>
        </div>
        <div class="col-md-4 d-flex align-items-center justify-content-md-end">
            <a href="create_campaign.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Start a Campaign
            </a>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="campaign-filter-form" action="campaigns.php" method="GET">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search campaigns..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($CATEGORIES as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($category === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department">
                            <option value="">All Departments</option>
                            <?php foreach ($DEPARTMENTS as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($department === $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="goal" <?php echo ($sort === 'goal') ? 'selected' : ''; ?>>Progress</option>
                            <option value="ending" <?php echo ($sort === 'ending') ? 'selected' : ''; ?>>Ending Soon</option>
                        </select>
                    </div>
                    
                    <div class="col-12 text-end">
                        <button type="button" id="clear-filters" class="btn btn-outline-secondary">Clear Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($campaigns)): ?>
        <!-- No Campaigns Found -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3>No Campaigns Found</h3>
                <p class="text-muted">We couldn't find any campaigns matching your filter criteria.</p>
                <a href="campaigns.php" class="btn btn-primary mt-3">View All Campaigns</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Campaigns Grid -->
        <div class="row">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card campaign-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo !empty($campaign['featured_image']) ? htmlspecialchars($campaign['featured_image']) : 'https://images.unsplash.com/photo-1591901206069-ed60c4429a2e'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                            <span class="badge bg-primary category-badge"><?php echo getCategoryName($campaign['category']); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                                <?php if ($campaign['is_featured']): ?>
                                    <span class="badge bg-warning"><i class="fas fa-star me-1"></i> Featured</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-2">
                                By <?php echo htmlspecialchars($campaign['creator_name']); ?>
                                <?php if ($campaign['creator_verified']): ?>
                                    <i class="fas fa-check-circle text-primary ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified User"></i>
                                <?php endif; ?>
                            </p>
                            <p class="card-text"><?php echo htmlspecialchars(truncateText($campaign['description'] ?? ($campaign['short_description'] ?? ''), 100)); ?></p>
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo calculateFundingPercentage($campaign['current_amount'] ?? $campaign['amount_raised'], $campaign['goal_amount'] ?? $campaign['goal']); ?>%" aria-valuenow="<?php echo calculateFundingPercentage($campaign['current_amount'] ?? $campaign['amount_raised'], $campaign['goal_amount'] ?? $campaign['goal']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold"><?php echo formatCurrency($campaign['current_amount'] ?? $campaign['amount_raised']); ?> raised</span>
                                <span class="text-muted"><?php echo round(calculateFundingPercentage($campaign['current_amount'] ?? $campaign['amount_raised'], $campaign['goal_amount'] ?? $campaign['goal'])); ?>% of <?php echo formatCurrency($campaign['goal_amount'] ?? $campaign['goal']); ?></span>
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
                                <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-4">
                <?php 
                $queryParams = $_GET;
                unset($queryParams['page']);
                $queryString = http_build_query($queryParams);
                $baseUrl = 'campaigns.php?' . $queryString;
                
                echo generatePagination($page, $totalPages, $baseUrl);
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Include testimonials section
include_once '../includes/testimonials.php';

// Include footer
include_once '../includes/footer.php';
?>
