<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Messages'; ?>
<?php include 'app/views/admin/_header.php'; ?>

<div class="grid md:grid-cols-3 gap-6">
    <!-- Thread List -->
    <div class="md:col-span-1">
        <div class="card rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold" style="color: #093FB4;">Conversations</h3>
                <button type="button" id="newMessageBtn" class="px-4 py-2 rounded-lg text-sm font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                    <i class="fas fa-plus mr-1"></i>New
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" id="searchThreads" placeholder="Search conversations..." class="w-full px-3 py-2 rounded-lg border text-sm" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
            </div>

            <!-- Thread List -->
            <div class="space-y-2 max-h-[600px] overflow-y-auto">
                <?php if (!empty($threads)): ?>
                    <?php foreach ($threads as $thread): ?>
                        <a href="<?= site_url('admin/view_thread/' . $thread['thread_id']) ?>" 
                           class="block p-3 rounded-lg hover-lift transition" style="<?= $thread['unread_count'] > 0 ? 'background: rgba(9, 63, 180, 0.1); border: 1px solid rgba(9, 63, 180, 0.3);' : 'background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.1);' ?>">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm truncate" style="color: #1e293b;"><?= htmlspecialchars($thread['other_party_name']) ?></div>
                                    <div class="text-xs" style="color: #64748b;"><?= htmlspecialchars($thread['other_party_role']) ?></div>
                                </div>
                                <?php if ($thread['unread_count'] > 0): ?>
                                    <span class="flex-shrink-0 ml-2 text-xs rounded-full w-6 h-6 flex items-center justify-center font-semibold" style="background: #093FB4; color: #FFFFFF;">
                                        <?= $thread['unread_count'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm font-medium mb-1 truncate" style="color: #475569;"><?= htmlspecialchars($thread['subject']) ?></div>
                            <div class="text-xs truncate" style="color: #64748b;"><?= htmlspecialchars(substr($thread['last_message'], 0, 60)) ?><?= strlen($thread['last_message']) > 60 ? '...' : '' ?></div>
                            <div class="text-xs mt-1" style="color: #94a3b8;">
                                <?= date('M d, Y g:i A', strtotime($thread['last_message_at'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-6xl mb-4" style="color: #93c5fd;"></i>
                        <p style="color: #475569; font-weight: 600;">No conversations yet</p>
                        <p class="text-sm" style="color: #64748b;">Start a new conversation!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Welcome/Info Panel -->
    <div class="md:col-span-2">
        <div class="card rounded-xl p-8 text-center">
            <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4" style="background: rgba(9, 63, 180, 0.1);">
                <i class="fas fa-envelope text-3xl" style="color: #093FB4;"></i>
            </div>
            <h3 class="text-2xl font-bold mb-2" style="color: #093FB4;">Your Messages</h3>
            <p class="mb-6" style="color: #64748b;">Select a conversation from the list to view messages, or start a new conversation with teachers.</p>
            
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="p-4 rounded-lg hover-lift" style="background: #E8F9FF;">
                    <div class="text-3xl font-bold" style="color: #093FB4;"><?= count($threads) ?></div>
                    <div class="text-sm mt-1" style="color: #64748b;">Total Threads</div>
                </div>
                <div class="p-4 rounded-lg hover-lift" style="background: #E8F9FF;">
                    <div class="text-3xl font-bold" style="color: #16a34a;"><?= $unread_count ?></div>
                    <div class="text-sm mt-1" style="color: #64748b;">Unread</div>
                </div>
                <div class="p-4 rounded-lg hover-lift" style="background: #E8F9FF;">
                    <div class="text-3xl font-bold" style="color: #7c3aed;"><?= count($messageable_users) ?></div>
                    <div class="text-sm mt-1" style="color: #64748b;">Contacts</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div id="newMessageModal" class="hidden fixed inset-0 flex items-center justify-center z-50" style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);">
    <div class="rounded-xl shadow-2xl w-full max-w-2xl mx-4" style="background: #FFFFFF;">
        <div class="flex items-center justify-between p-6" style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
            <h3 class="text-xl font-bold" style="color: #093FB4;">New Message</h3>
            <button type="button" id="closeModalBtn" class="hover:underline" style="color: #64748b;">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="newMessageForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #475569;">To:</label>
                <select name="recipient_id" required class="w-full px-3 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <option value="">Select recipient...</option>
                    <?php foreach ($messageable_users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #475569;">Subject:</label>
                <input type="text" name="subject" required maxlength="255" class="w-full px-3 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #475569;">Message:</label>
                <textarea name="message" required rows="6" class="w-full px-3 py-2 rounded-lg border resize-none" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancelBtn" class="px-6 py-3 rounded-lg border font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // New message modal
    $('#newMessageBtn').on('click', function() {
        $('#newMessageModal').removeClass('hidden');
    });
    
    $('#closeModalBtn, #cancelBtn').on('click', function() {
        $('#newMessageModal').addClass('hidden');
        $('#newMessageForm')[0].reset();
    });
    
    // Send new message
    $('#newMessageForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= site_url("admin/send_message") ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Message sent successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to send message'));
                }
            },
            error: function() {
                alert('Failed to send message. Please try again.');
            }
        });
    });
    
    // Search threads
    $('#searchThreads').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.space-y-2 > a').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>

<?php include 'app/views/admin/_footer.php'; ?>
