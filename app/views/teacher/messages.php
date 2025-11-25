<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Messages'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<div class="grid md:grid-cols-3 gap-6">
    <!-- Thread List -->
    <div class="md:col-span-1">
        <div class="card rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Conversations</h3>
                <button type="button" id="newMessageBtn" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition-colors">
                    <i class="fas fa-plus mr-1"></i>New
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" id="searchThreads" placeholder="Search conversations..." class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded text-sm focus:outline-none focus:border-blue-500">
            </div>

            <!-- Thread List -->
            <div class="space-y-2 max-h-[600px] overflow-y-auto">
                <?php if (!empty($threads)): ?>
                    <?php foreach ($threads as $thread): ?>
                        <a href="<?= site_url('teacher/view_thread/' . $thread['thread_id']) ?>" 
                           class="block p-3 rounded <?= $thread['unread_count'] > 0 ? 'bg-blue-500/10 border border-blue-500/30' : 'bg-white/5 hover:bg-white/10' ?> transition">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm truncate"><?= htmlspecialchars($thread['other_party_name']) ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($thread['other_party_role']) ?></div>
                                </div>
                                <?php if ($thread['unread_count'] > 0): ?>
                                    <span class="flex-shrink-0 ml-2 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                        <?= $thread['unread_count'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm font-medium text-gray-300 mb-1 truncate"><?= htmlspecialchars($thread['subject']) ?></div>
                            <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars(substr($thread['last_message'], 0, 60)) ?><?= strlen($thread['last_message']) > 60 ? '...' : '' ?></div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?= date('M d, Y g:i A', strtotime($thread['last_message_at'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>No conversations yet</p>
                        <p class="text-sm">Start a new conversation!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Welcome/Info Panel -->
    <div class="md:col-span-2">
        <div class="card rounded-lg p-8 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-blue-500/20 flex items-center justify-center mb-4">
                <i class="fas fa-envelope text-blue-400 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-semibold mb-2">Your Messages</h3>
            <p class="text-gray-400 mb-6">Select a conversation from the list to view messages, or start a new conversation with administrators.</p>
            
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="p-4 bg-white/5 rounded-lg">
                    <div class="text-3xl font-bold text-blue-400"><?= count($threads) ?></div>
                    <div class="text-sm text-gray-400 mt-1">Total Threads</div>
                </div>
                <div class="p-4 bg-white/5 rounded-lg">
                    <div class="text-3xl font-bold text-green-400"><?= $unread_count ?></div>
                    <div class="text-sm text-gray-400 mt-1">Unread</div>
                </div>
                <div class="p-4 bg-white/5 rounded-lg">
                    <div class="text-3xl font-bold text-purple-400"><?= count($messageable_users) ?></div>
                    <div class="text-sm text-gray-400 mt-1">Contacts</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div id="newMessageModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 border border-white/10">
        <div class="flex items-center justify-between p-6 border-b border-white/10">
            <h3 class="text-xl font-semibold">New Message</h3>
            <button type="button" id="closeModalBtn" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="newMessageForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">To:</label>
                <select name="recipient_id" required class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded focus:outline-none focus:border-blue-500">
                    <option value="">Select recipient...</option>
                    <?php 
                    $current_type = '';
                    foreach ($messageable_users as $user): 
                        $user_type = isset($user['user_type']) ? $user['user_type'] : ucfirst($user['role']);
                        if ($user_type !== $current_type):
                            if ($current_type !== ''): ?>
                                </optgroup>
                            <?php endif;
                            $current_type = $user_type; ?>
                            <optgroup label="<?= htmlspecialchars($user_type) ?>">
                        <?php endif; ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                    <?php endforeach; ?>
                    <?php if ($current_type !== ''): ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Subject:</label>
                <input type="text" name="subject" required maxlength="255" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Message:</label>
                <textarea name="message" required rows="6" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded focus:outline-none focus:border-blue-500 resize-none"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
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
            url: '<?= site_url("teacher/send_message") ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Message sent successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to send message'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
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

<?php include 'app/views/teacher/_footer.php'; ?>
