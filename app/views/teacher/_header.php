<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page) ? htmlspecialchars($page) . ' - Teacher' : 'Teacher' ?> - Grading Management</title>
    <!-- Tailwind for quick consistent styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Unified light theme matching admin */
        :root {
            --primary-white: #FFFCFB;
            --light-blue: #E8F9FF;
            --light-pink: #FFD8D8;
            --accent-blue: #093FB4;
            --teacher-accent: #10b981;
        }
        
        body {
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            color: #1e293b;
        }
        
        .teacher-bg { 
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            min-height: 100vh;
        }
        
        .card { 
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.1);
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);
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
        }
        
        .nav-link-active { 
            background: linear-gradient(135deg, #d1fae5 0%, #bbf7d0 100%);
            border-bottom: 3px solid #10b981 !important;
            color: #059669 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #FFD8D8 0%, #ffb8b8 100%);
            color: #991b1b;
            border: 1px solid rgba(153, 27, 27, 0.2);
        }
        
        h1, h2, h3, h4, h5, h6 { color: #093FB4 !important; }
        table { color: #1e293b; }
        th { color: #475569 !important; }
        td { color: #1e293b !important; }
        input, select, textarea { 
            color: #1e293b !important; 
            background: #FFFFFF !important;
            border: 1px solid rgba(9, 63, 180, 0.2) !important;
        }
        
        .grade-input {
            background: #FFFFFF !important;
            border: 1px solid rgba(9, 63, 180, 0.2) !important;
            transition: all 0.2s;
        }
        .grade-input:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #E8F9FF 100%);
            border-left: 4px solid #10b981;
        }
        
        .loading-overlay {
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="min-h-screen teacher-bg">
    <nav class="w-full" style="position: relative; z-index: 1000;">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Top Row: Logo and User Info -->
            <div class="flex items-center justify-between py-4 border-b" style="border-color: rgba(9, 63, 180, 0.1);">
                <div class="text-xl font-bold tracking-wide flex items-center" style="color: #10b981;">
                    <i class="fas fa-chalkboard-teacher mr-2" style="color: #0ea5e9;"></i>
                    <span>Teacher Portal</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm font-medium" style="color: #475569;">
                        <i class="fas fa-user-circle mr-1" style="color: #10b981;"></i>
                        <?= isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'Teacher' ?>
                    </div>
                    <a href="<?= site_url('auth/logout') ?>" class="px-4 py-2 rounded-lg text-sm transition btn-danger font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Bottom Row: Navigation Links -->
            <div class="hidden md:flex items-center text-sm space-x-1 py-3">
                <a href="<?= site_url('teacher') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Dashboard') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Dashboard') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?= site_url('teacher/subjects') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && ($page==='Subjects' || $page==='Subject Details')) ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && ($page==='Subjects' || $page==='Subject Details')) ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-book mr-2"></i>My Subjects
                </a>
                <a href="<?= site_url('teacher/grades') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Grades') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Grades') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-graduation-cap mr-2"></i>Grades
                </a>
                <a href="<?= site_url('teacher/messages') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift relative <?= (isset($page) && $page==='Messages') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Messages') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-envelope mr-2"></i>Messages
                    <?php if (isset($unread_count) && $unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold" style="background: #ef4444; color: #FFFFFF;"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= site_url('auth/profile') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Profile') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Profile') ? 'color: #475569;' : '' ?>">
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
            <h1 class="text-3xl font-bold" style="color: #10b981;"><?= isset($page) ? htmlspecialchars($page) : 'Teacher Portal' ?></h1>
            <p class="text-sm mt-2 font-medium" style="color: #64748b;">Manage your subjects and student grades</p>
        </div>

        <div id="teacher-content">
<!-- teacher content starts -->