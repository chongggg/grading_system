<?php 
if (!defined('PREVENT_DIRECT_ACCESS')) exit;
$page = 'Announcements';
include 'app/views/admin/_header.php'; 
?>

<!-- Flash Messages -->
<?php if ($flash_success): ?>
    <div class="card px-4 py-3 rounded-lg mb-4" style="background: #dcfce7; color: #166534; border: 1px solid #86efac;">
        <i class="fas fa-check-circle mr-2"></i><?= $flash_success ?>
    </div>
<?php endif; ?>
        
        <?php if ($flash_error): ?>
            <div class="card px-4 py-3 rounded-lg mb-4" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                <i class="fas fa-exclamation-circle mr-2"></i><?= $flash_error ?>
            </div>
        <?php endif; ?>

        <?php if ($flash_email_success): ?>
            <div class="card px-4 py-3 rounded-lg mb-4" style="background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;">
                <i class="fas fa-envelope mr-2"></i><?= $flash_email_success ?>
            </div>
        <?php endif; ?>

        <?php if ($flash_email_warning): ?>
            <div class="card px-4 py-3 rounded-lg mb-4" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= $flash_email_warning ?>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="mb-6">
            <a href="<?= site_url('announcements/create') ?>" 
               class="px-6 py-3 rounded-lg transition inline-flex items-center font-medium shadow-md hover-lift" 
               style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                <i class="fas fa-plus mr-2"></i>Create New Announcement
            </a>
        </div>

        <!-- Announcements List -->
        <div class="card rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b" style="border-color: rgba(9, 63, 180, 0.1);">
                <h2 class="text-xl font-bold" style="color: #093FB4;">
                    <i class="fas fa-list mr-2"></i>All Announcements
                </h2>
            </div>
            
            <?php if (!empty($announcements)): ?>
                <div class="divide-y" style="border-color: rgba(9, 63, 180, 0.1);">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="p-6 hover:bg-blue-50 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-bold" style="color: #093FB4;">
                                            <?= htmlspecialchars($announcement['title']) ?>
                                        </h3>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            <?php if ($announcement['role_target'] == 'all'): ?>
                                                badge-primary
                                            <?php elseif ($announcement['role_target'] == 'teacher'): ?>
                                                badge-primary
                                            <?php else: ?>
                                                badge-success
                                            <?php endif; ?>">
                                            <i class="fas fa-users mr-1"></i>
                                            <?= ucfirst($announcement['role_target']) ?>
                                        </span>
                                    </div>
                                    
                                    <p class="mb-3 line-clamp-2" style="color: #475569;">
                                        <?= htmlspecialchars(substr($announcement['content'], 0, 200)) ?>
                                        <?= strlen($announcement['content']) > 200 ? '...' : '' ?>
                                    </p>
                                    
                                    <div class="flex items-center text-sm space-x-4" style="color: #64748b;">
                                        <span>
                                            <i class="fas fa-user mr-1"></i>
                                            <?= htmlspecialchars($announcement['author_first_name'] . ' ' . $announcement['author_last_name']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= date('M d, Y h:i A', strtotime($announcement['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2 ml-4">
                                    <a href="<?= site_url('announcements/view/' . $announcement['id']) ?>" 
                                       class="px-4 py-2 rounded-lg transition shadow-sm hover-lift" 
                                       style="background: linear-gradient(135deg, #E8F9FF 0%, #d0f0ff 100%); color: #093FB4; border: 1px solid rgba(9, 63, 180, 0.2);">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('announcements/edit/' . $announcement['id']) ?>" 
                                       class="px-4 py-2 rounded-lg transition shadow-sm hover-lift" 
                                       style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('announcements/delete/' . $announcement['id']) ?>" 
                                       onclick="return confirm('Are you sure you want to delete this announcement?')"
                                       class="px-4 py-2 rounded-lg transition shadow-sm hover-lift" 
                                       style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="p-6 border-t flex justify-center space-x-2" style="border-color: rgba(9, 63, 180, 0.1);">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= site_url('announcements/index/' . $i) ?>" 
                               class="px-4 py-2 rounded-lg transition shadow-sm font-medium" 
                               style="<?= $i == $current_page ? 'background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;' : 'background: #FFFFFF; color: #093FB4; border: 1px solid rgba(9, 63, 180, 0.2);' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="p-12 text-center" style="color: #64748b;">
                    <i class="fas fa-bullhorn text-6xl mb-4" style="color: #93c5fd;"></i>
                    <p class="text-xl mb-2 font-semibold" style="color: #1e293b;">No announcements yet</p>
                    <p class="mb-4">Create your first announcement to notify users</p>
                    <a href="<?= site_url('announcements/create') ?>" 
                       class="px-6 py-3 rounded-lg transition inline-flex items-center font-medium shadow-md hover-lift" 
                       style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                        <i class="fas fa-plus mr-2"></i>Create Announcement
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'app/views/admin/_footer.php'; ?>
