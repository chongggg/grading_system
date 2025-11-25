<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
        }

        .form-input {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 12px 32px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding: 12px 32px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .super-admin-badge {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body class="bg-slate-900 text-white">
    <!-- Top Navigation -->
    <nav class="bg-slate-800/95 backdrop-blur-lg border-b border-white/10 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-600 to-red-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Super Admin Control Panel</h1>
                        <p class="text-xs text-red-400">Edit User</p>
                    </div>
                </div>
                <span class="super-admin-badge">
                    <i class="fas fa-crown mr-1"></i>Super Admin
                </span>
            </div>
            
            <a href="<?= site_url('superadmin') ?>" class="text-white hover:text-blue-400 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Users</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8 max-w-3xl">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('error')): ?>
            <div class="mb-6 p-4 bg-red-900/20 border border-red-500/30 rounded-lg text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i><?= $this->session->flashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="glass-card p-8">
            <div class="flex items-center gap-3 mb-6">
                <i class="fas fa-user-edit text-4xl text-blue-500"></i>
                <div>
                    <h2 class="text-3xl font-bold text-white">Edit User</h2>
                    <p class="text-gray-400">Modify user information and credentials</p>
                </div>
            </div>

            <form method="POST" action="<?= site_url('superadmin/edit/' . $user['auth_id']) ?>" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">
                            <i class="fas fa-user mr-2 text-blue-400"></i>First Name
                        </label>
                        <input type="text" 
                               name="first_name" 
                               value="<?= htmlspecialchars($user['first_name']) ?>"
                               class="form-input" 
                               required>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">
                            <i class="fas fa-user mr-2 text-blue-400"></i>Last Name
                        </label>
                        <input type="text" 
                               name="last_name" 
                               value="<?= htmlspecialchars($user['last_name']) ?>"
                               class="form-input" 
                               required>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-400"></i>Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>"
                           class="form-input" 
                           required>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        <i class="fas fa-id-badge mr-2 text-blue-400"></i>Username
                    </label>
                    <input type="text" 
                           name="username" 
                           value="<?= htmlspecialchars($user['username']) ?>"
                           class="form-input" 
                           required>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        <i class="fas fa-user-tag mr-2 text-blue-400"></i>User Role
                    </label>
                    <select name="role" class="form-input" required>
                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <!-- Teacher-specific fields -->
                <?php if ($user['teacher_id']): ?>
                <div class="bg-orange-900/10 border border-orange-500/20 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-orange-400 mb-4">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">
                                <i class="fas fa-phone mr-2 text-blue-400"></i>Contact Number
                            </label>
                            <input type="text" 
                                   name="contact_number" 
                                   value="<?= htmlspecialchars($user['contact_number']) ?>"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">
                                <i class="fas fa-graduation-cap mr-2 text-blue-400"></i>Specialization
                            </label>
                            <input type="text" 
                                   name="specialization" 
                                   value="<?= htmlspecialchars($user['specialization']) ?>"
                                   class="form-input">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Password (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-400"></i>New Password (Optional)
                    </label>
                    <input type="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Leave blank to keep current password">
                    <p class="text-xs text-gray-400 mt-1">Only fill this if you want to change the password</p>
                </div>

                <!-- Status Info -->
                <?php if ($user['is_deleted']): ?>
                <div class="bg-red-900/20 border border-red-500/30 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                        <p class="text-red-300 text-sm">
                            <strong>Notice:</strong> This user is currently archived. 
                            <a href="<?= site_url('superadmin/restore/' . $user['auth_id']) ?>" 
                               class="underline hover:text-red-200"
                               onclick="return confirm('Restore this user?')">
                                Click here to restore
                            </a>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="btn-primary flex-1">
                        <i class="fas fa-save mr-2"></i>
                        Save Changes
                    </button>
                    <a href="<?= site_url('superadmin') ?>" class="btn-secondary flex-1 text-center">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        });
    });
    </script>
</body>
</html>
