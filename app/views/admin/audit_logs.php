<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2" style="color: #093FB4;">System Activity Logs</h2>
            <p style="color: #64748b;">Track all system activities and user actions</p>
        </div>
        <?php if (isset($total_logs) && $total_logs > 0): ?>
            <div class="text-right">
                <span class="inline-block px-4 py-2 rounded-lg font-semibold" style="background: #e0f2fe; color: #0369a1;">
                    <i class="fas fa-database mr-2"></i><?= number_format($total_logs) ?> Total Logs
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="p-6 rounded-xl card">
    <?php if (!empty($logs) && is_array($logs)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1); background: #f8fafc;">
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">User</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Role</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Action</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Table</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Record ID</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);">
                            <td class="px-4 py-3" style="color: #1e293b;">
                                <div class="font-medium"><?= htmlspecialchars($l['username'] ?? 'User #' . $l['user_id']) ?></div>
                                <?php if (!empty($l['first_name']) && !empty($l['last_name'])): ?>
                                    <div class="text-xs" style="color: #64748b;"><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php 
                                $role = $l['role'] ?? 'unknown';
                                $roleStyle = '';
                                if ($role === 'admin') {
                                    $roleStyle = 'background: #dbeafe; color: #1e40af;';
                                } elseif ($role === 'teacher') {
                                    $roleStyle = 'background: #fef3c7; color: #92400e;';
                                } elseif ($role === 'student') {
                                    $roleStyle = 'background: #d1fae5; color: #065f46;';
                                } else {
                                    $roleStyle = 'background: #f3f4f6; color: #374151;';
                                }
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold" style="<?= $roleStyle ?>"><?= htmlspecialchars(ucfirst($role)) ?></span>
                            </td>
                            <td class="px-4 py-3" style="color: #1e293b; max-width: 400px;">
                                <div class="break-words"><?= htmlspecialchars($l['action'] ?? '-') ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-1 rounded text-xs" style="background: #f1f5f9; color: #475569;"><?= htmlspecialchars($l['table_name'] ?? '-') ?></code>
                            </td>
                            <td class="px-4 py-3 text-center" style="color: #475569;"><?= htmlspecialchars($l['record_id'] ?? '-') ?></td>
                            <td class="px-4 py-3" style="color: #64748b;">
                                <?= date('M j, Y', strtotime($l['timestamp'])) ?><br>
                                <span class="text-xs"><?= date('g:i A', strtotime($l['timestamp'])) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($pagination_links) && !empty($pagination_links)): ?>
            <div class="mt-6">
                <div class="text-sm text-center mb-4" style="color: #64748b;">
                    Showing <?= (($current_page - 1) * $per_page) + 1 ?> to <?= min($current_page * $per_page, $total_logs) ?> of <?= number_format($total_logs) ?> logs
                </div>
                <style>
                    .pagination-nav a {
                        background: #f1f5f9;
                        color: #475569;
                        border: 1px solid #e2e8f0;
                    }
                    .pagination-nav a:hover {
                        background: linear-gradient(135deg, #093FB4, #0c4fd9);
                        color: white;
                        border-color: #093FB4;
                    }
                    .pagination-nav .active {
                        background: linear-gradient(135deg, #093FB4, #0c4fd9) !important;
                        color: white !important;
                        border-color: #093FB4 !important;
                    }
                </style>
                <div class="pagination-nav">
                    <?= $pagination_links ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-clipboard-list text-6xl mb-4" style="color: #93c5fd;"></i>
            <p style="color: #64748b;">No audit logs found.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
