<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Edit Student</h2>
        <p class="text-sm text-gray-400 mt-1">Update student information</p>
    </div>

    <?php if (isset($student)): ?>
        <form action="<?= site_url('admin/edit_student/' . $student['id']) ?>" method="POST" class="max-w-2xl">
            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="first_name">First Name</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               required 
                               value="<?= htmlspecialchars($student['first_name'] ?? '') ?>"
                               class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2" for="last_name">Last Name</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               required 
                               value="<?= htmlspecialchars($student['last_name'] ?? '') ?>"
                               class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <label class="block text-sm font-medium mb-2" for="email">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?= htmlspecialchars($student['email'] ?? '') ?>"
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" for="phone">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?= htmlspecialchars($student['phone'] ?? '') ?>"
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="gender">Gender</label>
                        <select id="gender" 
                                name="gender" 
                                class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Gender</option>
                            <option value="male" <?= (isset($student['gender']) && strtolower($student['gender']) === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (isset($student['gender']) && strtolower($student['gender']) === 'female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="birth_date">Birth Date</label>
                        <input type="date" 
                               id="birth_date" 
                               name="birth_date" 
                               value="<?= htmlspecialchars($student['birth_date'] ?? '') ?>"
                               class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Account Credentials -->
                <div class="border-t border-white/10 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Account Credentials</h3>
                        <button type="button" 
                                id="showPasswordFields"
                                class="text-sm text-blue-400 hover:text-blue-300">
                            <i class="fas fa-key mr-1"></i> Change Password
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="username">Username</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   value="<?= htmlspecialchars($student['username'] ?? '') ?>"
                                   class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div id="passwordFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2" for="password">New Password</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="confirm_password">Confirm New Password</label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="<?= site_url('admin/students') ?>" class="px-4 py-2 rounded hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 rounded bg-blue-500 hover:bg-blue-600">
                        Update Student
                    </button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Student Not Found</h3>
            <p>The requested student could not be found.</p>
            <a href="<?= site_url('admin/students') ?>" class="mt-4 inline-block text-blue-400 hover:text-blue-300">
                <i class="fas fa-arrow-left mr-1"></i> Back to Students
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle password fields
document.getElementById('showPasswordFields').addEventListener('click', function() {
    const fields = document.getElementById('passwordFields');
    fields.classList.toggle('hidden');
    if (!fields.classList.contains('hidden')) {
        document.getElementById('password').focus();
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    // Password validation (only if changing password)
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if (password || confirm) {
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }
    }

    // Email validation
    const email = document.getElementById('email').value;
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        e.preventDefault();
        alert('Please enter a valid email address!');
        return;
    }

    // Username validation
    const username = document.getElementById('username').value;
    if (username.length < 4) {
        e.preventDefault();
        alert('Username must be at least 4 characters long!');
        return;
    }
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>