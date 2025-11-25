<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Announcements</h2>
        <button id="new-ann-btn" class="px-3 py-2 rounded bg-blue-600">New Announcement</button>
    </div>

    <div id="new-ann-form" class="mt-4 hidden p-4 rounded bg-white/3">
        <form method="post" action="<?= site_url('admin/add_announcement') ?>">
            <div class="mb-3">
                <input type="text" name="title" placeholder="Title" class="w-full p-2 rounded bg-transparent border border-white/10" required>
            </div>
            <div class="mb-3">
                <textarea name="content" placeholder="Content" class="w-full p-2 rounded bg-transparent border border-white/10" rows="4" required></textarea>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 rounded bg-green-600">Post</button>
                <button type="button" id="cancel-ann" class="px-4 py-2 rounded bg-gray-700">Cancel</button>
            </div>
        </form>
    </div>

    <div class="mt-6 space-y-4">
        <?php if (!empty($announcements) && is_array($announcements)): ?>
            <?php foreach ($announcements as $a): ?>
                <div class="p-4 rounded card">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold"><?= htmlspecialchars($a['title'] ?? '') ?></h3>
                            <div class="text-sm text-gray-400">By <?= htmlspecialchars($a['author_id'] ?? 'system') ?> • <?= htmlspecialchars($a['created_at'] ?? '') ?></div>
                        </div>
                    </div>
                    <p class="mt-3 text-gray-200"><?= nl2br(htmlspecialchars($a['content'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-gray-400">No announcements yet.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(function(){
        $('#new-ann-btn').on('click', function(){ $('#new-ann-form').slideToggle(); });
        $('#cancel-ann').on('click', function(){ $('#new-ann-form').slideUp(); });
    });
</script>

<?php include __DIR__ . '/_footer.php'; ?>
