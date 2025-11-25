<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Conversation'; ?>
<?php include 'app/views/student/_header.php'; ?>

<!-- Breadcrumb -->
<nav class="mb-4 text-sm">
    <ol class="list-none p-0 inline-flex text-gray-400">
        <li class="flex items-center">
            <a href="<?= site_url('student/messages') ?>" class="hover:text-white">Messages</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-200"><?= isset($thread['subject']) ? htmlspecialchars($thread['subject']) : 'Conversation' ?></li>
    </ol>
</nav>

<?php if (isset($thread) && isset($messages)): ?>
    <div class="card rounded-lg overflow-hidden">
        <!-- Thread Header -->
        <div class="p-6 border-b border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold mb-1"><?= htmlspecialchars($thread['subject']) ?></h2>
                    <p class="text-gray-400 text-sm">
                        Started on <?= date('F d, Y g:i A', strtotime($thread['created_at'])) ?>
                    </p>
                </div>
                <a href="<?= site_url('student/delete_thread/' . $thread['thread_id']) ?>" 
                   onclick="return confirm('Are you sure you want to delete this entire conversation?')"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Thread
                </a>
            </div>
        </div>

        <!-- Messages -->
        <div class="p-6 space-y-4 max-h-[600px] overflow-y-auto" id="messagesContainer">
            <?php 
            $current_user_id = $_SESSION['user_id'];
            foreach ($messages as $msg): 
                $is_sender = $msg['sender_id'] == $current_user_id;
            ?>
                <div class="flex <?= $is_sender ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[70%]">
                        <div class="flex items-center mb-1 <?= $is_sender ? 'justify-end' : '' ?>">
                            <span class="text-sm font-medium"><?= htmlspecialchars($msg['sender_name']) ?></span>
                            <span class="text-xs text-gray-500 ml-2"><?= htmlspecialchars($msg['sender_role']) ?></span>
                        </div>
                        <div class="p-4 rounded-lg <?= $is_sender ? 'bg-blue-600' : 'bg-white/5' ?>">
                            <p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                        </div>
                        <div class="text-xs text-gray-500 mt-1 <?= $is_sender ? 'text-right' : '' ?>">
                            <?= date('M d, Y g:i A', strtotime($msg['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply Form -->
        <div class="p-6 border-t border-white/10 bg-white/5">
            <form id="replyForm" class="space-y-3">
                <input type="hidden" name="thread_id" value="<?= $thread['thread_id'] ?>">
                <input type="hidden" name="parent_id" value="<?= $messages[count($messages) - 1]['id'] ?>">
                <input type="hidden" name="recipient_id" value="<?= $is_sender ? $thread['recipient_id'] : $thread['sender_id'] ?>">
                <input type="hidden" name="subject" value="<?= htmlspecialchars($thread['subject']) ?>">
                
                <div>
                    <label class="block text-sm font-medium mb-2">Reply:</label>
                    <textarea name="message" required rows="4" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded focus:outline-none focus:border-blue-500 resize-none" placeholder="Type your reply..."></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                        <i class="fas fa-reply mr-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card rounded-lg p-6 text-center">
        <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
        <p class="text-gray-300">Conversation not found or you don't have access.</p>
        <a href="<?= site_url('student/messages') ?>" class="mt-4 inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            Back to Messages
        </a>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Scroll to bottom of messages
    const container = $('#messagesContainer');
    if (container.length) {
        container.scrollTop(container[0].scrollHeight);
    }
    
    // Handle reply submission
    $('#replyForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending...');
        
        $.ajax({
            url: '<?= site_url("student/send_message") ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to send reply'));
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Failed to send reply. Please try again.');
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php include 'app/views/student/_footer.php'; ?>
