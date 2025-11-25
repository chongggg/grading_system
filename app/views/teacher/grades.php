<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Grades'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<!-- Breadcrumb -->
<div class="flex items-center space-x-2 text-sm text-gray-400 mb-6">
    <a href="<?= site_url('teacher/subjects') ?>" class="hover:text-white">Subjects</a>
    <i class="fas fa-chevron-right text-xs"></i>
    <span class="text-white"><?= htmlspecialchars($subject->name) ?></span>
    <i class="fas fa-chevron-right text-xs"></i>
    <span>Grades</span>
</div>

<!-- Subject Info -->
<div class="card rounded-lg p-6 mb-6">
    <div class="grid md:grid-cols-3 gap-6">
        <div>
            <h3 class="text-2xl font-semibold mb-2"><?= htmlspecialchars($subject->name) ?></h3>
            <div class="text-sm text-gray-400">
                <div class="mb-1">
                    <span class="font-medium">Code:</span> 
                    <?= htmlspecialchars($subject->code) ?>
                </div>
                <?php if (isset($subject->section)): ?>
                    <div class="mb-1">
                        <span class="font-medium">Section:</span> 
                        <?= htmlspecialchars($subject->section) ?>
                    </div>
                <?php endif; ?>
                <div class="mb-1">
                    <span class="font-medium">School Year:</span>
                    <?= isset($subject->school_year) ? htmlspecialchars($subject->school_year) : date('Y') . '-' . (date('Y')+1) ?>
                </div>
                <?php if (isset($subject->semester)): ?>
                    <div>
                        <span class="font-medium">Semester:</span>
                        <?= htmlspecialchars($subject->semester) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-4">
            <div>
                <div class="text-sm text-gray-400">Total Students</div>
                <div class="text-2xl font-semibold">
                    <?= isset($subject->students) ? count($subject->students) : 0 ?>
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-400">Class Average</div>
                <div class="text-2xl font-semibold text-blue-400">
                    <?php
                        $total = 0;
                        $count = 0;
                        if (isset($subject->students)) {
                            foreach ($subject->students as $st) {
                                if (isset($st->final_grade)) {
                                    $total += $st->final_grade;
                                    $count++;
                                }
                            }
                        }
                        echo $count > 0 ? number_format($total / $count, 2) : '-';
                    ?>
                </div>
            </div>
        </div>

        <!-- Grade Distribution -->
        <div>
            <h4 class="text-sm text-gray-400 mb-2">Grade Distribution</h4>
            <?php
                $ranges = [
                    '95-100' => ['count' => 0, 'class' => 'bg-green-500'],
                    '90-94' => ['count' => 0, 'class' => 'bg-green-400'],
                    '85-89' => ['count' => 0, 'class' => 'bg-blue-400'],
                    '80-84' => ['count' => 0, 'class' => 'bg-blue-300'],
                    '75-79' => ['count' => 0, 'class' => 'bg-yellow-400'],
                    'Below 75' => ['count' => 0, 'class' => 'bg-red-400'],
                    'No Grade' => ['count' => 0, 'class' => 'bg-gray-400']
                ];

                if (isset($subject->students)) {
                    foreach ($subject->students as $st) {
                        if (!isset($st->final_grade)) {
                            $ranges['No Grade']['count']++;
                        } else {
                            $grade = $st->final_grade;
                            if ($grade >= 95) $ranges['95-100']['count']++;
                            elseif ($grade >= 90) $ranges['90-94']['count']++;
                            elseif ($grade >= 85) $ranges['85-89']['count']++;
                            elseif ($grade >= 80) $ranges['80-84']['count']++;
                            elseif ($grade >= 75) $ranges['75-79']['count']++;
                            else $ranges['Below 75']['count']++;
                        }
                    }
                }

                $maxCount = max(array_map(function($r) { return $r['count']; }, $ranges));
            ?>
            <div class="space-y-2">
                <?php foreach ($ranges as $label => $data): ?>
                    <div class="flex items-center text-sm">
                        <div class="w-16 text-gray-400"><?= $label ?></div>
                        <div class="flex-grow h-4 bg-white/5 rounded-full overflow-hidden ml-2">
                            <?php if ($maxCount > 0): ?>
                                <div class="h-full <?= $data['class'] ?>/20" 
                                     style="width: <?= ($data['count'] / $maxCount * 100) ?>%">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="w-8 text-right ml-2"><?= $data['count'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Grade Management -->
<div class="card rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-semibold">Grade Management</h3>
                
                <!-- Section Filter -->
                <?php if (!empty($sections)): ?>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-300">
                        <i class="fas fa-filter mr-1"></i>Section:
                    </label>
                    <select id="section_filter" class="px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="all" <?= empty($selected_section) || $selected_section === 'all' ? 'selected' : '' ?>>All Sections</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= htmlspecialchars($section['id']) ?>" <?= isset($selected_section) && $selected_section == $section['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section['section_name']) ?> (<?= htmlspecialchars($section['student_count']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="space-x-4">
                <a href="<?= site_url('teacher/subjects/' . $subject->id . '/students') ?>" class="text-sm text-gray-400 hover:text-white">
                    <i class="fas fa-users mr-1"></i> Class List
                </a>
                <button type="button" class="save-grades-btn px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-24">Prelim</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-24">Midterm</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-24">Finals</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-24">Final Grade</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider w-32">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (isset($subject->students) && !empty($subject->students)): ?>
                        <?php foreach ($subject->students as $student): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium">
                                        <?= htmlspecialchars($student->last_name . ', ' . $student->first_name) ?>
                                    </div>
                                    <div class="text-xs text-gray-400">ID: <?= $student->id ?></div>
                                </td>
                                <?php for ($period = 1; $period <= 3; $period++): ?>
                                    <td class="px-4 py-3 text-center">
                                        <div class="relative">
                                            <input type="number" 
                                                   class="grade-input w-20 px-2 py-1 rounded text-center bg-white/10 hover:bg-white/20 focus:bg-white/30 focus:outline-none"
                                                   min="0" 
                                                   max="100"
                                                   step="0.01"
                                                   value="<?= isset($student->grades[$period]) ? number_format($student->grades[$period], 2) : '' ?>"
                                                   data-student="<?= htmlspecialchars($student->id) ?>"
                                                   data-subject="<?= htmlspecialchars($subject->id) ?>"
                                                   data-period="<?= $period ?>"
                                            >
                                        </div>
                                    </td>
                                <?php endfor; ?>
                                <td class="px-4 py-3 text-center font-semibold">
                                    <span class="final-grade" data-student="<?= htmlspecialchars($student->id) ?>" data-subject="<?= htmlspecialchars($subject->id) ?>">
                                        <?= isset($student->final_grade) ? number_format($student->final_grade, 2) : '-' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (isset($student->final_grade)): ?>
                                        <?php if ($student->final_grade >= 75): ?>
                                            <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Passed</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs">Failed</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-xs">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-sm text-center text-gray-400">
                                No students enrolled in this subject
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript for handling grades -->
<script>
$(document).ready(function() {
    // Section filter change handler
    $('#section_filter').on('change', function() {
        const sectionId = $(this).val();
        const subjectId = '<?= isset($subject->id) ? $subject->id : "" ?>';
        const url = '<?= site_url("teacher/grades/") ?>' + subjectId + 
            (sectionId && sectionId !== 'all' ? '?section_id=' + sectionId : '');
        window.location.href = url;
    });
    
    // Save grades when button is clicked
    $('.save-grades-btn').click(function() {
        var grades = [];
        
        // Collect all grades
        $('.grade-input').each(function() {
            var input = $(this);
            if (input.val()) {
                grades.push({
                    student_id: input.data('student'),
                    subject_id: input.data('subject'),
                    period: input.data('period'),
                    grade: parseFloat(input.val())
                });
            }
        });

        // Show loading state
        var btn = $(this);
        var originalText = btn.html();
        btn.prop('disabled', true)
           .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Saving...')
           .addClass('opacity-75');

        // Send grades to server
        $.ajax({
            url: '<?= site_url('teacher/subjects/save-grades') ?>',
            type: 'POST',
            data: { grades: grades },
            success: function(response) {
                if (response.success) {
                    // Show success message and refresh page
                    location.reload();
                } else {
                    alert('Error saving grades: ' + response.message);
                    btn.prop('disabled', false)
                       .html(originalText)
                       .removeClass('opacity-75');
                }
            },
            error: function() {
                alert('Error saving grades. Please try again.');
                btn.prop('disabled', false)
                   .html(originalText)
                   .removeClass('opacity-75');
            }
        });
    });

    // Validate grade input
    $('.grade-input').on('input', function() {
        var value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });

    // Auto-calculate final grade on input change
    $('.grade-input').on('input', function() {
        var row = $(this).closest('tr');
        var grades = [];
        
        row.find('.grade-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) grades.push(val);
        });

        var finalGrade = '-';
        if (grades.length > 0) {
            var sum = grades.reduce((a, b) => a + b, 0);
            finalGrade = (sum / grades.length).toFixed(2);
        }

        var finalGradeEl = row.find('.final-grade');
        finalGradeEl.text(finalGrade);

        // Update status
        var statusEl = row.find('td:last-child');
        if (finalGrade !== '-') {
            if (parseFloat(finalGrade) >= 75) {
                statusEl.html('<span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Passed</span>');
            } else {
                statusEl.html('<span class="px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs">Failed</span>');
            }
        } else {
            statusEl.html('<span class="px-2 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-xs">Pending</span>');
        }
    });
});
</script>

<?php include 'app/views/teacher/_footer.php'; ?>
