<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <h2 class="text-xl font-semibold mb-4">Edit Teacher</h2>

    <?php $t = $teacher ?? null; ?>
        <form method="POST" action="<?= site_url('admin/edit_teacher/' . ($t['id'] ?? '')) ?>" class="space-y-4">
            <div>
                <label class="block text-sm text-gray-300">First name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($t['first_name'] ?? '') ?>" class="w-full form-input p-2 rounded" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300">Last name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($t['last_name'] ?? '') ?>" class="w-full form-input p-2 rounded" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($t['email'] ?? '') ?>" class="w-full form-input p-2 rounded" required>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
                <a href="<?= site_url('admin/teachers') ?>" class="ml-3 text-gray-300">Cancel</a>
            </div>
        </form>
</div>

<?php include __DIR__ . '/_footer.php'; ?>