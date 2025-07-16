<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Home Page
 */

// Set page title
$page_title = 'Home';

// Include header
include_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-4">Support Innovation at NIT Delhi</h1>
                <p class="mb-4">Our crowdfunding platform empowers students, faculty, and alumni to bring their projects to life through community support.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="pages/campaigns.php" class="btn btn-light btn-lg">Explore Campaigns</a>
                    <a href="pages/create_campaign.php" class="btn btn-outline-light btn-lg">Start a Campaign</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f" alt="Students collaborating on a project" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Counter Section -->
<section class="stats-counter py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 col-sm-6 mb-4 mb-md-0">
                <div class="counter-item">
                    <h2 class="counter-number" data-target="500000">0</h2>
                    <p class="counter-label mb-0">Total Raised (₹)</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4 mb-md-0">
                <div class="counter-item">
                    <h2 class="counter-number" data-target="50">0</h2>
                    <p class="counter-label mb-0">Active Campaigns</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4 mb-md-0">
                <div class="counter-item">
                    <h2 class="counter-number" data-target="1000">0</h2>
                    <p class="counter-label mb-0">Total Donors</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Campaigns Section -->
<section class="featured-campaigns py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Featured Campaigns</h2>
                <p class="text-muted">Discover the most innovative projects at NIT Delhi</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="pages/campaigns.php" class="btn btn-outline-primary">View All Campaigns</a>
            </div>
        </div>
        
        <div class="row">
            <!-- Campaign Card 1 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card campaign-card h-100">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1519452575417-564c1401ecc0" class="card-img-top" alt="Campaign Image">
                        <span class="badge bg-primary category-badge">Campus Events</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Annual Tech Festival Expansion</h5>
                        <p class="card-text">Help us expand our annual technology festival with more workshops, competitions, and industry speakers.</p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">₹75,000 raised</span>
                            <span class="text-muted">75% of ₹100,000</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock me-1"></i> 15 days left</span>
                            <a href="pages/campaign_details.php?id=1" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Campaign Card 2 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card campaign-card h-100">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c" class="card-img-top" alt="Campaign Image">
                        <span class="badge bg-success category-badge">Research Projects</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Smart Campus IoT Initiative</h5>
                        <p class="card-text">Support our research team in developing IoT solutions for energy efficiency and smart management across campus.</p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">₹80,000 raised</span>
                            <span class="text-muted">40% of ₹200,000</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock me-1"></i> 30 days left</span>
                            <a href="pages/campaign_details.php?id=2" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Campaign Card 3 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card campaign-card h-100">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1603827457577-609e6f42a45e" class="card-img-top" alt="Campaign Image">
                        <span class="badge bg-info category-badge">Scholarships</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Merit Scholarship Fund</h5>
                        <p class="card-text">Help us create more scholarship opportunities for deserving students from economically challenged backgrounds.</p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">₹150,000 raised</span>
                            <span class="text-muted">60% of ₹250,000</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock me-1"></i> 45 days left</span>
                            <a href="pages/campaign_details.php?id=3" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Exploration Section -->
<section class="category-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2>Explore by Category</h2>
                <p class="text-muted">Discover projects by area of interest</p>
            </div>
        </div>
        
        <div class="row category-grid">
            <!-- Category 1 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="pages/campaigns.php?category=research" class="text-decoration-none">
                    <div class="category-item">
                        <img src="https://images.unsplash.com/photo-1543269865-cbf427effbad" class="category-image" alt="Research Projects">
                        <div class="category-overlay">
                            <h5 class="category-title">Research Projects</h5>
                            <p class="category-description">Innovative academic research</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Category 2 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="pages/campaigns.php?category=events" class="text-decoration-none">
                    <div class="category-item">
                        <img src="https://images.unsplash.com/photo-1613687969216-40c7b718c025" class="category-image" alt="Campus Events">
                        <div class="category-overlay">
                            <h5 class="category-title">Campus Events</h5>
                            <p class="category-description">Festivals, competitions & more</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Category 3 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="pages/campaigns.php?category=infrastructure" class="text-decoration-none">
                    <div class="category-item">
                        <img src="https://images.unsplash.com/photo-1705937721505-942fff402d77" class="category-image" alt="Infrastructure">
                        <div class="category-overlay">
                            <h5 class="category-title">Infrastructure</h5>
                            <p class="category-description">Campus facility improvements</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Category 4 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="pages/campaigns.php?category=student_initiatives" class="text-decoration-none">
                    <div class="category-item">
                        <img src="https://images.unsplash.com/photo-1531545514256-b1400bc00f31" class="category-image" alt="Student Initiatives">
                        <div class="category-overlay">
                            <h5 class="category-title">Student Initiatives</h5>
                            <p class="category-description">Projects led by students</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Success Stories Section -->
