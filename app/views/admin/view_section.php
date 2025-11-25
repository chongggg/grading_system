<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <!-- Section Header -->
    <div class="mb-6 pb-4 border-b border-white/10">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-blue-400"><?= htmlspecialchars($section['section_name']) ?></h2>
                <p class="text-gray-400 mt-1">
                    Grade <?= htmlspecialchars($section['grade_level']) ?> • <?= htmlspecialchars($section['school_year']) ?>
                    <?php if (!empty($section['semester'])): ?>
                        • <span class="text-purple-400"><?= htmlspecialchars($section['semester']) ?> Semester</span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?= site_url('admin/sections') ?>" 
               class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Sections
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="p-4 rounded bg-blue-500/10 border border-blue-500/30">
                <div class="text-sm text-gray-400">Students</div>
                <div class="text-2xl font-semibold"><?= $section['student_count'] ?> / <?= $section['max_capacity'] ?></div>
            </div>
            <?php if (!empty($section['adviser_first_name'])): ?>
                <div class="p-4 rounded bg-purple-500/10 border border-purple-500/30">
                    <div class="text-sm text-gray-400">Class Adviser</div>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($section['adviser_first_name'] . ' ' . $section['adviser_last_name']) ?></div>
                    <?php if (!empty($section['adviser_email'])): ?>
                        <div class="text-sm text-gray-400"><?= htmlspecialchars($section['adviser_email']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="p-4 rounded bg-green-500/10 border border-green-500/30">
                <div class="text-sm text-gray-400">Available Slots</div>
                <div class="text-2xl font-semibold"><?= $section['max_capacity'] - $section['student_count'] ?></div>
            </div>
        </div>
    </div>

    <!-- Assigned Students -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Assigned Students</h3>
            <button type="button" 
                    onclick="openAssignModal()" 
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 transition">
                <i class="fas fa-user-plus mr-2"></i> Assign Students
            </button>
        </div>

        <?php if (!empty($section['students'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-gray-400">
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($section['students'] as $student): ?>
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </td>
                                <td class="px-4 py-3"><?= htmlspecialchars($student['email']) ?></td>
                                <td class="px-4 py-3">
                                    <button type="button" 
                                            onclick="removeStudent(<?= $student['id'] ?>)" 
                                            class="text-red-400 hover:text-red-300">
                                        <i class="fas fa-times mr-1"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p>No students assigned to this section yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Students Modal -->
<div id="assignModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Assign Students to Section</h3>
                    <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 160px);">
                <div class="mb-4">
                    <input type="text" 
                           id="studentSearch" 
                           placeholder="Search unassigned students..." 
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500">
                </div>

                <?php if (!empty($unassigned_students)): ?>
                    <div class="space-y-2">
                        <?php foreach ($unassigned_students as $student): ?>
                            <div class="flex items-center p-3 rounded bg-white/5 hover:bg-white/10 student-item" 
                                 data-name="<?= htmlspecialchars(strtolower($student['first_name'] . ' ' . $student['last_name'])) ?>">
                                <input type="checkbox" 
                                       class="student-checkbox mr-3" 
                                       value="<?= $student['id'] ?>"
                                       id="student_<?= $student['id'] ?>">
                                <label for="student_<?= $student['id'] ?>" class="flex-1 cursor-pointer">
                                    <div class="font-medium"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                                    <div class="text-sm text-gray-400"><?= htmlspecialchars($student['email']) ?></div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-info-circle text-3xl mb-2"></i>
                        <p>No unassigned students available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-6 border-t border-white/10 flex items-center justify-between">
                <div class="text-sm text-gray-400">
                    <span id="selectedCount">0</span> student(s) selected
                </div>
                <div class="flex space-x-3">
                    <button type="button" 
                            onclick="closeAssignModal()" 
                            class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="button" 
                            onclick="bulkAssignStudents()" 
                            class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 transition">
                        <i class="fas fa-check mr-1"></i> Assign Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const sectionId = <?= $section['id'] ?>;

function openAssignModal() {
    document.getElementById('assignModal').classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
}

// Search students
document.getElementById('studentSearch')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.student-item').forEach(item => {
        const name = item.dataset.name;
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
});

// Update selected count
document.querySelectorAll('.student-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const count = document.querySelectorAll('.student-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Bulk assign students
function bulkAssignStudents() {
    const studentIds = Array.from(document.querySelectorAll('.student-checkbox:checked'))
        .map(cb => cb.value);
    
    if (studentIds.length === 0) {
        alert('Please select at least one student');
        return;
    }
    
    if (!confirm(`Assign ${studentIds.length} student(s) to this section?`)) {
        return;
    }

    $.ajax({
        url: '<?= site_url("admin/bulk_assign_section") ?>',
        type: 'POST',
        data: {
            student_ids: studentIds,
            section_id: sectionId
        },
        dataType: 'json',
        success: function(response) {
            console.log('Assignment Response:', response);
            
            if (response.success) {
                let message = response.message;
                
                // Show auto-enrollment details if available
                if (response.auto_enrollment) {
                    console.log('Auto-enrollment details:', response.auto_enrollment);
                    message += '\n\nAuto-enrollment: ' + response.auto_enrollment.message;
                    
                    if (response.auto_enrollment.errors && response.auto_enrollment.errors.length > 0) {
                        message += '\n\nErrors:\n' + response.auto_enrollment.errors.join('\n');
                    }
                }
                
                // Show warning if auto-enrollment failed
                if (response.warning) {
                    message += '\n\nWarning: ' + response.warning;
                }
                
                alert(message);
                location.reload();
            } else {
                alert(response.message || 'Failed to assign students');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('An error occurred while assigning students');
        }
    });
}

// Remove student from section
function removeStudent(studentId) {
    if (!confirm('Remove this student from the section?')) {
        return;
    }

    $.ajax({
        url: '<?= site_url("admin/remove_student_from_section") ?>',
        type: 'POST',
        data: { student_id: studentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message || 'Failed to remove student');
            }
        },
        error: function() {
            alert('An error occurred');
        }
    });
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
