<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!-- teacher content ends -->
        </div>
    </main>

    <footer class="mt-12 pb-8 text-center text-sm">
        <div class="max-w-7xl mx-auto" style="color: #64748b;">© <?= date('Y') ?> Grading Management System</div>
    </footer>

    <!-- Animation Library -->
    <script src="<?= site_url('public/js/animations.js') ?>"></script>

    <script>
        // jQuery helper for flash messages and animations
        $(function(){
            // Auto-hide flash messages after 6s with fade effect
            setTimeout(function(){ 
                $('[style*="fee2e2"], [style*="dcfce7"]').fadeOut(800); 
            }, 6000);

            // Use custom confirm dialog for delete links
            $(document).on('click', 'a[data-confirm], button[data-confirm]', function(e){
                e.preventDefault();
                const element = $(this);
                const msg = element.data('confirm') || 'Are you sure you want to proceed?';
                const href = element.attr('href') || element.data('href');
                
                if (typeof showConfirmDialog === 'function') {
                    showConfirmDialog({
                        title: 'Confirm Action',
                        message: msg,
                        confirmText: 'Yes, Proceed',
                        cancelText: 'Cancel',
                        confirmClass: 'btn-danger',
                        icon: 'fa-exclamation-triangle',
                        onConfirm: function() {
                            if (href) window.location.href = href;
                            else if (element.is('button')) element.closest('form').submit();
                        }
                    });
                } else {
                    if (confirm(msg)) {
                        if (href) window.location.href = href;
                        else if (element.is('button')) element.closest('form').submit();
                    }
                }
            });

            // Enhanced delete confirmation
            $(document).on('click', '[data-delete]', function(e){
                e.preventDefault();
                const element = $(this);
                const itemName = element.data('delete') || 'this item';
                const href = element.attr('href') || element.data('href');
                
                if (typeof confirmDelete === 'function') {
                    confirmDelete({
                        title: 'Delete Confirmation',
                        message: `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
                        onConfirm: function() {
                            if (typeof showLoading === 'function') showLoading('Deleting...');
                            window.location.href = href;
                        }
                    });
                } else {
                    if (confirm(`Delete ${itemName}?`)) window.location.href = href;
                }
            });

            // Form submission animations
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                if (!submitBtn.hasClass('no-loading')) {
                    submitBtn.addClass('loading').prop('disabled', true);
                }
            });

            // Animate cards on page load
            $('.card').each(function(index) {
                $(this).css('opacity', '0');
                setTimeout(() => {
                    if (typeof animateIn === 'function') animateIn(this, 'fadeInUp');
                    $(this).css('opacity', '1');
                }, index * 100);
            });
        });

        // Handle grade input changes with animation
        $(document).on('change', '.grade-input', function() {
            const input = $(this);
            const grade = parseFloat(input.val());
            const studentId = input.data('student');
            const subjectId = input.data('subject');
            const period = input.data('period');

            // Validate grade input
            if (isNaN(grade) || grade < 0 || grade > 100) {
                alert('Please enter a valid grade between 0 and 100');
                input.val('');
                return;
            }

            // Show loading on input
            input.prop('disabled', true).addClass('opacity-50');

            // Send grade update
            $.ajax({
                url: '<?= site_url("teacher/update_grade") ?>',
                type: 'POST',
                data: {
                    student_id: studentId,
                    subject_id: subjectId,
                    period: period,
                    grade: grade
                },
                dataType: 'json',
                success: function(response) {
                    input.prop('disabled', false).removeClass('opacity-50');
                    
                    if (response.success) {
                        // Success - no animation
                        input.addClass('border-green-500').removeClass('border-red-500');
                        alert('Grade updated successfully');
                        
                        // Update final grade cell if provided
                        if (response.final_grade !== undefined && response.final_grade !== null) {
                            const fg = $('.final-grade[data-student="' + studentId + '"][data-subject="' + subjectId + '"]');
                            if (fg.length) {
                                fg.text(parseFloat(response.final_grade).toFixed(2));
                            }
                        }
                        setTimeout(() => input.removeClass('border-green-500'), 2000);
                    } else {
                        input.addClass('border-red-500');
                        alert(response.message || 'Error updating grade');
                    }
                },
                error: function() {
                    input.prop('disabled', false).removeClass('opacity-50');
                    input.addClass('border-red-500');
                    alert('Failed to update grade. Please try again.');
                }
                }
            });
        });
    </script>
</body>
</html>