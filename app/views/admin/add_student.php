<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Add New Student</h2>
        <p class="text-sm text-gray-400 mt-1">Create a new student account</p>
    </div>

    <form action="<?= site_url('admin/add_student') ?>" method="POST" class="max-w-2xl">
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2" for="first_name">First Name</label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           required 
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" for="last_name">Last Name</label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           required 
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
                       class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="phone">Phone Number</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
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
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" for="birth_date">Birth Date</label>
                    <input type="date" 
                           id="birth_date" 
                           name="birth_date" 
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <!-- Subject Selection -->
            <div class="border-t border-white/10 pt-6">
                <h3 class="text-lg font-semibold mb-4">Subject Enrollment</h3>
                <div class="space-y-4">
                    <p class="text-sm text-gray-400">You can enroll the student in subjects after creating their account.</p>
                </div>
            </div>

            <!-- Account Credentials -->
            <div class="border-t border-white/10 pt-6">
                <h3 class="text-lg font-semibold mb-4">Account Credentials</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="username">Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required 
                               class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="password">Password</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="confirm_password">Confirm Password</label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
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
                    Create Student
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    // Password validation
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
        return;
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