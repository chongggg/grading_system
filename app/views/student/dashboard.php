<?php $this->call->view('student/_header'); ?>

<!-- Dashboard Content -->
<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2" style="color: #093FB4;">Welcome back, <?= htmlspecialchars($student['first_name']) ?>! 👋</h1>
    <p style="color: #1e293b;">Here's an overview of your academic progress</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card rounded-lg p-6 hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Total Subjects</p>
                <h3 class="text-3xl font-bold" style="color: #093FB4;"><?= $total_subjects ?></h3>
            </div>
            <div class="bg-blue-500/20 p-4 rounded-full">
                <i class="fas fa-book text-3xl text-blue-400"></i>
            </div>
        </div>
    </div>

    <div class="card rounded-lg p-6 hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Reviewed Grades</p>
                <h3 class="text-3xl font-bold text-green-400"><?= $reviewed_grades ?></h3>
            </div>
            <div class="bg-green-500/20 p-4 rounded-full">
                <i class="fas fa-check-circle text-3xl text-green-400"></i>
            </div>
        </div>
    </div>

    <div class="card rounded-lg p-6 hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Pending Grades</p>
                <h3 class="text-3xl font-bold text-yellow-400"><?= $pending_grades ?></h3>
            </div>
            <div class="bg-yellow-500/20 p-4 rounded-full">
                <i class="fas fa-clock text-3xl text-yellow-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="<?= site_url('student/grades') ?>" class="card rounded-lg p-6 hover:bg-white/5 transition-all duration-300 group">
        <div class="flex items-center">
            <div class="bg-green-500/20 p-4 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-bar text-2xl text-green-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-1" style="color: #093FB4;">View My Grades</h3>
                <p class="text-sm" style="color: #1e293b;">Check your academic performance</p>
            </div>
        </div>
    </a>

    <a href="<?= site_url('student/profile') ?>" class="card rounded-lg p-6 hover:bg-white/5 transition-all duration-300 group">
        <div class="flex items-center">
            <div class="bg-blue-500/20 p-4 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-edit text-2xl text-blue-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-1" style="color: #093FB4;">Update Profile</h3>
                <p class="text-sm" style="color: #1e293b;">Manage your personal information</p>
            </div>
        </div>
    </a>
</div>

<!-- Announcements -->
<div class="card rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold flex items-center" style="color: #093FB4;">
            <i class="fas fa-bullhorn mr-2 text-yellow-400"></i>
            Latest Announcements
        </h2>
    </div>

    <?php if (!empty($announcements)): ?>
        <div class="space-y-4">
            <?php foreach ($announcements as $announcement): ?>
                <div class="rounded-lg p-4 transition-all duration-300" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1);">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-lg font-semibold" style="color: #093FB4;"><?= htmlspecialchars($announcement['title']) ?></h3>
                        <span class="text-xs" style="color: #64748b;"><?= date('M d, Y', strtotime($announcement['created_at'])) ?></span>
                    </div>
                    <p class="text-sm leading-relaxed" style="color: #1e293b;"><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                    <?php if ($announcement['role_target'] === 'student'): ?>
                        <span class="inline-block mt-2 text-xs bg-green-500/20 text-green-400 px-2 py-1 rounded">For Students</span>
                    <?php elseif ($announcement['role_target'] === 'all'): ?>
                        <span class="inline-block mt-2 text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded">For Everyone</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8" style="color: #64748b;">
            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
            <p>No announcements at this time</p>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Animate statistics cards with number count-up
    $('.card h3').each(function() {
        const $this = $(this);
        const text = $this.text();
        if (!isNaN(text)) {
            const target = parseInt(text);
            animateNumber($this, target, 1000);
        }
    });
});
</script>

<?php $this->call->view('student/_footer'); ?>
