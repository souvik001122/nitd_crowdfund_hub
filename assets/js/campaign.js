/**
 * NIT Delhi Crowdfunding Platform
 * Campaign-specific JavaScript functions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Campaign creation form validation
    const campaignForm = document.getElementById('campaign-form');
    if (campaignForm) {
        initCampaignFormValidation();
        setupMediaPreview();
        initRichTextEditor();
        setupBudgetBreakdown();
    }

    // Campaign filtering
    const filterForm = document.getElementById('campaign-filter-form');
    if (filterForm) {
        initCampaignFilters();
    }

    // Campaign details page
    const campaignDetailsPage = document.querySelector('.campaign-details');
    if (campaignDetailsPage) {
        initCampaignDetailsPage();
    }
});

/**
 * Initialize campaign form validation
 */
function initCampaignFormValidation() {
    const campaignForm = document.getElementById('campaign-form');
    
    campaignForm.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        this.classList.add('was-validated');
    });

    // Goal amount formatting
    const goalInput = document.getElementById('campaign-goal');
    if (goalInput) {
        goalInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('en-IN');
            }
        });

        goalInput.addEventListener('blur', function() {
            if (this.value) {
                const numericValue = parseInt(this.value.replace(/[^\d]/g, ''));
                this.value = numericValue.toLocaleString('en-IN');
            }
        });
    }

    // End date validation (must be in the future)
    const endDateInput = document.getElementById('campaign-end-date');
    if (endDateInput) {
        const today = new Date();
        const minDate = new Date(today.setDate(today.getDate() + 7)); // Minimum 7 days from now
        const maxDate = new Date(today.setDate(today.getDate() + 90)); // Maximum 90 days from now
        
        endDateInput.min = minDate.toISOString().split('T')[0];
        endDateInput.max = maxDate.toISOString().split('T')[0];
        
        endDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            if (selectedDate <= now) {
                this.setCustomValidity('End date must be in the future');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Auto-save draft functionality
    let autoSaveTimer;
    const formInputs = campaignForm.querySelectorAll('input, textarea, select');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveCampaignDraft, 10000); // Save after 10 seconds of inactivity
        });
    });
}

/**
 * Save campaign draft automatically
 */
function saveCampaignDraft() {
    const campaignForm = document.getElementById('campaign-form');
    const formData = new FormData(campaignForm);
    formData.append('action', 'save_draft');
    
    fetch('/api/campaigns.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Draft saved successfully', 'success');
            
            // Update campaign ID for future saves if this is a new draft
            const campaignIdInput = document.getElementById('campaign-id');
            if (!campaignIdInput.value && data.campaign_id) {
                campaignIdInput.value = data.campaign_id;
            }
        } else {
            showToast('Failed to save draft: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving draft:', error);
        showToast('Failed to save draft', 'error');
    });
}

/**
 * Setup media preview for campaign images
 */
function setupMediaPreview() {
    const imageUpload = document.getElementById('campaign-images');
    const previewContainer = document.getElementById('image-preview-container');
    
    if (imageUpload && previewContainer) {
        imageUpload.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files.length > 5) {
                showToast('You can upload maximum 5 images', 'warning');
                this.value = '';
                return;
            }
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                
                // Check file type
                if (!file.type.match('image.*')) {
                    continue;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'col-md-4 col-sm-6 mb-3';
                    previewDiv.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" alt="Campaign image preview">
                            <div class="card-body">
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-image">Remove</button>
                            </div>
                        </div>
                    `;
                    
                    previewContainer.appendChild(previewDiv);
                    
                    // Setup remove button
                    previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                        previewDiv.remove();
                        // Note: We can't directly modify the FileList, so we'll handle this during form submission
                    });
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Initialize rich text editor for campaign description
 */
function initRichTextEditor() {
    const descriptionTextarea = document.getElementById('campaign-description');
    
    if (descriptionTextarea) {
        // Check if we're using a custom rich text editor library
        // For now, we'll just use basic textarea with some styling
        descriptionTextarea.classList.add('form-control');
        descriptionTextarea.style.minHeight = '200px';
        
        // Add a character counter
        const charCounter = document.createElement('small');
        charCounter.className = 'text-muted d-block text-end mt-1';
        charCounter.textContent = '0/3000 characters';
        
        descriptionTextarea.parentNode.insertBefore(charCounter, descriptionTextarea.nextSibling);
        
        descriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            const max = 3000;
            const remaining = max - count;
            
            charCounter.textContent = `${count}/${max} characters`;
            
            if (remaining < 0) {
                charCounter.classList.add('text-danger');
                this.value = this.value.substring(0, max);
                charCounter.textContent = `${max}/${max} characters`;
            } else {
                charCounter.classList.remove('text-danger');
            }
        });
    }
}

/**
 * Setup budget breakdown builder
 */
function setupBudgetBreakdown() {
    const budgetContainer = document.getElementById('budget-breakdown-container');
    const addBudgetItemButton = document.getElementById('add-budget-item');
    
    if (budgetContainer && addBudgetItemButton) {
        addBudgetItemButton.addEventListener('click', function() {
            const itemCount = budgetContainer.querySelectorAll('.budget-item').length;
            
            if (itemCount >= 10) {
                showToast('You can add maximum 10 budget items', 'warning');
                return;
            }
            
            const newItem = document.createElement('div');
            newItem.className = 'budget-item card mb-3';
            newItem.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Item Description</label>
                            <input type="text" class="form-control budget-item-description" name="budget_items[${itemCount}][description]" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input type="text" class="form-control budget-item-amount" name="budget_items[${itemCount}][amount]" required pattern="[0-9,]+" placeholder="0">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100 remove-budget-item">Remove</button>
                        </div>
                    </div>
                </div>
            `;
            
            budgetContainer.appendChild(newItem);
            
            // Setup amount formatting
            const amountInput = newItem.querySelector('.budget-item-amount');
            amountInput.addEventListener('input', function() {
                let value = this.value.replace(/[^\d]/g, '');
                if (value) {
                    this.value = parseInt(value).toLocaleString('en-IN');
                }
            });
            
            // Setup remove button
            newItem.querySelector('.remove-budget-item').addEventListener('click', function() {
                newItem.remove();
                updateBudgetTotal();
            });
            
            // Setup total update
            amountInput.addEventListener('change', updateBudgetTotal);
        });
        
        // Initial budget item
        if (budgetContainer.querySelectorAll('.budget-item').length === 0) {
            addBudgetItemButton.click();
        }
    }
}

