/**
 * Admin Panel AJAX Utilities
 * Handles all AJAX operations for the admin panel
 */

// CSRF Token Setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const bgColor = {
        'success': 'bg-success-500',
        'error': 'bg-danger-500',
        'warning': 'bg-warning-500',
        'info': 'bg-primary-500'
    }[type] || 'bg-success-500';

    const toast = `
        <div class="toast-notification fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-[9999] animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}"></i>
                <span>${message}</span>
            </div>
        </div>
    `;

    $('body').append(toast);
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Auto remove after 3 seconds
    setTimeout(() => {
        $('.toast-notification').fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

/**
 * Show loading overlay
 */
function showLoading(target = 'body') {
    const loader = `
        <div class="ajax-loader fixed inset-0 bg-black/50 flex items-center justify-center z-[9998]">
            <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute animate-[hitZak_0.6s_ease-in-out_infinite_alternate]"></div>
        </div>
    `;
    $(target).append(loader);
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('.ajax-loader').remove();
}

/**
 * Confirm dialog with callback
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Delete user via AJAX
 */
function deleteUser(userId, userName) {
    confirmAction(`Are you sure you want to delete user "${userName}"?`, function() {
        showLoading();
        
        $.ajax({
            url: `/admin/users/${userId}`,
            type: 'DELETE',
            success: function(response) {
                hideLoading();
                showToast(`User "${userName}" deleted successfully!`, 'success');
                
                // Remove row from table
                $(`tr[data-user-id="${userId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Check if table is empty
                    if ($('tbody tr:visible').length === 0) {
                        $('tbody').html('<tr><td colspan="7" class="text-center">No users found</td></tr>');
                    }
                });
            },
            error: function(xhr) {
                hideLoading();
                const message = xhr.responseJSON?.message || 'Failed to delete user';
                showToast(message, 'error');
            }
        });
    });
}

/**
 * Delete post via AJAX
 */
function deletePost(postId, postContent) {
    const displayText = postContent ? postContent.substring(0, 30) + '...' : 'this post';
    
    confirmAction(`Are you sure you want to delete "${displayText}"?`, function() {
        showLoading();
        
        $.ajax({
            url: `/admin/posts/${postId}`,
            type: 'DELETE',
            success: function(response) {
                hideLoading();
                showToast('Post deleted successfully!', 'success');
                
                // Remove row from table
                $(`tr[data-post-id="${postId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Check if table is empty
                    if ($('tbody tr:visible').length === 0) {
                        $('tbody').html('<tr><td colspan="8" class="text-center">No posts found</td></tr>');
                    }
                });
            },
            error: function(xhr) {
                hideLoading();
                const message = xhr.responseJSON?.message || 'Failed to delete post';
                showToast(message, 'error');
            }
        });
    });
}

/**
 * Load dashboard stats via AJAX
 */
function loadDashboardStats() {
    $.ajax({
        url: '/admin/api/stats',
        type: 'GET',
        success: function(data) {
            if (data.total_users !== undefined) {
                $('#total-users-count').text(data.total_users);
            }
            if (data.verified_users !== undefined) {
                $('#verified-users-count').text(data.verified_users);
            }
            if (data.total_posts !== undefined) {
                $('#total-posts-count').text(data.total_posts);
            }
            if (data.total_comments !== undefined) {
                $('#total-comments-count').text(data.total_comments);
            }
        },
        error: function(xhr) {
            console.error('Failed to load dashboard stats:', xhr);
        }
    });
}

/**
 * Search users with debounce
 */
let searchTimeout;
function searchUsers(query) {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(function() {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('search', query);
        
        showLoading('.card-body');
        
        $.ajax({
            url: currentUrl.toString(),
            type: 'GET',
            success: function(html) {
                hideLoading();
                const newTableBody = $(html).find('tbody').html();
                $('tbody').html(newTableBody);
                
                // Reinitialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            },
            error: function(xhr) {
                hideLoading();
                showToast('Failed to search users', 'error');
            }
        });
    }, 500);
}

/**
 * Quick status toggle for users
 */
function toggleUserStatus(userId, currentStatus) {
    showLoading();
    
    $.ajax({
        url: `/admin/users/${userId}/toggle-status`,
        type: 'POST',
        data: {
            status: currentStatus === 'active' ? 'inactive' : 'active'
        },
        success: function(response) {
            hideLoading();
            showToast(response.message || 'Status updated successfully', 'success');
            
            // Update badge
            const badge = $(`tr[data-user-id="${userId}"] .status-badge`);
            if (response.status === 'active') {
                badge.removeClass('bg-warning').addClass('bg-success').text('Active');
            } else {
                badge.removeClass('bg-success').addClass('bg-warning').text('Inactive');
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('Failed to update status', 'error');
        }
    });
}

/**
 * Inline edit for quick updates
 */
function enableInlineEdit() {
    $('.editable').on('dblclick', function() {
        const $this = $(this);
        const currentValue = $this.text().trim();
        const field = $this.data('field');
        const recordId = $this.data('record-id');
        const recordType = $this.data('record-type');
        
        const input = $('<input>', {
            type: 'text',
            class: 'form-control form-control-sm',
            value: currentValue
        });
        
        $this.html(input);
        input.focus();
        
        input.on('blur keypress', function(e) {
            if (e.type === 'keypress' && e.which !== 13) return;
            
            const newValue = $(this).val();
            
            if (newValue !== currentValue) {
                updateField(recordType, recordId, field, newValue, $this);
            } else {
                $this.text(currentValue);
            }
        });
    });
}

/**
 * Update field via AJAX
 */
function updateField(type, id, field, value, $element) {
    showLoading();
    
    $.ajax({
        url: `/admin/${type}/${id}/quick-update`,
        type: 'PATCH',
        data: {
            field: field,
            value: value
        },
        success: function(response) {
            hideLoading();
            showToast(response.message || 'Updated successfully', 'success');
            $element.text(value);
        },
        error: function(xhr) {
            hideLoading();
            showToast('Failed to update', 'error');
            $element.text($element.data('original-value'));
        }
    });
}

/**
 * Form submission via AJAX
 */
function submitFormAjax(formSelector, successCallback) {
    $(formSelector).on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method') || 'POST';
        const formData = new FormData(this);
        
        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        
        showLoading();
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                submitBtn.prop('disabled', false);
                
                // Clear validation errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                
                showToast(response.message || 'Operation successful', 'success');
                
                if (successCallback) {
                    successCallback(response);
                }
            },
            error: function(xhr) {
                hideLoading();
                submitBtn.prop('disabled', false);
                
                // Clear previous errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
                    $.each(errors, function(field, messages) {
                        const input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                    
                    showToast('Please fix the validation errors', 'error');
                } else {
                    const message = xhr.responseJSON?.message || 'Operation failed';
                    showToast(message, 'error');
                }
            }
        });
    });
}

/**
 * Live validation
 */
function enableLiveValidation() {
    $('input[required], textarea[required], select[required]').on('blur', function() {
        const $this = $(this);
        
        if ($this.val().trim() === '') {
            $this.addClass('is-invalid');
            if (!$this.next('.invalid-feedback').length) {
                $this.after('<div class="invalid-feedback">This field is required</div>');
            }
        } else {
            $this.removeClass('is-invalid');
            $this.next('.invalid-feedback').remove();
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    // Enable inline editing
    enableInlineEdit();
    
    // Enable live validation
    enableLiveValidation();
    
    // Initialize feather icons after AJAX content loads
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// Add CSS for animations
if (!$('#admin-ajax-styles').length) {
    $('head').append(`
        <style id="admin-ajax-styles">
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }
            .editable {
                cursor: pointer;
                position: relative;
            }
            .editable:hover::after {
                content: '✎';
                position: absolute;
                right: -20px;
                opacity: 0.5;
            }
        </style>
    `);
}

