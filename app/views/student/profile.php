<?php $this->call->view('student/_header'); ?>

<!-- Profile Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2" style="color: #093FB4;">My Profile</h1>
    <p style="color: #1e293b;">View and update your personal information</p>
</div>

<!-- Profile Card -->
<div class="card rounded-lg p-8 max-w-4xl">
    <form id="profile-form" class="space-y-6">
        <!-- Personal Information Section -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b pb-2" style="color: #093FB4; border-color: rgba(9, 63, 180, 0.1);">
                <i class="fas fa-user mr-2" style="color: #10b981;"></i>
                Personal Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #1e293b;">
                        First Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" 
                           style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #1e293b;">Middle Name</label>
                    <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition"
                           style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #1e293b;">
                        Last Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" 
                           style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                           required>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b pb-2" style="color: #093FB4; border-color: rgba(9, 63, 180, 0.1);">
                <i class="fas fa-envelope mr-2" style="color: #3b82f6;"></i>
                Contact Information
            </h2>
            
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    Email Address <span class="text-red-400">*</span>
                </label>
                <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" 
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                       required>
            </div>
        </div>

        <!-- Additional Information -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b pb-2" style="color: #093FB4; border-color: rgba(9, 63, 180, 0.1);">
                <i class="fas fa-info-circle mr-2" style="color: #a855f7;"></i>
                Additional Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #1e293b;">Gender</label>
                    <select name="gender" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
                        <option value="">Select Gender</option>
                        <option value="Male" <?= ($student['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($student['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($student['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #1e293b;">Birthdate</label>
                    <input type="date" name="birthdate" value="<?= htmlspecialchars($student['birthdate'] ?? '') ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" style="color: #1e293b;">Address</label>
                <textarea name="address" rows="3" 
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-green-500 transition" style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Academic Information (Read-Only) -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center border-b pb-2" style="color: #093FB4; border-color: rgba(9, 63, 180, 0.1);">
                <i class="fas fa-graduation-cap mr-2" style="color: #f59e0b;"></i>
                Academic Information
                <span class="ml-2 text-xs" style="color: #1e293b;">(Admin Only)</span>
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #475569;">Grade Level</label>
                    <input type="text" value="<?= htmlspecialchars($student['grade_level'] ?? 'Not Set') ?>" 
                           class="w-full px-4 py-2 bg-gray-700/30 border border-gray-600/50 rounded-lg text-gray-400 cursor-not-allowed" 
                           readonly disabled>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-lock mr-1"></i>Only administrators can modify this field
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: #475569;">Section</label>
                    <input type="text" value="<?= htmlspecialchars($student['section'] ?? 'Not Set') ?>" 
                           class="w-full px-4 py-2 bg-gray-700/30 border border-gray-600/50 rounded-lg text-gray-400 cursor-not-allowed" 
                           readonly disabled>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-lock mr-1"></i>Only administrators can modify this field
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-white/10">
            <button type="button" id="cancel-btn" class="px-6 py-2 bg-gray-600/50 hover:bg-gray-600/70 text-white rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="submit" id="save-btn" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    const form = $('#profile-form');
    const saveBtn = $('#save-btn');
    const cancelBtn = $('#cancel-btn');
    let originalData = form.serialize();

    // Track changes
    form.find('input, select, textarea').on('change', function() {
        if ($(this).is(':not([readonly]):not([disabled])')) {
            $(this).addClass('border-yellow-500');
            setTimeout(() => {
                $(this).removeClass('border-yellow-500').addClass('border-green-500');
            }, 300);
        }
    });

    // Cancel button
    cancelBtn.on('click', function() {
        if (form.serialize() !== originalData) {
            showConfirmDialog({
                title: 'Discard Changes?',
                message: 'You have unsaved changes. Are you sure you want to discard them?',
                confirmText: 'Discard',
                confirmClass: 'bg-red-500 hover:bg-red-600',
                onConfirm: function() {
                    window.location.reload();
                }
            });
        } else {
            window.location.reload();
        }
    });

    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        // Disable button and show loading
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
        
        $.ajax({
            url: '<?= site_url("student/profile") ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    originalData = form.serialize();
                    
                    // Flash green on all changed fields
                    form.find('.border-green-500').each(function() {
                        pulseElement(this);
                        $(this).removeClass('border-green-500');
                    });
                    
                    // Reload after 1.5 seconds to update session
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                    shakeElement(form[0]);
                }
            },
            error: function() {
                toastr.error('Failed to update profile. Please try again.');
                shakeElement(form[0]);
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Save Changes');
            }
        });
    });

    // Animate form on load
    form.css('opacity', '0').css('transform', 'translateY(20px)');
    setTimeout(() => {
        form.css('transition', 'all 0.5s ease');
        form.css('opacity', '1').css('transform', 'translateY(0)');
    }, 100);
});
</script>

<?php $this->call->view('student/_footer'); ?>
