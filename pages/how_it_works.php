
<?php
/**
 * NIT Delhi Crowdfunding Platform
 * How It Works Page
 */

// Set page title
$page_title = 'How It Works';

// Include header
include_once '../includes/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5">How It Works</h1>
    
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto text-center">
            <p class="lead">The NIT Delhi Crowdfunding Platform empowers students, faculty, and alumni to raise funds for innovative projects, research initiatives, and campus improvements. Our streamlined process makes it easy to create campaigns and support meaningful initiatives.</p>
        </div>
    </div>
    
    <div class="row g-4 mb-5">
        <!-- Step 1 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="display-4 mb-3 text-primary">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>1. Create Account</h3>
                    <p class="text-muted">Sign up with your NIT Delhi email to get started. Verify your account to begin creating campaigns or supporting initiatives.</p>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="display-4 mb-3 text-primary">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>2. Start Campaign</h3>
                    <p class="text-muted">Create your campaign with a clear goal, compelling story, and relevant media to engage potential donors. Set your funding target and timeline.</p>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="display-4 mb-3 text-primary">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3>3. Receive Support</h3>
                    <p class="text-muted">Share your campaign and receive donations from the NIT Delhi community. Track progress in real-time and keep donors updated.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- For Campaign Creators Section -->
    <div class="row mt-5 mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="mb-4 border-bottom pb-3"><i class="fas fa-lightbulb text-warning me-2"></i>For Campaign Creators</h3>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Getting Started</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Register with your NIT Delhi email</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Complete your profile with verification</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Read the campaign guidelines</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Plan your campaign objectives and timeline</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Creating a Successful Campaign</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Write a compelling campaign story</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Add high-quality photos and videos</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Create a detailed budget breakdown</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Set realistic funding goals and deadlines</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <h5 class="mb-3">Managing Your Campaign</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Share campaign with your network</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Post regular updates to keep donors engaged</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Respond to donor questions promptly</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Track fundraising progress in your dashboard</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">After Funding</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Receive funds after campaign reaches deadline</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Implement your project or initiative</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Provide project implementation updates</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Share final outcomes with your supporters</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- For Donors Section -->
    <div class="row mt-5 mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="mb-4 border-bottom pb-3"><i class="fas fa-heart text-danger me-2"></i>For Donors</h3>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Finding Campaigns</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Register with your NIT Delhi email (optional)</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Browse campaigns by category or department</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Use search and filters to find relevant projects</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Read campaign details and creator backgrounds</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Making Donations</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Donate any amount to campaigns you support</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Make secure payments through our platform</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Receive instant confirmation of your donation</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Option to donate anonymously if preferred</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <h5 class="mb-3">Tracking Progress</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Receive email updates on campaign progress</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>View all your donations in your dashboard</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Comment and engage with campaign creators</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Share campaigns with your own network</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Impact & Recognition</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Receive acknowledgment for your support</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Get updates on how funds are being utilized</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>See the impact of projects you've supported</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>Donor recognition based on contribution level</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-lg-10 mx-auto">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="accordion" id="faqAccordion">
                <!-- FAQ Item 1 -->
                <div class="accordion-item border mb-3 shadow-sm">
                    <h2 class="accordion-header" id="faqHeading1">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
                            Who can create campaigns on the NIT Delhi Crowdfunding Platform?
                        </button>
                    </h2>
                    <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Current students, faculty members, and verified alumni of NIT Delhi can create campaigns. All users must register with their NIT Delhi email addresses and complete the verification process before creating campaigns.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="accordion-item border mb-3 shadow-sm">
                    <h2 class="accordion-header" id="faqHeading2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                            What types of projects can be funded through this platform?
                        </button>
                    </h2>
                    <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>The platform supports various categories of projects including:</p>
                            <ul>
                                <li>Research initiatives and innovative projects</li>
                                <li>Infrastructure improvements around campus</li>
                                <li>Events and cultural activities</li>
                                <li>Student club initiatives and competitions</li>
                                <li>Technology and entrepreneurship ventures</li>
                            </ul>
                            <p>All projects must align with NIT Delhi's values and guidelines.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="accordion-item border mb-3 shadow-sm">
                    <h2 class="accordion-header" id="faqHeading3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                            What happens if a campaign doesn't reach its funding goal?
                        </button>
                    </h2>
                    <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>We use a flexible funding model, which means campaign creators receive all funds raised even if the campaign doesn't reach its target goal. However, creators should clearly communicate how funds will be used in both full-funding and partial-funding scenarios.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="accordion-item border mb-3 shadow-sm">
                    <h2 class="accordion-header" id="faqHeading4">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                            How secure are donations made through the platform?
                        </button>
                    </h2>
                    <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>We prioritize the security of all transactions. The platform uses industry-standard encryption and secure payment gateways for processing donations. All payment information is handled according to PCI DSS guidelines, and we never store your complete payment details on our servers.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="accordion-item border mb-3 shadow-sm">
                    <h2 class="accordion-header" id="faqHeading5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                            How is campaign accountability ensured?
                        </button>
                    </h2>
                    <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>All campaign creators are required to provide regular updates on their progress both during and after the fundraising period. The platform also includes a reporting system that allows donors to flag concerns. For institutional campaigns, additional oversight is provided by relevant department heads or faculty advisors.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA Section -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto text-center">
            <div class="card bg-primary text-white shadow p-4">
                <div class="card-body">
                    <h3>Ready to make a difference?</h3>
                    <p class="lead">Join the NIT Delhi Crowdfunding community today and be part of transformative initiatives.</p>
                    <div class="mt-4">
                        <a href="../pages/register.php" class="btn btn-lg btn-light me-3">Create Account</a>
                        <a href="../pages/campaigns.php" class="btn btn-lg btn-outline-light">Explore Campaigns</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