/**
 * Update the budget total amount
 */
function updateBudgetTotal() {
    const budgetItems = document.querySelectorAll('.budget-item-amount');
    let total = 0;
    
    budgetItems.forEach(item => {
        const value = parseInt(item.value.replace(/[^\d]/g, '') || 0);
        total += value;
    });
    
    const totalElement = document.getElementById('budget-total');
    if (totalElement) {
        totalElement.textContent = total.toLocaleString('en-IN');
    }
    
    // Update goal amount if it exists
    const goalInput = document.getElementById('campaign-goal');
    if (goalInput) {
        goalInput.value = total.toLocaleString('en-IN');
    }
}

/**
 * Initialize campaign filters
 */
function initCampaignFilters() {
    const filterForm = document.getElementById('campaign-filter-form');
    const filterInputs = filterForm.querySelectorAll('input, select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Clear filters button
    const clearFiltersButton = document.getElementById('clear-filters');
    if (clearFiltersButton) {
        clearFiltersButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            filterInputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
            
            filterForm.submit();
        });
    }
}

/**
 * Initialize campaign details page
 */
function initCampaignDetailsPage() {
    // Image gallery functionality
    const galleryThumbnails = document.querySelectorAll('.gallery-thumbnail');
    const mainImage = document.getElementById('main-campaign-image');
    
    if (galleryThumbnails.length > 0 && mainImage) {
        galleryThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Remove active class from all thumbnails
                galleryThumbnails.forEach(item => item.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                mainImage.src = this.getAttribute('data-full-image');
            });
        });
    }
    
    // Comment form
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_comment');
            
            fetch('/api/campaigns.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear form
                    document.getElementById('comment-text').value = '';
                    
                    // Add new comment to the list
                    const commentsList = document.getElementById('comments-list');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment-item mb-3 p-3 border rounded';
                    newComment.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center mb-2">
                                <div class="comment-avatar me-2">
                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">${data.comment.author}</h6>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">${data.comment.text}</p>
                    `;
                    
                    if (commentsList) {
                        commentsList.prepend(newComment);
                    }
                    
                    showToast('Comment added successfully', 'success');
                } else {
                    showToast('Failed to add comment: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error adding comment:', error);
                showToast('Failed to add comment', 'error');
            });
        });
    }
    
    // Campaign sharing
    const shareButtons = document.querySelectorAll('.share-button');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const platform = this.getAttribute('data-platform');
            const campaignTitle = this.getAttribute('data-title');
            const campaignUrl = window.location.href;
            
            let shareUrl = '';
            
            switch (platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(campaignUrl)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent('Check out this campaign: ' + campaignTitle)}&url=${encodeURIComponent(campaignUrl)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(campaignTitle + ' ' + campaignUrl)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(campaignUrl)}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=${encodeURIComponent('Check out this campaign: ' + campaignTitle)}&body=${encodeURIComponent('I thought you might be interested in this campaign: ' + campaignUrl)}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });
    
    // Donation amount presets
    const donationPresets = document.querySelectorAll('.donation-preset');
    const customAmountInput = document.getElementById('donation-amount');
    
    if (donationPresets.length > 0 && customAmountInput) {
        donationPresets.forEach(preset => {
            preset.addEventListener('click', function() {
                // Remove active class from all presets
                donationPresets.forEach(item => item.classList.remove('active'));
                
                // Add active class to clicked preset
                this.classList.add('active');
                
                // Update custom amount input
                const amount = this.getAttribute('data-amount');
                customAmountInput.value = amount;
            });
        });
        
        // Update presets when custom amount changes
        customAmountInput.addEventListener('input', function() {
            const value = this.value.replace(/[^\d]/g, '');
            
            // Unselect presets
            donationPresets.forEach(preset => {
                preset.classList.remove('active');
            });
            
            // Format the input value
            if (value) {
                this.value = parseInt(value).toLocaleString('en-IN');
            }
        });
    }
}
