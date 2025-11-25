/**
 * Global Animation and Effects Library
 * Provides reusable jQuery animations and effects
 */

// Initialize animations when document is ready
$(document).ready(function() {
    initializeAnimations();
});

function initializeAnimations() {
    // Add ripple effect to all buttons
    addRippleEffect();
    
    // Add smooth scroll
    addSmoothScroll();
    
    // Add hover effects
    addHoverEffects();
    
    // Initialize tooltips
    initializeTooltips();
}

/**
 * Ripple Effect on Buttons
 */
function addRippleEffect() {
    $(document).on('click', '.btn, button, .submit-btn', function(e) {
        const button = $(this);
        
        // Don't add ripple if button is disabled
        if (button.is(':disabled') || button.hasClass('no-ripple')) {
            return;
        }
        
        const ripple = $('<span class="ripple-effect"></span>');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.css({
            width: size,
            height: size,
            left: x,
            top: y
        });
        
        button.css('position', 'relative').css('overflow', 'hidden');
        button.append(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });
}

/**
 * Smooth Scroll to Anchor Links
 */
function addSmoothScroll() {
    $(document).on('click', 'a[href^="#"]', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800, 'swing');
        }
    });
}

/**
 * Enhanced Hover Effects
 */
function addHoverEffects() {
    // Card hover effect
    $('.card, .glassmorphism').hover(
        function() {
            $(this).addClass('hover-lift');
        },
        function() {
            $(this).removeClass('hover-lift');
        }
    );
}

/**
 * Initialize Bootstrap Tooltips (if using Bootstrap)
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
}

/**
 * Custom Confirmation Dialog with Animation
 */
function showConfirmDialog(options) {
    const defaults = {
        title: 'Confirm Action',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Yes, Proceed',
        cancelText: 'Cancel',
        confirmClass: 'btn-danger',
        icon: 'fa-exclamation-triangle',
        onConfirm: function() {},
        onCancel: function() {}
    };
    
    const settings = $.extend({}, defaults, options);
    
    // Create modal HTML
    const modalHtml = `
        <div class="custom-modal-overlay" id="confirmModal">
            <div class="custom-modal-container">
                <div class="custom-modal-content">
                    <div class="custom-modal-icon">
                        <i class="fas ${settings.icon}"></i>
                    </div>
                    <h3 class="custom-modal-title">${settings.title}</h3>
                    <p class="custom-modal-message">${settings.message}</p>
                    <div class="custom-modal-actions">
                        <button class="btn btn-secondary custom-modal-cancel">${settings.cancelText}</button>
                        <button class="btn ${settings.confirmClass} custom-modal-confirm">${settings.confirmText}</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#confirmModal').remove();
    
    // Append modal to body
    $('body').append(modalHtml);
    
    // Animate modal in
    setTimeout(() => {
        $('#confirmModal').addClass('show');
    }, 10);
    
    // Handle confirm
    $(document).on('click', '#confirmModal .custom-modal-confirm', function() {
        hideConfirmDialog();
        settings.onConfirm();
    });
    
    // Handle cancel
    $(document).on('click', '#confirmModal .custom-modal-cancel, #confirmModal .custom-modal-overlay', function(e) {
        if (e.target === this) {
            hideConfirmDialog();
            settings.onCancel();
        }
    });
}

function hideConfirmDialog() {
    $('#confirmModal').removeClass('show');
    setTimeout(() => {
        $('#confirmModal').remove();
    }, 300);
}

/**
 * Show Success Animation
 */
function showSuccessAnimation(message = 'Success!') {
    toastr.success(message);
    
    // Add confetti effect (optional)
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
}

/**
 * Show Loading Overlay
 */
function showLoading(message = 'Loading...') {
    const loadingHtml = `
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="loading-text">${message}</p>
            </div>
        </div>
    `;
    
    $('#loadingOverlay').remove();
    $('body').append(loadingHtml);
    setTimeout(() => $('#loadingOverlay').addClass('show'), 10);
}

function hideLoading() {
    $('#loadingOverlay').removeClass('show');
    setTimeout(() => $('#loadingOverlay').remove(), 300);
}

/**
 * Form Submission with Loading State
 */
function handleFormSubmit(formSelector, options = {}) {
    $(formSelector).on('submit', function(e) {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        // Add loading state
        submitBtn.addClass('loading').prop('disabled', true);
        
        if (options.showOverlay) {
            showLoading(options.loadingMessage || 'Processing...');
        }
        
        // If AJAX is not prevented, let form submit normally
        if (!options.preventDefault) {
            return true;
        }
        
        e.preventDefault();
        
        // Custom AJAX handler if provided
        if (options.onSubmit) {
            options.onSubmit(form, submitBtn);
        }
    });
}

/**
 * Delete Confirmation with Animation
 */
function confirmDelete(options) {
    showConfirmDialog({
        title: options.title || 'Delete Confirmation',
        message: options.message || 'This action cannot be undone. Are you sure?',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
        confirmClass: 'btn-danger',
        icon: 'fa-trash-alt',
        onConfirm: options.onConfirm || function() {}
    });
}

/**
 * Animate Element Entry
 */
function animateIn(element, animation = 'fadeInUp') {
    $(element).addClass('animated ' + animation);
    setTimeout(() => {
        $(element).removeClass('animated ' + animation);
    }, 1000);
}

/**
 * Count Up Animation for Numbers
 */
function animateNumber(element, targetNumber, duration = 1000) {
    const $element = $(element);
    const startNumber = 0;
    const increment = targetNumber / (duration / 16);
    let currentNumber = startNumber;
    
    const timer = setInterval(() => {
        currentNumber += increment;
        if (currentNumber >= targetNumber) {
            clearInterval(timer);
            $element.text(Math.floor(targetNumber));
        } else {
            $element.text(Math.floor(currentNumber));
        }
    }, 16);
}

/**
 * Shake Animation for Errors
 */
function shakeElement(element) {
    $(element).addClass('shake-animation');
    setTimeout(() => {
        $(element).removeClass('shake-animation');
    }, 500);
}

/**
 * Pulse Animation for Attention
 */
function pulseElement(element) {
    $(element).addClass('pulse-animation');
    setTimeout(() => {
        $(element).removeClass('pulse-animation');
    }, 1000);
}

// Global CSS for animations (inject dynamically)
const animationStyles = `
<style>
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: scale(0);
    animation: ripple-anim 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple-anim {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.hover-lift {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.custom-modal-overlay.show {
    opacity: 1;
}

.custom-modal-container {
    background: white;
    border-radius: 15px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    transform: scale(0.7);
    transition: transform 0.3s ease;
}

.custom-modal-overlay.show .custom-modal-container {
    transform: scale(1);
}

.custom-modal-icon {
    font-size: 60px;
    text-align: center;
    color: #f59e0b;
    margin-bottom: 20px;
}

.custom-modal-title {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    color: #333;
    margin-bottom: 15px;
}

.custom-modal-message {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
}

.custom-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.custom-modal-actions button {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.loading-overlay.show {
    opacity: 1;
}

.loading-spinner {
    text-align: center;
    color: white;
}

.loading-text {
    margin-top: 15px;
    font-size: 16px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.shake-animation {
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.pulse-animation {
    animation: pulse 1s;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.fadeInUp {
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
`;

// Inject styles on load
$(document).ready(function() {
    if (!$('#animation-styles').length) {
        $('head').append(animationStyles);
    }
});
