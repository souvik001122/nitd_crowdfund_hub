/**
 * NIT Delhi Crowdfunding Platform
 * Dashboard-specific JavaScript functions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize campaign management
    initCampaignManagement();
    
    // Initialize dashboard charts
    initDashboardCharts();
    
    // Initialize dashboard tabs
    initDashboardTabs();
    
    // Initialize admin functionality if on admin page
    if (document.querySelector('.admin-dashboard')) {
        initAdminFunctions();
    }
});

/**
 * Initialize campaign management functionality
 */
function initCampaignManagement() {
    const campaignActions = document.querySelectorAll('.campaign-action');
    
    if (campaignActions.length > 0) {
        campaignActions.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.getAttribute('data-action');
                const campaignId = this.getAttribute('data-campaign-id');
                
                // Confirm deletion
                if (action === 'delete' && !confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
                    return;
                }
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                // Send action request
                fetch('/api/campaigns.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=${action}_campaign&campaign_id=${campaignId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showToast(`Campaign ${action}d successfully`, 'success');
                        
                        // Handle UI updates based on action
                        if (action === 'delete') {
                            // Remove campaign card/row from the list
                            const campaignElement = this.closest('.campaign-card, .campaign-row');
                            if (campaignElement) {
                                campaignElement.remove();
                            }
                        } else if (action === 'publish') {
                            // Update status badge
                            const statusBadge = document.querySelector(`.campaign-status[data-campaign-id="${campaignId}"]`);
                            if (statusBadge) {
                                statusBadge.textContent = 'Published';
                                statusBadge.className = 'campaign-status badge bg-success';
                            }
                            
                            // Update action buttons
                            this.style.display = 'none';
                            const unpublishButton = document.querySelector(`.campaign-action[data-action="unpublish"][data-campaign-id="${campaignId}"]`);
                            if (unpublishButton) {
                                unpublishButton.style.display = 'inline-block';
                            }
                        } else if (action === 'unpublish') {
                            // Update status badge
                            const statusBadge = document.querySelector(`.campaign-status[data-campaign-id="${campaignId}"]`);
                            if (statusBadge) {
                                statusBadge.textContent = 'Draft';
                                statusBadge.className = 'campaign-status badge bg-secondary';
                            }
                            
                            // Update action buttons
                            this.style.display = 'none';
                            const publishButton = document.querySelector(`.campaign-action[data-action="publish"][data-campaign-id="${campaignId}"]`);
                            if (publishButton) {
                                publishButton.style.display = 'inline-block';
                            }
                        }
                    } else {
                        // Show error message
                        showToast(`Error: ${data.message}`, 'danger');
                    }
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error(`Error performing ${action} action:`, error);
                    showToast('An error occurred. Please try again.', 'danger');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });
    }
}

/**
 * Initialize dashboard charts
 */
function initDashboardCharts() {
    // Donation history chart
    const donationChartCanvas = document.getElementById('donation-history-chart');
    if (donationChartCanvas) {
        // Fetch donation history data
        fetch('/api/users.php?action=get_donation_history')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderDonationChart(donationChartCanvas, data.donations);
                }
            })
            .catch(error => {
                console.error('Error fetching donation history:', error);
            });
    }
    
    // Campaign performance chart
    const campaignChartCanvas = document.getElementById('campaign-performance-chart');
    if (campaignChartCanvas) {
        // Fetch campaign performance data
        fetch('/api/users.php?action=get_campaign_performance')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCampaignChart(campaignChartCanvas, data.campaigns);
                }
            })
            .catch(error => {
                console.error('Error fetching campaign performance:', error);
            });
    }
}

/**
 * Render donation history chart
 * @param {HTMLCanvasElement} canvas - Canvas element for the chart
 * @param {Array} donations - Donation history data
 */
