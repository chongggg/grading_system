<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page) ? htmlspecialchars($page) . ' - Admin' : 'Admin' ?> - Grading Management</title>
    <!-- Tailwind for quick consistent styling (matches existing pages) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Custom color palette: #FFFCFB (50%), #E8F9FF (30%), #FFD8D8 (10%), #093FB4 (10%) */
        :root {
            --primary-white: #FFFCFB;
            --light-blue: #E8F9FF;
            --light-pink: #FFD8D8;
            --accent-blue: #093FB4;
        }
        
        body {
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            color: #1e293b;
        }
        
        .admin-bg { 
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            min-height: 100vh;
        }
        
        .card { 
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.1);
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 16px rgba(9, 63, 180, 0.15);
            transform: translateY(-4px);
            border-color: rgba(9, 63, 180, 0.2);
        }
        
        nav {
            background: #FFFFFF !important;
            border-bottom: 1px solid rgba(9, 63, 180, 0.1) !important;
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.06);
            z-index: 1000 !important;
        }
        
        .nav-link-active { 
            background: linear-gradient(135deg, #E8F9FF 0%, #d0f0ff 100%);
            border-bottom: 3px solid #093FB4 !important;
            color: #093FB4 !important;
            font-weight: 600;
        }
        
        .dropdown { 
            position: relative; 
            z-index: 1001;
        }
        
        .dropdown:hover .dropdown-menu { 
            display: block; 
        }
        
        .dropdown-menu { 
            display: none; 
            position: absolute;
            z-index: 10000 !important;
            box-shadow: 0 8px 24px rgba(9, 63, 180, 0.15);
        }
        
        main { 
            position: relative; 
            z-index: 1; 
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%);
            color: #FFFCFB;
            border: none;
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0652d4 0%, #093FB4 100%);
            box-shadow: 0 4px 12px rgba(9, 63, 180, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #FFD8D8 0%, #ffb8b8 100%);
            color: #991b1b;
            border: 1px solid rgba(153, 27, 27, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #ffb8b8 0%, #FFD8D8 100%);
            box-shadow: 0 4px 12px rgba(255, 216, 216, 0.4);
        }
        
        .text-primary { color: #093FB4; }
        .text-secondary { color: #64748b; }
        .text-accent { color: #0ea5e9; }
        
        .icon-primary { color: #093FB4; }
        .icon-secondary { color: #0ea5e9; }
        .icon-accent { color: #FFD8D8; }
        
        /* Override default text colors for visibility */
        table { color: #1e293b; }
        th { color: #475569 !important; }
        td { color: #1e293b !important; }
        h1, h2, h3, h4, h5, h6 { color: #093FB4 !important; }
        p { color: #475569; }
        label { color: #1e293b; }
        input, select, textarea { 
            color: #1e293b !important; 
            background: #FFFFFF !important;
            border: 1px solid rgba(9, 63, 180, 0.2) !important;
        }
        .text-gray-400 { color: #64748b !important; }
        .text-gray-300 { color: #475569 !important; }
        .text-gray-200 { color: #1e293b !important; }
        .text-blue-300, .text-blue-400 { color: #093FB4 !important; }
        .text-green-300, .text-green-400 { color: #16a34a !important; }
        .text-red-400 { color: #dc2626 !important; }
        .text-yellow-400 { color: #ca8a04 !important; }
        .bg-gray-800 { background: #E8F9FF !important; color: #093FB4 !important; }
        .border-white\/5 { border-color: rgba(9, 63, 180, 0.1) !important; }
        .bg-blue-600 { background: #093FB4 !important; color: #FFFFFF !important; }
        .bg-green-600 { background: #16a34a !important; color: #FFFFFF !important; }
        .bg-red-600 { background: #dc2626 !important; color: #FFFFFF !important; }
        
        .badge-primary {
            background: linear-gradient(135deg, #E8F9FF 0%, #d0f0ff 100%);
            color: #093FB4;
            border: 1px solid rgba(9, 63, 180, 0.2);
            font-weight: 600;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 1px solid rgba(22, 101, 52, 0.2);
            font-weight: 600;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #E8F9FF 100%);
            border-left: 4px solid #093FB4;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen admin-bg">
    <nav class="w-full" style="position: relative; z-index: 1000;">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Top Row: Logo and User Info -->
            <div class="flex items-center justify-between py-4 border-b" style="border-color: rgba(9, 63, 180, 0.1);">
                <div class="text-xl font-bold tracking-wide flex items-center" style="color: #093FB4;">
                    <i class="fas fa-graduation-cap mr-2" style="color: #0ea5e9;"></i>
                    <span>Grading Management System</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm font-medium" style="color: #475569;">
                        <i class="fas fa-user-circle mr-1" style="color: #093FB4;"></i>
                        <?= isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'Admin' ?>
                    </div>
                    <a href="<?= site_url('auth/logout') ?>" class="px-4 py-2 rounded-lg text-sm transition btn-danger font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Bottom Row: Navigation Links -->
            <div class="hidden md:flex items-center text-sm space-x-1 py-3">
                <a href="<?= site_url('admin') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Dashboard') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Dashboard') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?= site_url('reports') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Reports') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Reports') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-chart-line mr-2"></i>Reports
                </a>
                <a href="<?= site_url('announcements') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Announcements') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Announcements') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-bullhorn mr-2"></i>Announcements
                </a>
                <a href="<?= site_url('admin/audit_logs') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Audit Logs') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Audit Logs') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-history mr-2"></i>Audit Logs
                </a>
                
                <!-- Data Management Dropdown -->
                <div class="relative dropdown">
                    <button class="px-4 py-2 rounded-lg transition font-medium hover-lift flex items-center" style="color: #475569;">
                        <i class="fas fa-database mr-2"></i>Data Management<i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div class="dropdown-menu absolute top-full left-0 mt-2 rounded-xl shadow-lg py-2 min-w-[200px]" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.1); z-index: 10000 !important;">
                        <a href="<?= site_url('admin/teachers') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-chalkboard-teacher mr-3" style="color: #093FB4;"></i>Teachers
                        </a>
                        <a href="<?= site_url('admin/students') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-user-graduate mr-3" style="color: #093FB4;"></i>Students
                        </a>
                        <a href="<?= site_url('admin/subjects') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-book mr-3" style="color: #093FB4;"></i>Subjects
                        </a>
                        <a href="<?= site_url('admin/sections') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-chalkboard mr-3" style="color: #093FB4;"></i>Sections
                        </a>
                        <a href="<?= site_url('admin/subject_bundles') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-layer-group mr-3" style="color: #093FB4;"></i>Subject Sets
                        </a>
                        <div class="border-t my-1" style="border-color: rgba(9, 63, 180, 0.1);"></div>
                        <a href="<?= site_url('pendingaccounts') ?>" class="block px-4 py-3 transition hover:bg-blue-50 font-medium" style="color: #1e293b;">
                            <i class="fas fa-user-clock mr-3" style="color: #093FB4;"></i>Pending Accounts
                        </a>
                    </div>
                </div>
                
                <a href="<?= site_url('admin/review_grades') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift" style="color: #475569;">
                    <i class="fas fa-clipboard-check mr-2"></i>Review Grades
                </a>
                <a href="<?= site_url('admin/messages') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift relative" style="color: #475569;">
                    <i class="fas fa-envelope mr-2"></i>Messages
                    <?php if (isset($unread_count) && $unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold" style="background: #ef4444; color: #FFFFFF;"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= site_url('auth/profile') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift" style="color: #475569;">
                    <i class="fas fa-user-cog mr-2"></i>Profile
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Flash messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 rounded-lg border" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5;">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 rounded-lg border" style="background: #dcfce7; color: #166534; border-color: #86efac;">
                <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <h1 class="text-3xl font-bold" style="color: #093FB4;"><?= isset($page) ? htmlspecialchars($page) : 'Admin' ?></h1>
            <p class="text-sm mt-2 font-medium" style="color: #64748b;">Administrative controls and reports</p>
        </div>

        <div id="admin-content">
<!-- admin content starts -->
