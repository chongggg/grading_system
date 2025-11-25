<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - All Users Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
        }

        /* Top Navigation */
        .top-nav {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        /* Super Admin Badge */
        .super-admin-badge {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Cards and Tables */
        .glass-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Search and Controls */
        .search-container {
            position: relative;
            max-width: 400px;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 12px 20px 12px 50px;
            width: 100%;
            transition: all 0.3s ease;
            color: white;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
        }

        .search-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 0 25px 25px 0;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .search-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* Action Buttons */
        .action-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .edit-btn {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }

        .edit-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }

        .delete-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .delete-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .add-student-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-student-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .view-deleted-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-deleted-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        /* Per Page Select */
        .per-page-select {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .per-page-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination .page-item {
            list-style: none;
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pagination .page-link:hover {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            transform: scale(1.1);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* Role Badges */
        .role-admin {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
        }

        .role-teacher {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .role-student {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
    </style>
</head>
<body class="bg-slate-900 text-white">
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="flex justify-between items-center px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-600 to-red-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Super Admin Control Panel</h1>
                        <p class="text-xs text-red-400">All Users Management</p>
                    </div>
                </div>
                <span class="super-admin-badge">
                    <i class="fas fa-crown mr-1"></i>Super Admin
                </span>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="<?= site_url('admin/') ?>" class="text-white hover:text-blue-400 transition-colors flex items-center gap-2">
                    <i class="fas fa-home"></i>
                    <span class="hidden sm:inline">Back to Dashboard</span>
                </a>

                <a href="<?= site_url('auth/profile') ?>" class="flex items-center space-x-2 text-white hover:text-blue-400 transition-colors">
                    <i class="fas fa-user-circle text-2xl"></i>
                    <span class="hidden sm:inline"><?= htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?></span>
                </a>

                <a href="<?= site_url('auth/logout') ?>" class="text-white hover:text-red-400 flex items-center space-x-2 transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8 max-w-7xl">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-users-cog text-5xl text-red-500"></i>
                <div>
                    <h2 class="text-4xl font-bold text-white mb-1">System Users Management</h2>
                    <p class="text-gray-400">Complete access to all registered users across the system (Students, Teachers, Admins)</p>
                </div>
            </div>
            <div class="bg-red-900/20 border border-red-500/30 rounded-lg p-4 mt-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                    <p class="text-red-300 text-sm"><strong>Secret Page:</strong> Super Admin access only. Manage all system users with full privileges.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                <?php if ($is_admin): ?>
                <a href="<?= site_url('students/create') ?>" class="add-student-btn">
                    <i class="fas fa-plus"></i>
                    Add New Account
                </a>
                <a href="<?= site_url('students/deleted') ?>" class="view-deleted-btn">
                    <i class="fas fa-trash"></i>
                    View Deleted
                </a>
                <?php endif; ?>
            </div>
        </div>

            <!-- Search and Controls -->
            <div class="mb-8 flex flex-col lg:flex-row gap-6 items-center justify-between">
                <!-- Search Form -->
                <form method="get" action="<?php echo site_url('students'); ?>" class="search-container w-full lg:w-auto">
                    <input type="hidden" name="per_page" value="<?php echo $per_page ?? 10; ?>">
                    <div class="relative flex">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="search" 
                               id="searchBox"
                               value="<?php echo htmlspecialchars($search ?? ''); ?>"
                               placeholder="Search students..."
                               class="search-input pr-20">
                        <button type="submit" class="search-btn">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Per Page Selector -->
                <form method="get" action="<?php echo site_url('students'); ?>" class="flex items-center space-x-3">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <span class="text-white font-medium whitespace-nowrap">Show:</span>
                    <select name="per_page" 
                            id="per_page" 
                            class="per-page-select"
                            onchange="this.form.submit()">
                        <option value="10" <?= ($per_page ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($per_page ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($per_page ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($per_page ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                    <span class="text-white font-medium whitespace-nowrap">per page</span>
                </form>
            </div>

            <!-- All Users Table -->
            <div class="glass-card overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-red-700 text-white px-8 py-6">
                    <h2 class="text-2xl font-semibold flex items-center gap-2">
                        <i class="fas fa-database"></i>
                        All System Users
                    </h2>
                    <p class="text-red-100 mt-1">Students • Teachers • Admins</p>
                </div>
                
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-800/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">User ID</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">Profile</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">Name</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">Username</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">Email</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">User Type</th>
                                <?php if ($is_admin): ?>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-blue-300 uppercase tracking-wider">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($students as $row): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap text-sm font-mono text-blue-400 font-semibold">
                                    #<?= $row['id'] ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <?php if (isset($row['profile_image']) && $row['profile_image']): ?>
                                        <img src="<?= site_url('public/uploads/' . $row['profile_image']) ?>" 
                                             alt="Profile" 
                                             class="w-12 h-12 rounded-full object-cover border-2 border-blue-400">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                            <?= isset($row['first_name']) ? strtoupper(substr($row['first_name'], 0, 1)) : 'U' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm font-medium text-white">
                                    <?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-300">
                                    <i class="fas fa-user mr-2 text-blue-400"></i><?= htmlspecialchars($row['username'] ?? 'N/A') ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-300">
                                    <?= htmlspecialchars($row['email'] ?? '') ?>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <?php 
                                    $role = $row['role'] ?? 'student';
                                    $role_class = 'role-' . $role;
                                    $role_icon = $role === 'admin' ? 'crown' : ($role === 'teacher' ? 'chalkboard-teacher' : 'user-graduate');
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $role_class ?> shadow-lg">
                                        <i class="fas fa-<?= $role_icon ?> mr-1.5"></i>
                                        <?= ucfirst($role) ?>
                                    </span>
                                </td>
                                <?php if ($is_admin): ?>
                                <td class="px-8 py-6 whitespace-nowrap text-sm space-x-2">
                                    <?php if ($role === 'student'): ?>
                                        <a href="<?= site_url('students/edit/' . $row['id']) ?>" class="action-btn edit-btn">
                                            <i class="fas fa-edit mr-1"></i>
                                            Edit
                                        </a>
                                        <a href="<?= site_url('students/delete/' . $row['id']) ?>" 
                                           onclick="return confirm('Are you sure you want to delete this student?')"
                                           class="action-btn delete-btn">
                                            <i class="fas fa-trash mr-1"></i>
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-xs italic">View Only</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden divide-y divide-white/5">
                    <?php foreach ($students as $row): ?>
                    <div class="p-6 hover:bg-white/5 transition-colors">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <?php if (isset($row['profile_image']) && $row['profile_image']): ?>
                                    <img src="<?= site_url('public/uploads/' . $row['profile_image']) ?>" 
                                         alt="Profile" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-blue-400">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                                        <?= isset($row['first_name']) ? strtoupper(substr($row['first_name'], 0, 1)) : 'U' ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-lg font-bold text-white">
                                        <?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?>
                                    </h3>
                                    <p class="text-blue-400 font-semibold">#<?= $row['id'] ?></p>
                                </div>
                            </div>
                            <?php 
                            $role = $row['role'] ?? 'student';
                            $role_class = 'role-' . $role;
                            $role_icon = $role === 'admin' ? 'crown' : ($role === 'teacher' ? 'chalkboard-teacher' : 'user-graduate');
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $role_class ?> shadow-lg">
                                <i class="fas fa-<?= $role_icon ?> mr-1"></i>
                                <?= ucfirst($role) ?>
                            </span>
                        </div>
                        <div class="mb-4 space-y-2">
                            <p class="text-gray-300 flex items-center text-sm">
                                <i class="fas fa-user mr-2 text-blue-500 w-4"></i>
                                <span class="text-gray-400 mr-2">Username:</span><?= htmlspecialchars($row['username'] ?? 'N/A') ?>
                            </p>
                            <p class="text-gray-300 flex items-center text-sm">
                                <i class="fas fa-envelope mr-2 text-blue-500 w-4"></i>
                                <span class="text-gray-400 mr-2">Email:</span><?= htmlspecialchars($row['email'] ?? '') ?>
                            </p>
                        </div>
                        <?php if ($is_admin && $role === 'student'): ?>
                        <div class="flex space-x-2">
                            <a href="<?= site_url('students/edit/' . $row['id']) ?>" class="action-btn edit-btn flex-1 justify-center">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <a href="<?= site_url('students/delete/' . $row['id']) ?>" 
                               onclick="return confirm('Are you sure you want to delete this student?')" 
                               class="action-btn delete-btn flex-1 justify-center">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination_links) && !empty($pagination_links)): ?>
                <div class="mt-8 flex justify-center">
                    <?php echo $pagination_links; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchBox = document.getElementById('searchBox');
        searchBox.addEventListener('keyup', function(e) {
            const query = this.value;
            fetch('<?php echo site_url("students/index"); ?>?search=' + query)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('table');
                    const currentTable = document.querySelector('table');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                });
        });

        // Smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Form loading state
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.textContent;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
    });
    </script>
</body>
</html>