function renderDonationChart(canvas, donations) {
    // Group donations by month
    const monthlyDonations = {};
    
    donations.forEach(donation => {
        const date = new Date(donation.created_at);
        const monthYear = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        
        if (!monthlyDonations[monthYear]) {
            monthlyDonations[monthYear] = 0;
        }
        
        monthlyDonations[monthYear] += parseFloat(donation.amount);
    });
    
    // Sort months chronologically
    const sortedMonths = Object.keys(monthlyDonations).sort();
    
    // Format labels for display
    const labels = sortedMonths.map(month => {
        const [year, monthNum] = month.split('-');
        const date = new Date(year, monthNum - 1, 1);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    // Prepare data for chart
    const data = sortedMonths.map(month => monthlyDonations[month]);
    
    // Create chart
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Donation Amount (₹)',
                data: data,
                backgroundColor: 'rgba(17, 100, 102, 0.2)',
                borderColor: 'rgba(17, 100, 102, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: 'rgba(17, 100, 102, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString('en-IN');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                },
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Monthly Donation History',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
}

/**
 * Render campaign performance chart
 * @param {HTMLCanvasElement} canvas - Canvas element for the chart
 * @param {Array} campaigns - Campaign performance data
 */
function renderCampaignChart(canvas, campaigns) {
    // Sort campaigns by goal completion percentage
    campaigns.sort((a, b) => {
        const aPercentage = (a.amount_raised / a.goal) * 100;
        const bPercentage = (b.amount_raised / b.goal) * 100;
        return bPercentage - aPercentage;
    });
    
    // Limit to top 5 campaigns
    const topCampaigns = campaigns.slice(0, 5);
    
    // Prepare data for chart
    const labels = topCampaigns.map(campaign => campaign.title);
    const raisedData = topCampaigns.map(campaign => campaign.amount_raised);
    const goalData = topCampaigns.map(campaign => campaign.goal);
    
    // Create chart
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Amount Raised',
                    data: raisedData,
                    backgroundColor: 'rgba(42, 157, 143, 0.8)',
                    borderColor: 'rgba(42, 157, 143, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Goal',
                    data: goalData,
                    backgroundColor: 'rgba(231, 111, 81, 0.2)',
                    borderColor: 'rgba(231, 111, 81, 1)',
                    borderWidth: 1,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            // Truncate long campaign titles
                            const label = this.getLabelForValue(value);
                            return label.length > 15 ? label.substring(0, 15) + '...' : label;
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString('en-IN');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y;
                            return label + ': ₹' + value.toLocaleString('en-IN');
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Top Campaign Performance',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
}

/**
 * Initialize dashboard tabs functionality
 */
function initDashboardTabs() {
    const tabLinks = document.querySelectorAll('.dashboard-tab-link');
    
    if (tabLinks.length > 0) {
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get tab ID from href
                const tabId = this.getAttribute('href');
                
                // Remove active class from all tabs and links
                document.querySelectorAll('.dashboard-tab').forEach(tab => {
                    tab.classList.remove('active', 'show');
                });
                
                tabLinks.forEach(tabLink => {
                    tabLink.classList.remove('active');
                });
                
                // Add active class to selected tab and link
                document.querySelector(tabId).classList.add('active', 'show');
                this.classList.add('active');
                
                // Store active tab in local storage
                localStorage.setItem('activeTab', tabId);
            });
        });
        
        // Restore active tab from local storage
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            const activeTabLink = document.querySelector(`a[href="${activeTab}"]`);
            if (activeTabLink) {
                activeTabLink.click();
            }
        }
    }
}

/**
 * Initialize admin-specific functions
 */
