<?php 
if (!defined('PREVENT_DIRECT_ACCESS')) exit;
$page = 'Audit Logs';
include 'app/views/admin/_header.php'; 
?>

<style>
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(59, 130, 246, 0.5);
        outline: none;
    }
    .form-input option {
        background: #1a1a2e;
        color: white;
    }
</style>

<!-- Flash Messages -->
<?php if ($flash_success): ?>
    <div class="card border-green-500/30 text-green-200 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= $flash_success ?>
    </div>
<?php endif; ?>

<?php if ($flash_error): ?>
    <div class="card border-red-500/30 text-red-200 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i><?= $flash_error ?>
    </div>
<?php endif; ?>

<!-- Filter Form -->
<div class="card rounded-lg shadow-md p-6 mb-8">
    <h2 class="text-xl font-bold mb-4 text-white">
        <i class="fas fa-filter mr-2 text-blue-300"></i>Filter Logs
    </h2>
    
    <form method="GET" action="<?= site_url('auditlogs') ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Date From</label>
            <input type="date" name="date_from" value="<?= $filters['date_from'] ?>" 
                   class="form-input w-full rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Date To</label>
            <input type="date" name="date_to" value="<?= $filters['date_to'] ?>" 
                   class="form-input w-full rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">User</label>
            <select name="user_id" class="form-input w-full rounded-lg px-4 py-2">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?> 
                        (<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Table</label>
            <select name="table_name" class="form-input w-full rounded-lg px-4 py-2">
                <option value="">All Tables</option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?= $table['table_name'] ?>" <?= $filters['table_name'] == $table['table_name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($table['table_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Action</label>
            <input type="text" name="action_type" value="<?= $filters['action_type'] ?>" 
                   placeholder="Search actions..."
                   class="form-input w-full rounded-lg px-4 py-2">
        </div>
        
        <div class="md:col-span-5 flex space-x-4">
            <button type="submit" class="bg-blue-500/20 border border-blue-500/30 text-blue-300 px-6 py-2 rounded-lg hover:bg-blue-500/30 transition">
                <i class="fas fa-search mr-2"></i>Apply Filters
            </button>
            <a href="<?= site_url('auditlogs') ?>" class="bg-white/10 border border-white/20 text-gray-300 px-6 py-2 rounded-lg hover:bg-white/20 transition">
                <i class="fas fa-times mr-2"></i>Clear Filters
            </a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="card rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-white/10 flex justify-between items-center">
        <h2 class="text-xl font-bold text-white">
            <i class="fas fa-list mr-2 text-blue-300"></i>System Activity Log
        </h2>
        <span class="text-gray-400">Total Logs: <?= number_format($total_pages * 50) ?>+</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Table</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Record ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                <?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">
                                    <?= htmlspecialchars($log['username']) ?>
                                </div>
                                <div class="text-sm text-gray-400">
                                    <?= htmlspecialchars($log['user_first_name'] . ' ' . $log['user_last_name']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                    <?php if ($log['role'] == 'admin'): ?>
                                        bg-red-500/20 text-red-300 border-red-500/30
                                    <?php elseif ($log['role'] == 'teacher'): ?>
                                        bg-orange-500/20 text-orange-300 border-orange-500/30
                                    <?php else: ?>
                                        bg-green-500/20 text-green-300 border-green-500/30
                                    <?php endif; ?>">
                                    <?= ucfirst($log['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                <?= htmlspecialchars($log['action']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <code class="bg-white/5 px-2 py-1 rounded border border-white/10"><?= htmlspecialchars($log['table_name']) ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?= $log['record_id'] ?? '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-inbox text-5xl mb-4 text-gray-600"></i>
                            <p class="text-xl">No logs found</p>
                            <p class="text-sm mt-2">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="p-6 border-t border-white/10 flex justify-center space-x-2">
            <?php for ($i = 1; $i <= min($total_pages, 10); $i++): ?>
                <a href="<?= site_url('auditlogs/index/' . $i . '?' . http_build_query($filters)) ?>" 
                   class="px-4 py-2 rounded-lg transition <?= $i == $current_page ? 'bg-blue-500/30 text-white border border-blue-500/50' : 'bg-white/5 text-gray-300 border border-white/10 hover:bg-white/10' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($total_pages > 10): ?>
                <span class="px-4 py-2 text-gray-400">...</span>
                <a href="<?= site_url('auditlogs/index/' . $total_pages . '?' . http_build_query($filters)) ?>" 
                   class="px-4 py-2 rounded-lg bg-white/5 text-gray-300 border border-white/10 hover:bg-white/10 transition">
                    <?= $total_pages ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/admin/_footer.php'; ?>
