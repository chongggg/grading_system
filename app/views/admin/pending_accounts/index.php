<?php 
if (!defined('PREVENT_DIRECT_ACCESS')) exit;
include 'app/views/admin/_header.php'; 
?>

<!-- Pending Accounts Management -->
<div class="card rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">
            <i class="fas fa-user-clock mr-2 text-yellow-400"></i>Pending Student Accounts
        </h2>
        <span class="bg-yellow-500/20 text-yellow-300 px-4 py-2 rounded-lg border border-yellow-500/30">
            <?= count($pending_users) ?> Pending
        </span>
    </div>

    <?php if (empty($pending_users)): ?>
        <!-- No Pending Accounts -->
        <div class="text-center py-12">
            <i class="fas fa-check-circle text-6xl text-green-400 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">All Caught Up!</h3>
            <p class="text-gray-400">There are no pending account approvals at this time.</p>
        </div>
    <?php else: ?>
        <!-- Pending Accounts Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php foreach ($pending_users as $user): ?>
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-300"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <code class="bg-white/5 px-2 py-1 rounded border border-white/10">
                                <?= htmlspecialchars($user['username']) ?>
                            </code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="<?= site_url('pendingaccounts/approve/' . $user['id']) ?>" 
                                   onclick="return confirm('Approve this student account?\n\nStudent: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>\nUsername: <?= htmlspecialchars($user['username']) ?>\n\nAn approval email will be sent to: <?= htmlspecialchars($user['email']) ?>');"
                                   class="px-4 py-2 bg-green-500/20 border border-green-500/30 text-green-300 rounded-lg hover:bg-green-500/30 transition text-sm">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </a>
                                <a href="<?= site_url('pendingaccounts/reject/' . $user['id']) ?>" 
                                   onclick="return confirm('Reject and delete this student account?\n\nStudent: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>\nUsername: <?= htmlspecialchars($user['username']) ?>\n\nThis action cannot be undone. A rejection email will be sent.');"
                                   class="px-4 py-2 bg-red-500/20 border border-red-500/30 text-red-300 rounded-lg hover:bg-red-500/30 transition text-sm">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Info Box -->
        <div class="mt-6 card border-blue-500/30 p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-400 text-xl mt-1 mr-3"></i>
                <div class="text-sm text-gray-300">
                    <p class="font-semibold text-blue-300 mb-2">Actions:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong class="text-green-300">Approve:</strong> Changes role from 'user' to 'student' and sends approval email</li>
                        <li><strong class="text-red-300">Reject:</strong> Deletes the account and sends rejection email</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/admin/_footer.php'; ?>
