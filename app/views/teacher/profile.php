<?php include 'app/views/teacher/_header.php'; ?>

<!-- Profile Content -->
<div class="card rounded-xl p-8 mb-6">
    <!-- Profile Header -->
    <div class="flex items-center justify-between mb-6 pb-6 border-b border-white/10">
        <h2 class="text-2xl font-bold flex items-center" style="color: #093FB4;">
            <i class="fas fa-user-circle mr-3" style="color: #10b981;"></i>
            Profile Settings
        </h2>
        <span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-500/20 border border-blue-500/30 text-blue-300">
            Teacher Account
        </span>
    </div>

    <!-- Profile Image Section -->
    <div class="card rounded-lg p-6 mb-8 text-center">
        <div class="relative inline-block">
            <img src="<?= isset($_SESSION['profile_image']) && $_SESSION['profile_image'] ? site_url('public/uploads/' . $_SESSION['profile_image']) : 'https://via.placeholder.com/120x120?text=' . substr($_SESSION['first_name'] ?? 'T', 0, 1) ?>" 
                 alt="Profile Image" 
                 class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-white/20 shadow-lg">
            
            <form method="POST" action="<?= site_url('auth/upload_image') ?>" enctype="multipart/form-data" class="mt-3">
                <label class="px-4 py-2 rounded-lg cursor-pointer text-sm font-medium bg-blue-500/20 border border-blue-500/30 text-blue-300 hover:bg-blue-500/30 transition inline-block">
                    <i class="fas fa-camera mr-2"></i>Change Photo
                    <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>
        
        <h3 class="text-2xl font-semibold mt-4 mb-2" style="color: #093FB4;">
            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
        </h3>
        <p class="text-lg" style="color: #10b981;"><i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($user['email'] ?? '') ?></p>
        <p class="text-sm mt-2" style="color: #1e293b;"><i class="fas fa-calendar mr-2"></i>Member since <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Unknown' ?></p>
    </div>

    <!-- Profile Form -->
    <div class="card rounded-lg p-6">
        <h4 class="text-xl font-semibold mb-6 flex items-center" style="color: #093FB4;">
            <i class="fas fa-edit mr-3" style="color: #10b981;"></i>Edit Information
        </h4>
        
        <form method="POST" action="<?= site_url('auth/profile') ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                        <i class="fas fa-user mr-2"></i>First Name
                    </label>
                    <input type="text" id="first_name" name="first_name" required
                           class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                           style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                           value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                        <i class="fas fa-user mr-2"></i>Last Name
                    </label>
                    <input type="text" id="last_name" name="last_name" required
                           class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                           style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                           value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-at mr-2"></i>Username
                </label>
                <input type="text" id="username" name="username" required
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                       value="<?= htmlspecialchars($auth['username'] ?? '') ?>">
            </div>

            <div>
                <label for="contact_number" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-phone mr-2"></i>Contact Number
                </label>
                <input type="text" id="contact_number" name="contact_number"
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                       value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
            </div>

            <div>
                <label for="specialization" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-graduation-cap mr-2"></i>Specialization
                </label>
                <input type="text" id="specialization" name="specialization"
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;"
                       value="<?= htmlspecialchars($user['specialization'] ?? '') ?>">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="<?= site_url('teacher') ?>" 
                   class="px-6 py-3 rounded-lg bg-gray-500/20 border border-gray-500/30 text-gray-300 hover:bg-gray-500/30 transition">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-lg bg-blue-500/20 border border-blue-500/30 text-blue-300 hover:bg-blue-500/30 transition">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Section -->
    <div class="card rounded-lg p-6 mt-6">
        <h4 class="text-xl font-semibold mb-6 flex items-center" style="color: #093FB4;">
            <i class="fas fa-lock mr-3" style="color: #10b981;"></i>Change Password
        </h4>
        
        <form method="POST" action="<?= site_url('auth/change_password') ?>" class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-key mr-2"></i>Current Password
                </label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-lock mr-2"></i>New Password
                </label>
                <input type="password" id="new_password" name="new_password" required
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium mb-2" style="color: #1e293b;">
                    <i class="fas fa-lock mr-2"></i>Confirm New Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-green-500 transition"
                       style="background: #FFFFFF; border-color: #e2e8f0; color: #1e293b;">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" 
                        class="px-6 py-3 rounded-lg bg-green-500/20 border border-green-500/30 text-green-300 hover:bg-green-500/30 transition">
                    <i class="fas fa-check mr-2"></i>Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'app/views/teacher/_footer.php'; ?>
