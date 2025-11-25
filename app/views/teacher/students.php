<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Class List'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<!-- Breadcrumb -->
<div class="flex items-center space-x-2 text-sm text-gray-400 mb-6">
    <a href="<?= site_url('teacher/subjects') ?>" class="hover:text-white">Subjects</a>
    <i class="fas fa-chevron-right text-xs"></i>
    <span class="text-white"><?= htmlspecialchars($subject->name) ?></span>
    <i class="fas fa-chevron-right text-xs"></i>
    <span>Class List</span>
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
        <div class="grid grid-cols-2 gap-4">
            <div class="card bg-white/5 p-4 rounded-lg">
                <div class="text-sm text-gray-400">Total Students</div>
                <div class="text-2xl font-semibold mt-1">
                    <?= isset($subject->students) ? count($subject->students) : 0 ?>
                </div>
            </div>
            <div class="card bg-white/5 p-4 rounded-lg">
                <div class="text-sm text-gray-400">Male/Female</div>
                <div class="text-2xl font-semibold mt-1">
                    <?php
                        $male = 0;
                        $female = 0;
                        if (isset($subject->students)) {
                            foreach ($subject->students as $st) {
                                if (isset($st->gender)) {
                                    if (strtolower($st->gender) === 'male') $male++;
                                    else if (strtolower($st->gender) === 'female') $female++;
                                }
                            }
                        }
                        echo $male . '/' . $female;
                    ?>
                </div>
            </div>
            <div class="card bg-white/5 p-4 rounded-lg">
                <div class="text-sm text-gray-400">Passing</div>
                <div class="text-2xl font-semibold text-green-400 mt-1">
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
            <div class="card bg-white/5 p-4 rounded-lg">
                <div class="text-sm text-gray-400">Needs Grading</div>
                <div class="text-2xl font-semibold text-yellow-400 mt-1">
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

        <!-- Actions -->
        <div class="flex flex-col justify-center items-end space-y-3">
            <a href="<?= site_url('teacher/subjects/' . $subject->id . '/grades') ?>" class="w-full md:w-auto px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-center">
                <i class="fas fa-star mr-1"></i> Manage Grades
            </a>
            <?php if (isset($subject->description)): ?>
                <button type="button" id="viewDescriptionBtn" class="w-full md:w-auto px-4 py-2 bg-white/10 text-white rounded hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/20 focus:ring-offset-2 text-center">
                    <i class="fas fa-info-circle mr-1"></i> View Description
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Students List -->
<div class="card rounded-lg overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold mb-6">Class List</h3>

        <?php if (isset($subject->students) && !empty($subject->students)): ?>
            <!-- Filter/Search -->
            <div class="mb-6">
                <input type="text" 
                       id="studentSearch" 
                       placeholder="Search students..." 
                       class="w-full md:w-80 px-4 py-2 rounded bg-white/10 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-white placeholder-gray-400">
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Student ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Contact Info</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Current Grade</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5" id="studentTableBody">
                        <?php foreach ($subject->students as $student): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($student->id) ?></td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium">
                                        <?= htmlspecialchars($student->last_name . ', ' . $student->first_name) ?>
                                    </div>
                                    <?php if (isset($student->gender)): ?>
                                        <div class="text-xs text-gray-400">
                                            <?= ucfirst(htmlspecialchars($student->gender)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (isset($student->email)): ?>
                                        <div class="text-sm"><?= htmlspecialchars($student->email) ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($student->phone)): ?>
                                        <div class="text-xs text-gray-400"><?= htmlspecialchars($student->phone) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">
                                    <?= isset($student->final_grade) ? number_format($student->final_grade, 2) : '-' ?>
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
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No Students Enrolled</h3>
                <p class="text-gray-400">There are no students enrolled in this subject yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($subject->description)): ?>
<!-- Description Modal -->
<div id="descriptionModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-gray-800 rounded-lg max-w-2xl w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Subject Description</h3>
                <button type="button" id="closeDescriptionBtn" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="prose prose-invert max-w-none">
                <?= nl2br(htmlspecialchars($subject->description)) ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript for Search and Modal -->
<script>
$(document).ready(function() {
    // Search functionality
    $('#studentSearch').on('input', function() {
        var searchText = $(this).val().toLowerCase();
        
        $('#studentTableBody tr').each(function() {
            var nameText = $(this).find('td:nth-child(2)').text().toLowerCase();
            var idText = $(this).find('td:first').text().toLowerCase();
            
            if (nameText.includes(searchText) || idText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    <?php if (isset($subject->description)): ?>
    // Modal functionality
    $('#viewDescriptionBtn').click(function() {
        $('#descriptionModal').removeClass('hidden');
    });

    $('#closeDescriptionBtn').click(function() {
        $('#descriptionModal').addClass('hidden');
    });

    $(document).click(function(e) {
        if ($(e.target).is('#descriptionModal')) {
            $('#descriptionModal').addClass('hidden');
        }
    });

    // Close modal on escape key
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            $('#descriptionModal').addClass('hidden');
        }
    });
    <?php endif; ?>
});
</script>

<?php include 'app/views/teacher/_footer.php'; ?>