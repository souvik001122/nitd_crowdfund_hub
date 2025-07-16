/**
 * NIT Delhi Crowdfunding Platform
 * Donation-specific JavaScript functions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize donation form
    const donationForm = document.getElementById('donation-form');
    if (donationForm) {
        initDonationForm();
    }
    
    // Donation history page
    const donationHistory = document.getElementById('donation-history');
    if (donationHistory) {
        initDonationHistory();
    }
});

/**
 * Initialize donation form functionality
 */
function initDonationForm() {
    const donationForm = document.getElementById('donation-form');
    const amountInput = document.getElementById('donation-amount');
    const donationPresets = document.querySelectorAll('.donation-preset');
    const anonymousCheckbox = document.getElementById('donation-anonymous');
    const nameInput = document.getElementById('donation-name');
    const tributeCheckbox = document.getElementById('donation-tribute');
    const tributeSection = document.getElementById('tribute-section');
    const recurringCheckbox = document.getElementById('donation-recurring');
    const recurringOptions = document.getElementById('recurring-options');
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetailsSections = document.querySelectorAll('.payment-details');
    const donationSummaryAmount = document.getElementById('donation-summary-amount');
    
    // Format donation amount with proper currency
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/[^\d]/g, '');
            
            // Format with thousands separator
            if (value) {
                this.value = parseInt(value).toLocaleString('en-IN');
                
                // Update donation summary if it exists
                if (donationSummaryAmount) {
                    donationSummaryAmount.textContent = '₹' + this.value;
                }
            } else {
                if (donationSummaryAmount) {
                    donationSummaryAmount.textContent = '₹0';
                }
            }
            
            // Unselect any preset buttons
            if (donationPresets) {
                donationPresets.forEach(preset => {
                    preset.classList.remove('active');
                });
            }
        });
    }
    
    // Donation preset buttons
    if (donationPresets && amountInput) {
        donationPresets.forEach(preset => {
            preset.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all presets
                donationPresets.forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked preset
                this.classList.add('active');
                
                // Update amount input
                const amount = this.getAttribute('data-amount');
                amountInput.value = parseInt(amount).toLocaleString('en-IN');
                
                // Update donation summary if it exists
                if (donationSummaryAmount) {
                    donationSummaryAmount.textContent = '₹' + amountInput.value;
                }
            });
        });
    }
    
    // Anonymous donation toggle
    if (anonymousCheckbox && nameInput) {
        anonymousCheckbox.addEventListener('change', function() {
            if (this.checked) {
                nameInput.setAttribute('disabled', 'disabled');
                nameInput.previousValue = nameInput.value;
                nameInput.value = 'Anonymous';
            } else {
                nameInput.removeAttribute('disabled');
                nameInput.value = nameInput.previousValue || '';
            }
        });
    }
    
    // Tribute donation toggle
    if (tributeCheckbox && tributeSection) {
        tributeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                tributeSection.style.display = 'block';
                tributeSection.querySelectorAll('input, select').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                tributeSection.style.display = 'none';
                tributeSection.querySelectorAll('input, select').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    }
    
    // Recurring donation toggle
    if (recurringCheckbox && recurringOptions) {
        recurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurringOptions.style.display = 'block';
                recurringOptions.querySelectorAll('select').forEach(select => {
                    select.setAttribute('required', 'required');
                });
            } else {
                recurringOptions.style.display = 'none';
                recurringOptions.querySelectorAll('select').forEach(select => {
                    select.removeAttribute('required');
                });
            }
        });
    }
    
    // Payment method selection
    if (paymentMethodInputs && paymentDetailsSections) {
        paymentMethodInputs.forEach(input => {
            input.addEventListener('change', function() {
                const selectedMethod = this.value;
                
                // Hide all payment details sections
                paymentDetailsSections.forEach(section => {
                    section.style.display = 'none';
                    // Disable fields to prevent validation
                    section.querySelectorAll('input, select').forEach(field => {
                        field.setAttribute('disabled', 'disabled');
                    });
                });
                
                // Show selected payment method details
                const selectedSection = document.getElementById(`${selectedMethod}-details`);
                if (selectedSection) {
                    selectedSection.style.display = 'block';
                    // Enable fields for validation
                    selectedSection.querySelectorAll('input, select').forEach(field => {
                        field.removeAttribute('disabled');
                    });
                }
            });
        });
        
        // Trigger change event on the checked radio button to initialize the form
        const checkedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (checkedPaymentMethod) {
            checkedPaymentMethod.dispatchEvent(new Event('change'));
        } else if (paymentMethodInputs.length > 0) {
            // Select the first payment method by default
            paymentMethodInputs[0].checked = true;
            paymentMethodInputs[0].dispatchEvent(new Event('change'));
        }
    }
    
    // Form submission
    if (donationForm) {
        donationForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            e.preventDefault();
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Get form data
            const formData = new FormData(this);
            formData.append('action', 'process_donation');
            
            // Convert amount to number (remove formatting)
            const amountValue = amountInput.value.replace(/[^\d]/g, '');
            formData.set('amount', amountValue);
            
            // Process donation
            fetch('/api/donations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showToast('Donation processed successfully', 'success');
                    
                    // Redirect to thank you page
                    window.location.href = data.redirect_url;
                } else {
                    // Show error message
                    showToast('Error: ' + data.message, 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error processing donation:', error);
                showToast('An error occurred. Please try again.', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
}

/**
 * Initialize donation history functionality
 */
function initDonationHistory() {
    // Donation receipt download
    const receiptButtons = document.querySelectorAll('.download-receipt');
    
    if (receiptButtons.length > 0) {
        receiptButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const donationId = this.getAttribute('data-donation-id');
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
                this.disabled = true;
                
                // Request receipt generation
                fetch(`/api/donations.php?action=generate_receipt&donation_id=${donationId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to generate receipt');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Create download link
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `donation_receipt_${donationId}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        
                        // Reset button
                        this.innerHTML = originalText;
                        this.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error generating receipt:', error);
                        showToast('Failed to generate receipt', 'danger');
                        
                        // Reset button
                        this.innerHTML = originalText;
                        this.disabled = false;
                    });
            });
        });
    }
    
    // Recurring donation management
    const cancelRecurringButtons = document.querySelectorAll('.cancel-recurring');
    
    if (cancelRecurringButtons.length > 0) {
        cancelRecurringButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to cancel this recurring donation?')) {
                    return;
                }
                
                const subscriptionId = this.getAttribute('data-subscription-id');
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...';
                this.disabled = true;
                
                // Send cancellation request
                fetch('/api/donations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancel_recurring&subscription_id=${subscriptionId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Recurring donation cancelled successfully', 'success');
                        
                        // Update UI
                        const statusBadge = document.querySelector(`.recurring-status[data-subscription-id="${subscriptionId}"]`);
                        if (statusBadge) {
                            statusBadge.textContent = 'Cancelled';
                            statusBadge.classList.remove('bg-success');
                            statusBadge.classList.add('bg-secondary');
                        }
                        
                        // Hide the cancel button
                        this.style.display = 'none';
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error('Error cancelling recurring donation:', error);
                    showToast('An error occurred. Please try again.', 'danger');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });
    }
    
    // Donation filtering
    const filterForm = document.getElementById('donation-filter-form');
    
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
}
