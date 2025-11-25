<?php include 'app/views/admin/_header.php'; ?>

<!-- Profile Content -->
<div class="card rounded-xl p-8 mb-6">
    <!-- Profile Header -->
    <div class="flex items-center justify-between mb-6 pb-6" style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
        <h2 class="text-2xl font-bold flex items-center" style="color: #093FB4;">
            <i class="fas fa-user-cog mr-3"></i>
            Profile Settings
        </h2>
        <span class="px-4 py-2 rounded-full text-sm font-medium" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
            Administrator
        </span>
    </div>

    <!-- Profile Image Section -->
    <div class="card rounded-xl p-6 mb-8 text-center" style="background: #E8F9FF;">
        <div class="relative inline-block">
            <img src="<?= isset($_SESSION['profile_image']) && $_SESSION['profile_image'] ? site_url('public/uploads/' . $_SESSION['profile_image']) : 'https://via.placeholder.com/120x120?text=' . substr($_SESSION['first_name'] ?? 'A', 0, 1) ?>" 
                 alt="Profile Image" 
                 class="w-32 h-32 rounded-full object-cover mx-auto mb-4 shadow-lg" style="border: 4px solid rgba(9, 63, 180, 0.2);">
            
            <form method="POST" action="<?= site_url('auth/upload_image') ?>" enctype="multipart/form-data" class="mt-3">
                <label class="px-6 py-3 rounded-lg cursor-pointer text-sm font-medium shadow-md hover-lift inline-block" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                    <i class="fas fa-camera mr-2"></i>Change Photo
                    <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>
        
        <h3 class="text-2xl font-bold mt-4 mb-2" style="color: #093FB4;">
            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
        </h3>
        <p class="text-lg" style="color: #475569;"><i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($user['email'] ?? '') ?></p>
        <p class="text-sm mt-2" style="color: #64748b;"><i class="fas fa-calendar mr-2"></i>Member since <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Unknown' ?></p>
    </div>

    <!-- Profile Form -->
    <div class="card rounded-xl p-6">
        <h4 class="text-xl font-bold mb-6 flex items-center" style="color: #093FB4;">
            <i class="fas fa-edit mr-3"></i>Edit Information
        </h4>
        
        <form method="POST" action="<?= site_url('auth/profile') ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium mb-2" style="color: #475569;">
                        <i class="fas fa-user mr-2"></i>First Name
                    </label>
                    <input type="text" id="first_name" name="first_name" required
                           class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;"
                           value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium mb-2" style="color: #475569;">
                        <i class="fas fa-user mr-2"></i>Last Name
                    </label>
                    <input type="text" id="last_name" name="last_name" required
                           class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;"
                           value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-2" style="color: #475569;">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium mb-2" style="color: #475569;">
                    <i class="fas fa-at mr-2"></i>Username
                </label>
                <input type="text" id="username" name="username" required
                       class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;"
                       value="<?= htmlspecialchars($auth['username'] ?? '') ?>">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="<?= site_url('admin') ?>" 
                   class="px-6 py-3 rounded-lg border font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Section -->
    <div class="card rounded-xl p-6 mt-6">
        <h4 class="text-xl font-bold mb-6 flex items-center" style="color: #093FB4;">
            <i class="fas fa-lock mr-3"></i>Change Password
        </h4>
        
        <form method="POST" action="<?= site_url('auth/change_password') ?>" class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium mb-2" style="color: #475569;">
                    <i class="fas fa-key mr-2"></i>Current Password
                </label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium mb-2" style="color: #475569;">
                    <i class="fas fa-lock mr-2"></i>New Password
                </label>
                <input type="password" id="new_password" name="new_password" required
                       class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium mb-2" style="color: #475569;">
                    <i class="fas fa-lock mr-2"></i>Confirm New Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-3 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" 
                        class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); color: #FFFFFF;">
                    <i class="fas fa-check mr-2"></i>Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'app/views/admin/_footer.php'; ?>
