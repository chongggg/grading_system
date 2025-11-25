<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="p-6 rounded-xl card stat-card hover-lift">
        <h3 class="text-lg font-semibold flex items-center" style="color: #1e293b;">
            <i class="fas fa-user-graduate mr-3" style="color: #093FB4;"></i>
            Total Students
        </h3>
        <div class="text-4xl font-bold mt-4" style="color: #093FB4;"><?= isset($total_students) ? number_format($total_students) : '—' ?></div>
    </div>
    <div class="p-6 rounded-xl card stat-card hover-lift">
        <h3 class="text-lg font-semibold flex items-center" style="color: #1e293b;">
            <i class="fas fa-chalkboard-teacher mr-3" style="color: #093FB4;"></i>
            Total Teachers
        </h3>
        <div class="text-4xl font-bold mt-4" style="color: #093FB4;"><?= isset($total_teachers) ? number_format($total_teachers) : '—' ?></div>
    </div>
    <div class="p-6 rounded-xl card stat-card hover-lift">
        <h3 class="text-lg font-semibold flex items-center" style="color: #1e293b;">
            <i class="fas fa-book mr-3" style="color: #093FB4;"></i>
            Total Subjects
        </h3>
        <div class="text-4xl font-bold mt-4" style="color: #093FB4;"><?= isset($total_subjects) ? number_format($total_subjects) : '—' ?></div>
    </div>
</div>

<!-- Recent Activity Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    <!-- Recent Grades -->
    <div class="p-6 rounded-xl card hover-lift">
        <h2 class="text-xl font-bold mb-4 flex items-center" style="color: #093FB4;">
            <i class="fas fa-chart-line mr-3" style="color: #0ea5e9;"></i>
            Recent Grades
        </h2>
        <?php if (!empty($recent_grades) && is_array($recent_grades)): ?>
            <div class="space-y-3">
                <?php foreach ($recent_grades as $grade): ?>
                    <div class="flex justify-between items-start border-b pb-3" style="border-color: rgba(9, 63, 180, 0.1);">
                        <div>
                            <div class="font-semibold" style="color: #1e293b;"><?= htmlspecialchars($grade['student_name'] ?? '—') ?></div>
                            <div class="text-sm" style="color: #64748b;"><?= htmlspecialchars($grade['subject_name'] ?? '—') ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold" style="color: #093FB4;"><?= number_format(($grade['final_grade'] ?? 0), 2) ?></div>
                            <div class="text-xs" style="color: #64748b;"><?= htmlspecialchars($grade['remarks'] ?? '—') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="color: #64748b;">No recent grades.</div>
        <?php endif; ?>
    </div>

    <!-- Recent Announcements -->
    <div class="p-6 rounded-xl card hover-lift">
        <h2 class="text-xl font-bold mb-4 flex items-center" style="color: #093FB4;">
            <i class="fas fa-bullhorn mr-3" style="color: #0ea5e9;"></i>
            Recent Announcements
        </h2>
        <?php if (!empty($recent_announcements) && is_array($recent_announcements)): ?>
            <div class="space-y-4">
                <?php foreach ($recent_announcements as $ann): ?>
                    <div class="border-b pb-4" style="border-color: rgba(9, 63, 180, 0.1);">
                        <h3 class="font-semibold" style="color: #1e293b;"><?= htmlspecialchars($ann['title'] ?? '') ?></h3>
                        <p class="text-sm mt-1" style="color: #475569;"><?= nl2br(htmlspecialchars($ann['content'] ?? '')) ?></p>
                        <div class="text-xs mt-2" style="color: #64748b;">
                            By <?= htmlspecialchars($ann['author_name'] ?? '—') ?> • 
                            <?= date('M j, Y g:i A', strtotime($ann['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="color: #64748b;">No recent announcements.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity Logs -->
<div class="mt-8 p-6 rounded-xl card hover-lift">
    <h2 class="text-xl font-bold mb-4 flex items-center" style="color: #093FB4;">
        <i class="fas fa-history mr-3" style="color: #0ea5e9;"></i>
        Recent Activity
    </h2>
    <?php if (!empty($recent_logs) && is_array($recent_logs)): ?>
        <div class="space-y-3">
            <?php foreach ($recent_logs as $log): ?>
                <div class="flex justify-between items-center border-b pb-3" style="border-color: rgba(9, 63, 180, 0.1);">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #E8F9FF 0%, #d0f0ff 100%);">
                            <i class="fas fa-user-circle" style="color: #093FB4;"></i>
                        </div>
                        <div>
                            <div class="font-semibold" style="color: #1e293b;"><?= htmlspecialchars($log['user_name'] ?? '—') ?></div>
                            <div class="text-sm" style="color: #64748b;">
                                <?= htmlspecialchars($log['action'] ?? '') ?> on 
                                <?= htmlspecialchars($log['table_name'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs" style="color: #64748b;">
                        <?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="color: #64748b;">No recent activity.</div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
