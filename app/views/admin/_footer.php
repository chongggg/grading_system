<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!-- admin content ends -->
        </div>
    </main>

    <footer class="mt-12 pb-8 text-center text-gray-400 text-sm">
        <div class="max-w-7xl mx-auto">© <?= date('Y') ?> Grading Management System</div>
    </footer>

    <!-- Animation Library -->
    <script src="<?= site_url('public/js/animations.js') ?>"></script>

    <script>
        // Small jQuery helper for table search and flash auto-hide
        $(function(){
            // Auto-hide flash messages after 6s with fade effect
            setTimeout(function(){ 
                $('.card').filter(function(){ 
                    return $(this).find('.fa-exclamation-triangle, .fa-check-circle').length>0 
                }).fadeOut(800); 
            }, 6000);

            // Use custom confirm dialog for delete links
            $(document).on('click', 'a[data-confirm], button[data-confirm]', function(e){
                e.preventDefault();
                const element = $(this);
                const msg = element.data('confirm') || 'Are you sure you want to proceed?';
                const href = element.attr('href') || element.data('href');
                
                showConfirmDialog({
                    title: 'Confirm Action',
                    message: msg,
                    confirmText: 'Yes, Proceed',
                    cancelText: 'Cancel',
                    confirmClass: 'btn-danger',
                    icon: 'fa-exclamation-triangle',
                    onConfirm: function() {
                        if (href) {
                            window.location.href = href;
                        } else if (element.is('button')) {
                            element.closest('form').submit();
                        }
                    }
                });
            });

            // Enhanced delete confirmation
            $(document).on('click', '[data-delete]', function(e){
                e.preventDefault();
                const element = $(this);
                const itemName = element.data('delete') || 'this item';
                const href = element.attr('href') || element.data('href');
                
                confirmDelete({
                    title: 'Delete Confirmation',
                    message: `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
                    onConfirm: function() {
                        showLoading('Deleting...');
                        window.location.href = href;
                    }
                });
            });

            // Form submission animations
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                if (!submitBtn.hasClass('no-loading')) {
                    submitBtn.addClass('loading').prop('disabled', true);
                }
            });

            // Animate cards on page load
            $('.card, .glassmorphism').each(function(index) {
                $(this).css('opacity', '0');
                setTimeout(() => {
                    animateIn(this, 'fadeInUp');
                    $(this).css('opacity', '1');
                }, index * 100);
            });
        });
    </script>
</body>
</html>
