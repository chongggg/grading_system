<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-bg {
            background: linear-gradient(135deg, #FFFCFB 0%, #E8F9FF 100%);
            min-height: 100vh;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.1);
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);
        }
        .hover-lift { transition: all 0.3s ease; }
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(9, 63, 180, 0.15); }
    </style>
</head>
<body class="min-h-screen admin-bg" style="color: #1e293b;">
    
    <!-- Navigation -->
    <nav class="shadow-lg" style="background: #FFFFFF; border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-bullhorn text-2xl" style="color: #093FB4;"></i>
                    <h1 class="text-2xl font-bold" style="color: #093FB4;">View Announcement</h1>
                </div>
                <a href="<?= site_url('announcements') ?>" class="px-6 py-3 rounded-lg font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border: 1px solid rgba(9, 63, 180, 0.2); color: #093FB4;">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Announcements
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <div class="card rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="p-6" style="background: linear-gradient(135deg, #E8F9FF 0%, #dbeafe 100%); border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold" style="color: #093FB4;"><?= htmlspecialchars($announcement['title']) ?></h1>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium badge-primary">
                        <i class="fas fa-users mr-2"></i>
                        <?= ucfirst($announcement['role_target']) ?>
                    </span>
                </div>
                
                <div class="flex items-center space-x-4 text-sm" style="color: #475569;">
                    <span>
                        <i class="fas fa-user mr-1"></i>
                        <?= htmlspecialchars($announcement['author_first_name'] . ' ' . $announcement['author_last_name']) ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar mr-1"></i>
                        <?= date('F d, Y', strtotime($announcement['created_at'])) ?>
                    </span>
                    <span>
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('h:i A', strtotime($announcement['created_at'])) ?>
                    </span>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-8">
                <div class="prose max-w-none" style="color: #1e293b; line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="p-6 flex space-x-4" style="border-top: 2px solid rgba(9, 63, 180, 0.1); background: #E8F9FF;">
                <a href="<?= site_url('announcements/edit/' . $announcement['id']) ?>" 
                   class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift flex items-center" style="background: linear-gradient(135deg, #eab308 0%, #fbbf24 100%); color: #FFFFFF;">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="<?= site_url('announcements/delete/' . $announcement['id']) ?>" 
                   onclick="return confirm('Are you sure you want to delete this announcement?')"
                   class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift flex items-center" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #FFFFFF;">
                    <i class="fas fa-trash mr-2"></i>Delete
                </a>
            </div>
        </div>
    </div>
</body>
</html>
