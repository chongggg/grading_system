<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Subject Details'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<!-- Breadcrumb -->
<nav class="mb-4 text-sm">
    <ol class="list-none p-0 inline-flex text-gray-400">
        <li class="flex items-center">
            <a href="<?= site_url('teacher/subjects') ?>" class="hover:text-white">My Subjects</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-200"><?= isset($subject) ? htmlspecialchars($subject->name) : 'Subject Details' ?></li>
    </ol>
</nav>

<?php if (isset($subject) && $subject): ?>
    <div class="card rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-semibold"><?= htmlspecialchars($subject->name) ?></h2>
                <div class="mt-1 text-sm text-gray-400">
                    <span class="mr-4">Code: <?= htmlspecialchars($subject->code ?? '') ?></span>
                    <?php if (isset($subject->section)): ?>
                        <span class="mr-4">Section: <?= htmlspecialchars($subject->section) ?></span>
                    <?php endif; ?>
                    <?php if (isset($subject->semester)): ?>
                        <span>Semester: <?= htmlspecialchars($subject->semester) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-400 mb-2">
                    School Year: <?= isset($subject->school_year) ? htmlspecialchars($subject->school_year) : date('Y') . '-' . (date('Y')+1) ?>
                </div>
                <div class="text-sm">
                    <?php
                        $total = isset($subject->students) ? count($subject->students) : 0;
                        echo "<span class=\"text-blue-400\">{$total}</span> student" . ($total !== 1 ? 's' : '');
                    ?>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between mt-4 mb-4">
            <div class="flex items-center space-x-4">
                <!-- Section Filter -->
                <?php if (!empty($sections)): ?>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-300">
                        <i class="fas fa-filter mr-1"></i>Filter by Section:
                    </label>
                    <select id="section_filter" class="px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="all" <?= empty($selected_section) || $selected_section === 'all' ? 'selected' : '' ?>>All Sections</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= htmlspecialchars($section['id']) ?>" <?= isset($selected_section) && $selected_section == $section['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section['section_name']) ?> (<?= htmlspecialchars($section['student_count']) ?> students)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <label class="flex items-center space-x-2 text-sm text-gray-300">
                    <input type="checkbox" id="auto_compute" />
                    <span>Automatic grade computation</span>
                </label>
                <label class="flex items-center space-x-2 text-sm text-gray-300">
                    <input type="checkbox" id="notify_student" />
                    <span>Notify student on update</span>
                </label>
            </div>
            <div class="flex items-center space-x-3">
                <?php 
                $section_param = !empty($selected_section) && $selected_section !== 'all' ? '?section_id=' . $selected_section : '';
                ?>
                <a href="<?= site_url('teacher/import_grades/' . $subject->id . $section_param) ?>" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>Import from Excel
                </a>
                <button type="button" id="submit_grades_btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Submit for Review
                </button>
            </div>
        </div>

        <?php if (isset($subject->description)): ?>
            <div class="mb-4 p-4 rounded bg-white/5">
                <h3 class="text-sm font-semibold mb-1">Description</h3>
                <p class="text-gray-300 text-sm"><?= nl2br(htmlspecialchars($subject->description)) ?></p>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-center">Prelim</th>
                        <th class="px-4 py-3 text-center">Midterm</th>
                        <th class="px-4 py-3 text-center">Finals</th>
                        <th class="px-4 py-3 text-center">Final Grade</th>
                        <th class="px-4 py-3 text-center">Remarks</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (!empty($subject->students)): ?>
                        <?php foreach ($subject->students as $st): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3"><?= htmlspecialchars($st->last_name . ', ' . $st->first_name) ?></td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="0" max="100" step="0.01" class="grade-input border rounded px-2 py-1 w-20 text-center" data-student="<?= htmlspecialchars($st->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>" data-period="1" value="<?= htmlspecialchars($st->grades[1] ?? '') ?>">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="0" max="100" step="0.01" class="grade-input border rounded px-2 py-1 w-20 text-center" data-student="<?= htmlspecialchars($st->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>" data-period="2" value="<?= htmlspecialchars($st->grades[2] ?? '') ?>">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="0" max="100" step="0.01" class="grade-input border rounded px-2 py-1 w-20 text-center" data-student="<?= htmlspecialchars($st->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>" data-period="3" value="<?= htmlspecialchars($st->grades[3] ?? '') ?>">
                                </td>
                                <td class="px-4 py-3 text-center font-semibold final-grade" data-student="<?= htmlspecialchars($st->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>"><?php if (isset($st->final_grade)) { echo round($st->final_grade,2); } else { echo '-'; } ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="remarks-badge inline-block px-3 py-1 rounded-full text-xs font-semibold" data-student="<?= htmlspecialchars($st->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>">
                                        <?php 
                                        $remarks = $st->remarks ?? 'Incomplete';
                                        $badgeClass = '';
                                        if ($remarks === 'Passed') {
                                            $badgeClass = 'bg-green-500/20 text-green-400';
                                        } elseif ($remarks === 'Failed') {
                                            $badgeClass = 'bg-red-500/20 text-red-400';
                                        } else {
                                            $badgeClass = 'bg-yellow-500/20 text-yellow-400';
                                        }
                                        echo "<span class=\"{$badgeClass}\">" . htmlspecialchars($remarks) . "</span>";
                                        ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php 
                                    $status = $st->status ?? 'Draft';
                                    $statusBadge = '';
                                    $statusIcon = '';
                                    if ($status === 'Draft') {
                                        $statusBadge = 'bg-gray-500/20 text-gray-400 border border-gray-500/30';
                                        $statusIcon = '<i class="fas fa-pencil-alt mr-1"></i>';
                                    } elseif ($status === 'Submitted') {
                                        $statusBadge = 'bg-orange-500/20 text-orange-400 border border-orange-500/30';
                                        $statusIcon = '<i class="fas fa-clock mr-1"></i>';
                                    } elseif ($status === 'Reviewed') {
                                        $statusBadge = 'bg-green-500/20 text-green-400 border border-green-500/30';
                                        $statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
                                    }
                                    echo "<span class=\"status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {$statusBadge}\" data-student=\"" . htmlspecialchars($st->id) . "\" data-subject=\"" . htmlspecialchars($subject->id) . "\">";
                                    echo $statusIcon . htmlspecialchars($status);
                                    echo "</span>";
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">No students enrolled.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card rounded-lg p-6 text-center text-gray-400">Subject not found or you are not assigned to this subject.</div>
<?php endif; ?>

<?php include 'app/views/teacher/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restore preferences from localStorage
    const autoCompute = localStorage.getItem('teacher_auto_compute') === '1';
    const notifyStudent = localStorage.getItem('teacher_notify_student') === '1';
    document.getElementById('auto_compute').checked = autoCompute;
    document.getElementById('notify_student').checked = notifyStudent;

    document.getElementById('auto_compute').addEventListener('change', function(e) {
        localStorage.setItem('teacher_auto_compute', e.target.checked ? '1' : '0');
    });
    document.getElementById('notify_student').addEventListener('change', function(e) {
        localStorage.setItem('teacher_notify_student', e.target.checked ? '1' : '0');
    });

    // Attach handlers to grade inputs
    document.querySelectorAll('.grade-input').forEach(function(input) {
        let timeout = null;
        input.addEventListener('input', function(e) {
            // debounce to avoid excessive requests
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(function() {
                const studentId = input.dataset.student;
                const subjectId = input.dataset.subject;
                const period = input.dataset.period;
                const grade = input.value;
                const notify = document.getElementById('notify_student').checked ? 1 : 0;

                // Basic validation
                if (grade === '' || isNaN(parseFloat(grade))) return;

                fetch('<?= site_url("teacher/update_grade") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `student_id=${encodeURIComponent(studentId)}&subject_id=${encodeURIComponent(subjectId)}&period=${encodeURIComponent(period)}&grade=${encodeURIComponent(grade)}&notify=${notify}`
                }).then(r => r.json()).then(function(res) {
                    if (!res.success) {
                        alert(res.message || 'Failed to update grade');
                        return;
                    }

                    if (document.getElementById('auto_compute').checked) {
                        // Update the final grade cell for this student
                        const selector = `.final-grade[data-student="${studentId}"][data-subject="${subjectId}"]`;
                        const fg = document.querySelector(selector);
                        if (fg) fg.textContent = (typeof res.final_grade !== 'undefined' && res.final_grade !== null) ? parseFloat(res.final_grade).toFixed(2) : '-';
                    }
                    
                    // Update remarks badge
                    if (res.remarks) {
                        const remarksSelector = `.remarks-badge[data-student="${studentId}"][data-subject="${subjectId}"] span`;
                        const remarksBadge = document.querySelector(remarksSelector);
                        if (remarksBadge) {
                            remarksBadge.textContent = res.remarks;
                            // Update badge color based on remarks
                            remarksBadge.className = '';
                            if (res.remarks === 'Passed') {
                                remarksBadge.className = 'bg-green-500/20 text-green-400';
                            } else if (res.remarks === 'Failed') {
                                remarksBadge.className = 'bg-red-500/20 text-red-400';
                            } else {
                                remarksBadge.className = 'bg-yellow-500/20 text-yellow-400';
                            }
                        }
                    }
                }).catch(function(err) {
                    console.error(err);
                    alert('An error occurred while updating the grade');
                });
            }, 550);
        });
    });
    
    // Section filter change handler
    const sectionFilter = document.getElementById('section_filter');
    if (sectionFilter) {
        sectionFilter.addEventListener('change', function() {
            const sectionId = this.value;
            const subjectId = '<?= isset($subject->id) ? $subject->id : "" ?>';
            const url = '<?= site_url("teacher/subjects/") ?>' + subjectId + '/students' +
                (sectionId && sectionId !== 'all' ? '?section_id=' + sectionId : '');
            window.location.href = url;
        });
    }
    
    // Submit grades for review
    document.getElementById('submit_grades_btn')?.addEventListener('click', function() {
        const sectionId = '<?= isset($selected_section) ? $selected_section : "all" ?>';
        const sectionText = (sectionId && sectionId !== 'all') ? ' for this section' : '';
        
        if (!confirm(`Are you sure you want to submit all grades${sectionText} for admin review? You won't be able to edit them until they are reviewed.`)) {
            return;
        }
        
        const subjectId = '<?= isset($subject->id) ? $subject->id : "" ?>';
        
        let body = `subject_id=${encodeURIComponent(subjectId)}`;
        if (sectionId && sectionId !== 'all') {
            body += `&section_id=${encodeURIComponent(sectionId)}`;
        }
        
        fetch('<?= site_url("teacher/submit_grades") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(r => r.json()).then(function(res) {
            if (res.success) {
                alert(res.message || 'Grades submitted successfully!');
                location.reload(); // Refresh to show updated status
            } else {
                alert(res.message || 'Failed to submit grades');
            }
        }).catch(function(err) {
            console.error(err);
            alert('An error occurred while submitting grades');
        });
    });
});
</script>

