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
        .form-input {
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.2);
            color: #1e293b;
        }
        .form-input:focus {
            background: #FFFFFF;
            border-color: #093FB4;
            outline: none;
            box-shadow: 0 0 0 3px rgba(9, 63, 180, 0.1);
        }
        .radio-card {
            background: #FFFFFF;
            border: 2px solid rgba(9, 63, 180, 0.2);
        }
        .radio-card:hover {
            background: #E8F9FF;
            transform: translateY(-2px);
        }
        .radio-card.checked {
            border-color: #093FB4;
            background: rgba(9, 63, 180, 0.05);
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
                    <h1 class="text-2xl font-bold" style="color: #093FB4;">Edit Announcement</h1>
                </div>
                <a href="<?= site_url('announcements') ?>" class="px-6 py-3 rounded-lg font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border: 1px solid rgba(9, 63, 180, 0.2); color: #093FB4;">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Announcements
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <!-- Validation Errors -->
        <?php if (!empty($validation_errors)): ?>
            <div class="rounded-lg px-4 py-3 mb-4" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                    <div>
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <div><?= $validation_errors ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card rounded-xl shadow-lg p-6">
            <form method="POST" action="<?= site_url('announcements/edit/' . $announcement['id']) ?>">
                
                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #475569;">
                        <i class="fas fa-heading mr-2"></i>Title *
                    </label>
                    <input type="text" name="title" required maxlength="150" 
                           value="<?= htmlspecialchars($announcement['title']) ?>"
                           class="form-input w-full rounded-lg px-4 py-2">
                </div>
                
                <!-- Content -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #475569;">
                        <i class="fas fa-align-left mr-2"></i>Message *
                    </label>
                    <textarea name="content" required rows="10"
                              class="form-input w-full rounded-lg px-4 py-2"><?= htmlspecialchars($announcement['content']) ?></textarea>
                </div>
                
                <!-- Target Audience -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #475569;">
                        <i class="fas fa-users mr-2"></i>Target Audience *
                    </label>
                    
                    <div class="space-y-3">
                        <label class="radio-card flex items-center p-4 rounded-lg cursor-pointer transition">
                            <input type="radio" name="role_target" value="all" <?= $announcement['role_target'] == 'all' ? 'checked' : '' ?> required class="mr-3 w-4 h-4">
                            <div>
                                <div class="font-medium" style="color: #1e293b;">
                                    <i class="fas fa-globe mr-2" style="color: #093FB4;"></i>Everyone (All Users)
                                </div>
                                <div class="text-sm" style="color: #64748b;">Send to all teachers and students</div>
                            </div>
                        </label>
                        
                        <label class="radio-card flex items-center p-4 rounded-lg cursor-pointer transition">
                            <input type="radio" name="role_target" value="teacher" <?= $announcement['role_target'] == 'teacher' ? 'checked' : '' ?> required class="mr-3 w-4 h-4">
                            <div>
                                <div class="font-medium" style="color: #1e293b;">
                                    <i class="fas fa-chalkboard-teacher mr-2" style="color: #ea580c;"></i>Teachers Only
                                </div>
                                <div class="text-sm" style="color: #64748b;">Send to teachers only</div>
                            </div>
                        </label>
                        
                        <label class="radio-card flex items-center p-4 rounded-lg cursor-pointer transition">
                            <input type="radio" name="role_target" value="student" <?= $announcement['role_target'] == 'student' ? 'checked' : '' ?> required class="mr-3 w-4 h-4">
                            <div>
                                <div class="font-medium" style="color: #1e293b;">
                                    <i class="fas fa-user-graduate mr-2" style="color: #16a34a;"></i>Students Only
                                </div>
                                <div class="text-sm" style="color: #64748b;">Send to students only</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Note -->
                <div class="mb-6 rounded-lg p-4" style="background: #fef3c7; border: 1px solid #fcd34d;">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle mt-1 mr-3" style="color: #92400e;"></i>
                        <div class="text-sm" style="color: #92400e;">
                            <strong>Note:</strong> Editing this announcement will NOT resend email notifications. Only new announcements trigger automatic emails.
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                        <i class="fas fa-save mr-2"></i>Update Announcement
                    </button>
                    <a href="<?= site_url('announcements') ?>" 
                       class="px-6 py-3 rounded-lg border font-medium hover-lift flex items-center" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
