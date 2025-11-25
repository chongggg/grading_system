<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include 'app/views/student/_header.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold mb-2" style="color: #093FB4;">My Subjects</h1>
    <p style="color: #1e293b;">View all your enrolled subjects and sections</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Total Subjects</p>
                <h3 class="text-3xl font-bold" style="color: #10b981;"><?= count($subjects) ?></h3>
            </div>
            <div class="text-4xl" style="color: #10b981;">
                <i class="fas fa-book-open"></i>
            </div>
        </div>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Current Semester</p>
                <h3 class="text-xl font-semibold" style="color: #093FB4;">
                    <?php
                        $current_semester = 'N/A';
                        if (!empty($subjects)) {
                            $first_subject = $subjects[0];
                            $current_semester = isset($first_subject['semester']) ? $first_subject['semester'] : 'N/A';
                        }
                        echo htmlspecialchars($current_semester);
                    ?>
                </h3>
            </div>
            <div class="text-4xl" style="color: #3b82f6;">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm mb-1" style="color: #1e293b;">Grade Level</p>
                <h3 class="text-xl font-semibold" style="color: #093FB4;">
                    <?php
                        $grade_level = 'N/A';
                        if (!empty($subjects)) {
                            $first_subject = $subjects[0];
                            $grade_level = isset($first_subject['grade_level']) ? $first_subject['grade_level'] : 'N/A';
                        }
                        echo htmlspecialchars($grade_level);
                    ?>
                </h3>
            </div>
            <div class="text-4xl" style="color: #a855f7;">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</div>

<!-- Subjects List -->
<?php if (!empty($subjects)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($subjects as $subject): ?>
            <div class="card rounded-lg overflow-hidden hover:border-green-500/30 transition-all duration-300">
                <div class="p-6">
                    <!-- Subject Code Badge -->
                    <div class="mb-4">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <?= htmlspecialchars($subject['subject_code']) ?>
                        </span>
                    </div>

                    <!-- Subject Name -->
                    <h3 class="text-xl font-bold mb-3" style="color: #093FB4;">
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </h3>

                    <!-- Description -->
                    <?php if (!empty($subject['description'])): ?>
                        <p class="text-sm mb-4 line-clamp-2" style="color: #1e293b;">
                            <?= htmlspecialchars($subject['description']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Subject Details -->
                    <div class="space-y-2 mb-4">
                        <?php if (!empty($subject['teacher_first_name'])): ?>
                            <div class="flex items-center text-sm" style="color: #1e293b;">
                                <i class="fas fa-chalkboard-teacher w-5" style="color: #3b82f6;"></i>
                                <span>
                                    <?= htmlspecialchars($subject['teacher_first_name'] . ' ' . $subject['teacher_last_name']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($subject['grade_level'])): ?>
                            <div class="flex items-center text-sm" style="color: #1e293b;">
                                <i class="fas fa-layer-group w-5" style="color: #a855f7;"></i>
                                <span>Grade <?= htmlspecialchars($subject['grade_level']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($subject['semester'])): ?>
                            <div class="flex items-center text-sm" style="color: #1e293b;">
                                <i class="fas fa-calendar w-5" style="color: #f59e0b;"></i>
                                <span><?= htmlspecialchars($subject['semester']) ?> Semester</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($subject['created_at'])): ?>
                            <div class="flex items-center text-sm" style="color: #1e293b;">
                                <i class="fas fa-clock w-5" style="color: #10b981;"></i>
                                <span>Enrolled: <?= date('M d, Y', strtotime($subject['created_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- View Grades Button -->
                    <a href="<?= site_url('student/grades') ?>" 
                       class="block w-full text-center px-4 py-2 rounded-md transition-all duration-300 border" 
                       style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3);">
                        <i class="fas fa-chart-line mr-2"></i>View Grades
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="card rounded-lg p-12 text-center">
        <div class="text-6xl mb-4" style="color: #cbd5e1;">
            <i class="fas fa-book-open"></i>
        </div>
        <h3 class="text-xl font-semibold mb-2" style="color: #093FB4;">No Subjects Enrolled</h3>
        <p class="mb-6" style="color: #1e293b;">
            You are not currently enrolled in any subjects. Please contact your administrator for assistance.
        </p>
        <a href="<?= site_url('student/dashboard') ?>" 
           class="inline-block px-6 py-2 rounded-md transition-all duration-300 border" 
           style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3);">
            <i class="fas fa-home mr-2"></i>Back to Dashboard
        </a>
    </div>
<?php endif; ?>

<?php include 'app/views/student/_footer.php'; ?>