function initAdminFunctions() {
    // Campaign approval/rejection
    const approvalButtons = document.querySelectorAll('.campaign-approval-btn');
    
    if (approvalButtons.length > 0) {
        approvalButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.getAttribute('data-action'); // 'approve' or 'reject'
                const campaignId = this.getAttribute('data-campaign-id');
                let rejectionReason = '';
                
                // Get rejection reason if rejecting
                if (action === 'reject') {
                    rejectionReason = prompt('Please provide a reason for rejection:');
                    if (rejectionReason === null) {
                        // User cancelled the prompt
                        return;
                    }
                }
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                // Prepare request data
                const formData = new FormData();
                formData.append('action', `${action}_campaign`);
                formData.append('campaign_id', campaignId);
                if (rejectionReason) {
                    formData.append('rejection_reason', rejectionReason);
                }
                
                // Send approval/rejection request
                fetch('/api/admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`Campaign ${action === 'approve' ? 'approved' : 'rejected'} successfully`, 'success');
                        
                        // Update UI
                        const campaignRow = this.closest('tr');
                        if (campaignRow) {
                            if (action === 'approve') {
                                // Update status badge
                                const statusCell = campaignRow.querySelector('.campaign-status');
                                if (statusCell) {
                                    statusCell.innerHTML = '<span class="badge bg-success">Approved</span>';
                                }
                                
                                // Hide approval buttons
                                campaignRow.querySelectorAll('.campaign-approval-btn').forEach(btn => {
                                    btn.style.display = 'none';
                                });
                            } else {
                                // Update status badge
                                const statusCell = campaignRow.querySelector('.campaign-status');
                                if (statusCell) {
                                    statusCell.innerHTML = '<span class="badge bg-danger">Rejected</span>';
                                }
                                
                                // Hide approval buttons
                                campaignRow.querySelectorAll('.campaign-approval-btn').forEach(btn => {
                                    btn.style.display = 'none';
                                });
                            }
                        }
                    } else {
                        showToast(`Error: ${data.message}`, 'danger');
                    }
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error(`Error ${action}ing campaign:`, error);
                    showToast('An error occurred. Please try again.', 'danger');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });
    }
    
    // User management
    const userActionButtons = document.querySelectorAll('.user-action-btn');
    
    if (userActionButtons.length > 0) {
        userActionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.getAttribute('data-action'); // 'verify', 'block', 'unblock', etc.
                const userId = this.getAttribute('data-user-id');
                
                // Confirm action
                if (!confirm(`Are you sure you want to ${action} this user?`)) {
                    return;
                }
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                // Send action request
                fetch('/api/admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=${action}_user&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`User ${action}ed successfully`, 'success');
                        
                        // Update UI based on action
                        if (action === 'verify') {
                            // Update verification badge
                            const statusCell = this.closest('tr').querySelector('.user-verification');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-success"><i class="fas fa-check"></i> Verified</span>';
                            }
                            
                            // Hide verify button
                            this.style.display = 'none';
                        } else if (action === 'block') {
                            // Update status badge
                            const statusCell = this.closest('tr').querySelector('.user-status');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-danger">Blocked</span>';
                            }
                            
                            // Switch button to unblock
                            this.setAttribute('data-action', 'unblock');
                            this.textContent = 'Unblock';
                            this.classList.remove('btn-danger');
                            this.classList.add('btn-success');
                        } else if (action === 'unblock') {
                            // Update status badge
                            const statusCell = this.closest('tr').querySelector('.user-status');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-success">Active</span>';
                            }
                            
                            // Switch button to block
                            this.setAttribute('data-action', 'block');
                            this.textContent = 'Block';
                            this.classList.remove('btn-success');
                            this.classList.add('btn-danger');
                        }
                    } else {
                        showToast(`Error: ${data.message}`, 'danger');
                    }
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error(`Error ${action}ing user:`, error);
                    showToast('An error occurred. Please try again.', 'danger');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });
    }
    
    // Admin dashboard statistics refresh
    const refreshStatsButton = document.getElementById('refresh-stats');
    
    if (refreshStatsButton) {
        refreshStatsButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
            this.disabled = true;
            
            // Fetch updated stats
            fetch('/api/admin.php?action=get_dashboard_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats cards
                        document.getElementById('total-campaigns').textContent = data.stats.total_campaigns;
                        document.getElementById('total-donations').textContent = data.stats.total_donations;
                        document.getElementById('total-users').textContent = data.stats.total_users;
                        document.getElementById('total-amount').textContent = '₹' + parseInt(data.stats.total_amount).toLocaleString('en-IN');
                        
                        showToast('Statistics refreshed successfully', 'success');
                    } else {
                        showToast(`Error: ${data.message}`, 'danger');
                    }
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error('Error refreshing stats:', error);
                    showToast('An error occurred. Please try again.', 'danger');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
        });
    }
}
