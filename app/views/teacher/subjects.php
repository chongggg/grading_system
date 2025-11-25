<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Subjects'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<!-- Statistics Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Total Subjects</h3>
            <i class="fas fa-book text-blue-400"></i>
        </div>
        <div class="mt-2">
            <div class="text-3xl font-bold" style="color: #10b981;">
                <?= isset($subjects) ? count($subjects) : 0 ?>
            </div>
            <p class="text-sm mt-1" style="color: #64748b;">Subjects assigned to you</p>
        </div>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Total Students</h3>
            <i class="fas fa-users text-green-400"></i>
        </div>
        <div class="mt-2">
            <div class="text-3xl font-bold" style="color: #10b981;">
                <?php
                    $total = 0;
                    if (isset($subjects)) {
                        foreach ($subjects as $s) {
                            $total += isset($s->students) ? count($s->students) : 0;
                        }
                    }
                    echo $total;
                ?>
            </div>
            <p class="text-sm mt-1" style="color: #64748b;">Students enrolled in your subjects</p>
        </div>
    </div>

    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Pending Grades</h3>
            <i class="fas fa-clock text-yellow-400"></i>
        </div>
        <div class="mt-2">
            <div class="text-3xl font-bold text-yellow-400">
                <?php
                    $pending = 0;
                    if (isset($subjects)) {
                        foreach ($subjects as $s) {
                            if (isset($s->students)) {
                                foreach ($s->students as $st) {
                                    if (!isset($st->final_grade)) $pending++;
                                }
                            }
                        }
                    }
                    echo $pending;
                ?>
            </div>
            <p class="text-sm mt-1" style="color: #1e293b;">Students needing grades</p>
        </div>
    </div>
</div>

<!-- Subjects List -->
<div class="grid grid-cols-1 gap-6">
    <?php if (isset($subjects) && !empty($subjects)): ?>
        <?php foreach ($subjects as $subject): ?>
            <div class="card rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold">
                                <?= htmlspecialchars($subject->display_name ?? $subject->name) ?>
                            </h3>
                            <div class="text-sm mt-1" style="color: #1e293b;">
                                <span class="mr-3">Code: <?= htmlspecialchars($subject->code) ?></span>
                                <?php if (isset($subject->section_name)): ?>
                                    <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-400 border border-purple-500/30 mr-3">
                                        <i class="fas fa-door-open mr-1"></i><?= htmlspecialchars($subject->section_name) ?>
                                    </span>
                                <?php endif; ?>
                                <?php
                                    $studentCount = isset($subject->students) ? count($subject->students) : 0;
                                    echo "<span>{$studentCount} student" . ($studentCount !== 1 ? 's' : '') . "</span>";
                                ?>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm" style="color: #1e293b;">
                                School Year: <?= isset($subject->school_year) ? htmlspecialchars($subject->school_year) : date('Y') . '-' . (date('Y')+1) ?>
                            </div>
                            <?php if (isset($subject->semester)): ?>
                                <div class="text-sm text-right mt-1">
                                    <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400">
                                        <?= htmlspecialchars($subject->semester) ?> Semester
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($subject->description)): ?>
                        <p class="text-sm mb-4" style="color: #1e293b;"><?= nl2br(htmlspecialchars($subject->description)) ?></p>
                    <?php endif; ?>

                    <!-- Grade Status Summary -->
                    <?php if (isset($subject->grade_status_summary)): ?>
                        <div class="mb-4 p-4 rounded bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-300">
                                    <i class="fas fa-clipboard-check mr-2"></i>Grade Submission Status
                                </h4>
                            </div>
                            <div class="grid grid-cols-4 gap-3">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-400">
                                        <?= $subject->grade_status_summary['total'] ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Total Grades</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-400">
                                        <?= $subject->grade_status_summary['Draft'] ?>
                                    </div>
                                    <div class="text-xs mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full border" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-pencil-alt mr-1 text-xs"></i>Draft
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-400">
                                        <?= $subject->grade_status_summary['Submitted'] ?>
                                    </div>
                                    <div class="text-xs mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                            <i class="fas fa-clock mr-1 text-xs"></i>Pending Review
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-400">
                                        <?= $subject->grade_status_summary['Reviewed'] ?>
                                    </div>
                                    <div class="text-xs mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                            <i class="fas fa-check-circle mr-1 text-xs"></i>Approved
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="px-4 py-3 rounded bg-white/5">
                            <div class="text-sm text-gray-400">Total Students</div>
                            <div class="text-xl font-semibold mt-1">
                                <?= isset($subject->students) ? count($subject->students) : 0 ?>
                            </div>
                        </div>
                        <div class="px-4 py-3 rounded bg-white/5">
                            <div class="text-sm text-gray-400">Passing</div>
                            <div class="text-xl font-semibold text-green-400 mt-1">
                                <?php
                                    $passing = 0;
                                    if (isset($subject->students)) {
                                        foreach ($subject->students as $st) {
                                            if (isset($st->final_grade) && $st->final_grade >= 75) $passing++;
                                        }
                                    }
                                    echo $passing;
                                ?>
                            </div>
                        </div>
                        <div class="px-4 py-3 rounded bg-white/5">
                            <div class="text-sm" style="color: #1e293b;">Needs Grading</div>
                            <div class="text-xl font-semibold text-yellow-400 mt-1">
                                <?php
                                    $pending = 0;
                                    if (isset($subject->students)) {
                                        foreach ($subject->students as $st) {
                                            if (!isset($st->final_grade)) $pending++;
                                        }
                                    }
                                    echo $pending;
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Links -->
                    <div class="flex items-center justify-end space-x-4 text-sm">
                        <?php 
                        $section_param = isset($subject->section_id) ? '?section_id=' . $subject->section_id : '';
                        ?>
                        <a href="<?= site_url('teacher/subjects/' . $subject->id . '/students' . $section_param) ?>" class="transition font-medium" style="color: #10b981;">
                            <i class="fas fa-users mr-1"></i> Class List
                        </a>
                        <a href="<?= site_url('teacher/subjects/' . $subject->id . '/grades' . $section_param) ?>" class="transition font-medium" style="color: #10b981;">
                            <i class="fas fa-star mr-1"></i> Manage Grades
                        </a>
                        <a href="<?= site_url('teacher/import_grades/' . $subject->id . $section_param) ?>" class="transition font-medium" style="color: #10b981;">
                            <i class="fas fa-file-excel mr-1"></i> Import Grades
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card rounded-lg p-6 text-center">
            <i class="fas fa-info-circle text-blue-400 text-4xl mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No Subjects Found</h3>
            <p style="color: #1e293b;">You haven't been assigned to any subjects yet. Please contact the administrator.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/teacher/_footer.php'; ?>