<section class="success-stories py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2>Success Stories</h2>
                <p class="text-muted">See how our community has made projects come to life</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="https://images.unsplash.com/photo-1679867733409-fd58a2ef4093" class="img-fluid rounded-start h-100 object-fit-cover" alt="Success Story">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body">
                                <h5 class="card-title">Robotics Lab Upgrade</h5>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-success me-2">Completed</span>
                                    <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> 2022</span>
                                </div>
                                <p class="card-text">The Robotics Team successfully raised ₹350,000 to upgrade their lab with cutting-edge equipment, which led to winning the National Robotics Competition.</p>
                                <div class="mt-3">
                                    <span class="fw-bold">₹350,000 raised</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="https://images.unsplash.com/photo-1593113630400-ea4288922497" class="img-fluid rounded-start h-100 object-fit-cover" alt="Success Story">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body">
                                <h5 class="card-title">Solar Powered Campus Initiative</h5>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-success me-2">Completed</span>
                                    <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> 2023</span>
                                </div>
                                <p class="card-text">A team of environmental engineering students raised funds to install solar panels that now provide 30% of the campus electricity needs.</p>
                                <div class="mt-3">
                                    <span class="fw-bold">₹500,000 raised</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activity Feed -->
<section class="recent-activity py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Recent Activity</h2>
                <p class="text-muted">See what's happening in our community</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="activity-feed">
                            <!-- Activity Item 1 -->
                            <div class="activity-item d-flex pb-3 mb-3 border-bottom">
                                <div class="activity-icon me-3 bg-primary text-white rounded-circle p-2">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1"><strong>Rahul Sharma</strong> donated <strong>₹5,000</strong> to <a href="#">Smart Campus IoT Initiative</a></p>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                            
                            <!-- Activity Item 2 -->
                            <div class="activity-item d-flex pb-3 mb-3 border-bottom">
                                <div class="activity-icon me-3 bg-success text-white rounded-circle p-2">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1"><strong>Dr. Priya Patel</strong> launched a new campaign <a href="#">AI Research Lab Initiative</a></p>
                                    <small class="text-muted">3 hours ago</small>
                                </div>
                            </div>
                            
                            <!-- Activity Item 3 -->
                            <div class="activity-item d-flex pb-3 mb-3 border-bottom">
                                <div class="activity-icon me-3 bg-warning text-white rounded-circle p-2">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1"><strong>Annual Tech Festival</strong> campaign reached <strong>75%</strong> of its goal</p>
                                    <small class="text-muted">Yesterday</small>
                                </div>
                            </div>
                            
                            <!-- Activity Item 4 -->
                            <div class="activity-item d-flex pb-3 mb-3 border-bottom">
                                <div class="activity-icon me-3 bg-info text-white rounded-circle p-2">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1"><strong>Neha Gupta</strong> donated <strong>₹2,500</strong> to <a href="#">Merit Scholarship Fund</a></p>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                            </div>
                            
                            <!-- Activity Item 5 -->
                            <div class="activity-item d-flex">
                                <div class="activity-icon me-3 bg-danger text-white rounded-circle p-2">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1"><strong>Cultural Festival</strong> campaign was successfully completed, raising <strong>₹150,000</strong></p>
                                    <small class="text-muted">1 week ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Join Our Community</h5>
                    </div>
                    <div class="card-body">
                        <p>Be part of our growing community of innovators, creators, and change-makers at NIT Delhi.</p>
                        <div class="d-grid gap-2">
                            <a href="pages/register.php" class="btn btn-primary">Register Now</a>
                            <a href="pages/login.php" class="btn btn-outline-primary">Sign In</a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">How It Works</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">1</span>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Create Your Campaign</h6>
                                <p class="small text-muted mb-0">Easy step-by-step process</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">2</span>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Share With Community</h6>
                                <p class="small text-muted mb-0">Reach potential supporters</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">3</span>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Collect Funds</h6>
                                <p class="small text-muted mb-0">Receive donations securely</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include testimonials section
include_once 'includes/testimonials.php';

// Include footer
include_once 'includes/footer.php';
?>
