<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Users</h2>
        <a href="<?= site_url('auth/register') ?>" class="px-3 py-2 rounded bg-blue-600">Create User</a>
    </div>

    <p class="text-gray-300 mt-3">This page represents user management. The project uses <code>Auth_model</code> and <code>Auth</code> controller for user operations (login/register/profile).</p>

    <div class="mt-4">
        <!-- If you want the admin to list users using Auth_model, wire Auth_model into Admin controller or create an admin-specific method in Auth controller that returns a users list. -->
        <?php if (!empty($users) && is_array($users)): ?>
            <table class="min-w-full text-left text-sm mt-4">
                <thead>
                    <tr class="text-gray-400">
                        <th class="px-3 py-2">Username</th>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr class="border-t border-white/5">
                            <td class="px-3 py-2"><?= htmlspecialchars($u['username'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($u['role'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td class="px-3 py-2">
                                <a href="<?= site_url('auth/profile') ?>" class="mr-2 text-blue-300">View</a>
                                <a href="<?= site_url('auth/delete_user/' . ($u['student_id'] ?? '')) ?>" data-confirm="Delete this user?" class="text-red-400">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-gray-400 mt-4">No users data provided. If you want this list powered by <code>Auth_model</code>, add logic in the controller to load users and pass <code>$data['users']</code> to this view.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
