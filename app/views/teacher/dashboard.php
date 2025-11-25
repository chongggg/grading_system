<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Dashboard'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Statistics Overview -->
    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">My Subjects</h3>
            <i class="fas fa-book text-blue-400"></i>
        </div>
        <div class="text-3xl font-bold" style="color: #10b981;"><?= isset($total_subjects) ? $total_subjects : 0 ?></div>
        <p class="text-sm mt-2" style="color: #64748b;">Total subjects you're teaching</p>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Students</h3>
            <i class="fas fa-users text-green-400"></i>
        </div>
        <div class="text-3xl font-bold" style="color: #10b981;"><?= isset($total_students) ? $total_students : 0 ?></div>
        <p class="text-sm mt-2" style="color: #64748b;">Total students under your subjects</p>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Pending Grades</h3>
            <i class="fas fa-clock text-yellow-400"></i>
        </div>
        <div class="text-3xl font-bold" style="color: #f59e0b;"><?= isset($pending_grades) ? $pending_grades : 0 ?></div>
        <p class="text-sm mt-2" style="color: #64748b;">Grades pending submission</p>
    </div>
</div>

<!-- Recent Activity -->
<div class="mt-8">
    <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
    <div class="card rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (isset($recent_activities) && !empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-sm"><?= date('M j, Y g:i A', strtotime($activity['date'])) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($activity['subject_name']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($activity['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-sm text-center text-gray-400">No recent activities</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <a href="<?= site_url('teacher/subjects') ?>" class="card rounded-lg p-6 hover:bg-white/5 transition-all">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-500/10 rounded-full p-3">
                <i class="fas fa-book text-blue-400 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold">Manage Subjects</h3>
                <p class="text-sm text-gray-400">View and manage your assigned subjects</p>
            </div>
            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
        </div>
    </a>

    <a href="<?= site_url('auth/profile') ?>" class="card rounded-lg p-6 hover:bg-white/5 transition-all">
        <div class="flex items-center space-x-4">
            <div class="bg-purple-500/10 rounded-full p-3">
                <i class="fas fa-user text-purple-400 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold">My Profile</h3>
                <p class="text-sm text-gray-400">View and update your profile information</p>
            </div>
            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
        </div>
    </a>
</div>

<?php include 'app/views/teacher/_footer.php'; ?>