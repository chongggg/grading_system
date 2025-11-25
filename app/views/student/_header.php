<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page) ? htmlspecialchars($page) . ' - Student' : 'Student' ?> - Grading Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        /* Unified light theme matching admin */
        :root {
            --primary-white: #FFFCFB;
            --light-blue: #E8F9FF;
            --light-pink: #FFD8D8;
            --accent-blue: #093FB4;
            --student-accent: #3b82f6;
        }
        
        body {
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            color: #1e293b;
        }
        
        .student-bg { 
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
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-bottom: 3px solid #3b82f6 !important;
            color: #1d4ed8 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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
        
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #E8F9FF 100%);
            border-left: 4px solid #3b82f6;
        }
        
        .dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu { display: none; }
        
        /* Notification bell styles */
        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            background: rgba(17, 24, 39, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .notification-dropdown.show {
            display: block;
        }
        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.2s;
        }
        .notification-item:hover {
            background: rgba(255,255,255,0.05);
        }
        .notification-item.unread {
            background: rgba(34,197,94,0.08);
            border-left: 3px solid #22c55e;
        }
        .notification-item .time {
            font-size: 11px;
            color: #64748b;
        }
        .shake-bell {
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }
        
        .notification-dropdown {
            background: #FFFFFF !important;
            border: 1px solid rgba(9, 63, 180, 0.2) !important;
            box-shadow: 0 10px 25px rgba(9, 63, 180, 0.2) !important;
        }
        .notification-item {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(9, 63, 180, 0.1) !important;
        }
        .notification-item:hover {
            background: rgba(59, 130, 246, 0.05) !important;
        }
        .notification-item.unread {
            background: rgba(59, 130, 246, 0.08) !important;
            border-left: 3px solid #3b82f6 !important;
        }
    </style>
</head>
<body class="min-h-screen student-bg">
    <nav class="w-full" style="position: relative; z-index: 1000;">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Top Row: Logo, User Info, Notifications -->
            <div class="flex items-center justify-between py-4 border-b" style="border-color: rgba(9, 63, 180, 0.1);">
                <div class="text-xl font-bold tracking-wide flex items-center" style="color: #3b82f6;">
                    <i class="fas fa-graduation-cap mr-2" style="color: #0ea5e9;"></i>
                    <span>Student Portal</span>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div class="relative notification-bell" id="notification-bell">
                        <i class="fas fa-bell text-2xl hover-lift" style="color: #64748b; cursor: pointer;"></i>
                        <?php if (isset($unread_count) && $unread_count > 0): ?>
                            <span class="notification-badge" id="notification-badge"><?= $unread_count ?></span>
                        <?php endif; ?>
                        
                        <!-- Notification Dropdown -->
                        <div class="notification-dropdown" id="notification-dropdown">
                            <div class="flex items-center justify-between p-3 border-b" style="border-color: rgba(9, 63, 180, 0.1);">
                                <h3 class="font-semibold text-sm" style="color: #093FB4;">Notifications</h3>
                                <button id="mark-all-read" class="text-xs hover-lift" style="color: #3b82f6;">Mark all read</button>
                            </div>
                            <div id="notification-list">
                                <div class="text-center py-8 text-sm" style="color: #64748b;">
                                    <i class="fas fa-spinner fa-spin mb-2"></i>
                                    <p>Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-sm font-medium" style="color: #475569;">
                        <i class="fas fa-user-circle mr-1" style="color: #3b82f6;"></i>
                        <?= isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Student' ?>
                    </div>
                    <a href="<?= site_url('auth/logout') ?>" class="px-4 py-2 rounded-lg text-sm transition btn-danger font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Bottom Row: Navigation Links -->
            <div class="hidden md:flex items-center text-sm space-x-1 py-3">
                <a href="<?= site_url('student/dashboard') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='Dashboard') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='Dashboard') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?= site_url('student/profile') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='My Profile') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='My Profile') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-user mr-2"></i>My Profile
                </a>
                <a href="<?= site_url('student/subjects') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='My Subjects') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='My Subjects') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-book mr-2"></i>My Subjects
                </a>
                <a href="<?= site_url('student/grades') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && $page==='My Grades') ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && $page==='My Grades') ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-chart-bar mr-2"></i>My Grades
                </a>
                <a href="<?= site_url('student/messages') ?>" class="px-4 py-2 rounded-lg transition font-medium hover-lift <?= (isset($page) && ($page==='Messages' || $page==='Conversation')) ? 'nav-link-active' : '' ?>" style="<?= !(isset($page) && ($page==='Messages' || $page==='Conversation')) ? 'color: #475569;' : '' ?>">
                    <i class="fas fa-envelope mr-2"></i>Messages
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-6">
