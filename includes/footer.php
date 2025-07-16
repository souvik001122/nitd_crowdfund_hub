    </main>
    
    <?php
    // Check if $base_url is not set (in case footer is included directly)
    if (!isset($base_url)) {
        $is_in_page_dir = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
        $base_url = $is_in_page_dir ? '../' : '';
    }
    ?>
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5>NIT Delhi Crowdfunding</h5>
                    <p class="text-white-50 mb-4">Supporting innovative projects, research initiatives, and community development within the NIT Delhi ecosystem.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_url ?? ''; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php">Explore Campaigns</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/create_campaign.php">Start a Campaign</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/how_it_works.php">How It Works</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/about.php">About Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5>Categories</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php?category=research">Research Projects</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php?category=infrastructure">Infrastructure</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php?category=events">Campus Events</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php?category=student_initiatives">Student Initiatives</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>pages/campaigns.php?category=technology">Technology</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5>Contact Us</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt me-2"></i> National Institute of Technology Delhi, Sector A-7, Institutional Area, Narela, Delhi, 110040</li>
                        <li><i class="fas fa-phone me-2"></i> +91 11 3380 1000</li>
                        <li><i class="fas fa-envelope me-2"></i> crowdfunding@nitdelhi.ac.in</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom text-center text-md-start">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> NIT Delhi Crowdfunding. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                            <li class="list-inline-item"><a href="#">Terms of Use</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core JavaScript -->
    <script src="<?php echo $base_url ?? ''; ?>assets/js/main.js"></script>
    
    <?php if (isset($page_specific_js) && is_array($page_specific_js)): ?>
        <!-- Page Specific JavaScript -->
        <?php foreach ($page_specific_js as $js_file): ?>
        <script src="<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
