<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded-xl card hover-lift">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="color: #093FB4;">Students</h2>
            <p class="text-sm mt-1" style="color: #64748b;">Manage students and their subject enrollments</p>
        </div>
        <a href="<?= site_url('admin/add_student') ?>" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift inline-flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
            <i class="fas fa-plus mr-2"></i> Add Student
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6">
        <input type="text" 
               id="studentSearch" 
               placeholder="Search by name or email..." 
               class="w-full md:w-80 px-4 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
    </div>

    <div class="relative overflow-x-auto">
        <?php if (!empty($students) && is_array($students)): ?>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Name</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Email</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Enrolled Subjects</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Status</th>
                        <th class="px-4 py-3 text-right font-semibold" style="color: #475569;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);">
                            <td class="px-4 py-3">
                                <div class="font-medium" style="color: #1e293b;"><?= htmlspecialchars(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?></div>
                                <div class="text-xs" style="color: #64748b;">ID: <?= $s['id'] ?></div>
                            </td>
                            <td class="px-4 py-3" style="color: #475569;"><?= htmlspecialchars($s['email'] ?? '') ?></td>
                            <td class="px-4 py-3">
                                <?php if (!empty($s['subjects'])): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($s['subjects'] as $subject): ?>
                                            <span class="badge-primary px-2 py-1 rounded-full text-xs inline-flex items-center">
                                                <?= htmlspecialchars($subject['name']) ?>
                                                <button type="button" 
                                                        class="ml-1 hover:underline" 
                                                        style="color: #dc2626;"
                                                        onclick="removeSubject(<?= $s['id'] ?>, <?= $subject['id'] ?>)"
                                                        title="Remove from subject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" 
                                            onclick="openSubjectModal(<?= $s['id'] ?>)" 
                                            class="mt-2 text-xs font-medium hover:underline" style="color: #093FB4;">
                                        <i class="fas fa-plus mr-1"></i> Add Subject
                                    </button>
                                <?php else: ?>
                                    <button type="button" 
                                            onclick="openSubjectModal(<?= $s['id'] ?>)" 
                                            class="text-xs font-medium hover:underline" style="color: #093FB4;">
                                        <i class="fas fa-plus mr-1"></i> Add Subject
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (isset($s['deleted_at']) && $s['deleted_at']): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium" style="background: #fef3c7; color: #92400e;">Deleted</span>
                                <?php else: ?>
                                    <span class="badge-success px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="<?= site_url('admin/edit_student/' . ($s['id'] ?? '')) ?>" 
                                   class="font-medium hover:underline" style="color: #093FB4;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('admin/delete_student/' . ($s['id'] ?? '')) ?>" 
                                   class="font-medium hover:underline" style="color: #dc2626;"
                                   onclick="return confirm('Are you sure you want to delete this student?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-users text-6xl mb-4" style="color: #93c5fd;"></i>
                <h3 class="text-xl font-semibold mb-2" style="color: #475569;">No Students Found</h3>
                <p style="color: #64748b;">Start by adding a new student to the system.</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- Pagination -->
    <?php if (!empty($pagination) && isset($pagination['last_page']) && $pagination['last_page'] > 1): ?>
        <div class="mt-4 flex items-center justify-center space-x-2">
            <?php
                $base = site_url('admin/students');
                $cur = (int)($pagination['current_page'] ?? 1);
                $last = (int)($pagination['last_page'] ?? 1);
            ?>
            <?php if ($cur > 1): ?>
                <a href="<?= $base . '?page=' . ($cur - 1) ?>" class="px-4 py-2 rounded-lg font-medium" style="background: rgba(9, 63, 180, 0.05); color: #093FB4; border: 1px solid rgba(9, 63, 180, 0.2);">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $last; $i++): ?>
                <a href="<?= $base . '?page=' . $i ?>" class="px-4 py-2 rounded-lg font-medium" style="<?= $i === $cur ? 'background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF; box-shadow: 0 2px 8px rgba(9, 63, 180, 0.3);' : 'background: rgba(9, 63, 180, 0.05); color: #093FB4; border: 1px solid rgba(9, 63, 180, 0.2);' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($cur < $last): ?>
                <a href="<?= $base . '?page=' . ($cur + 1) ?>" class="px-4 py-2 rounded-lg font-medium" style="background: rgba(9, 63, 180, 0.05); color: #093FB4; border: 1px solid rgba(9, 63, 180, 0.2);">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Subject Assignment Modal -->
<div id="subjectModal" class="hidden fixed inset-0 z-50" style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="rounded-xl max-w-lg w-full shadow-2xl" style="background: #FFFFFF;">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold" style="color: #093FB4;">Assign Subjects</h3>
                    <button type="button" onclick="closeSubjectModal()" class="hover:underline" style="color: #64748b;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="relative">
                        <input type="text" 
                               id="subjectSearch" 
                               placeholder="Search subjects..." 
                               class="w-full px-4 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                    </div>

                    <div id="availableSubjects" class="max-h-60 overflow-y-auto space-y-2">
                        <?php if (!empty($available_subjects)): ?>
                            <?php foreach ($available_subjects as $subject): ?>
                                <div class="flex items-center justify-between p-3 rounded-lg hover-lift transition" style="background: #E8F9FF;">
                                    <div>
                                        <div class="font-medium" style="color: #1e293b;"><?= htmlspecialchars($subject['name']) ?></div>
                                        <div class="text-sm" style="color: #64748b;">
                                            <?php if (!empty($subject['teacher_name'])): ?>
                                                Teacher: <?= htmlspecialchars($subject['teacher_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <button type="button"
                                            onclick="assignSubject(currentStudentId, <?= $subject['id'] ?>)"
                                            class="px-4 py-2 rounded-lg text-sm font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                                        Assign
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4" style="color: #64748b;">
                                No available subjects found
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentStudentId = null;

// Search functionality
document.getElementById('studentSearch').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        row.style.display = name.includes(search) || email.includes(search) ? '' : 'none';
    });
});

// Subject search in modal
document.getElementById('subjectSearch').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('#availableSubjects > div').forEach(div => {
        if (div.textContent.toLowerCase().includes(search)) {
            div.style.display = '';
        } else {
            div.style.display = 'none';
        }
    });
});

function openSubjectModal(studentId) {
    currentStudentId = studentId;
    document.getElementById('subjectModal').classList.remove('hidden');
    // Load available subjects for this student
    fetch(`<?= site_url('admin/get_available_subjects/') ?>${studentId}`)
        .then(response => response.json())
        .then(subjects => {
            const container = document.getElementById('availableSubjects');
            if (subjects.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-gray-400">No available subjects found</div>';
                return;
            }
            
            container.innerHTML = subjects.map(subject => `
                <div class="flex items-center justify-between p-3 rounded hover:bg-white/5">
                    <div>
                        <div class="font-medium">${subject.name}</div>
                        <div class="text-sm text-gray-400">
                            ${subject.teacher_name ? 'Teacher: ' + subject.teacher_name : ''}
                        </div>
                    </div>
                    <button type="button"
                            onclick="assignSubject(${currentStudentId}, ${subject.id})"
                            class="px-3 py-1 rounded text-sm bg-blue-500 hover:bg-blue-600">
                        Assign
                    </button>
                </div>
            `).join('');
        });
}

function closeSubjectModal() {
    document.getElementById('subjectModal').classList.add('hidden');
    currentStudentId = null;
}

function assignSubject(studentId, subjectId) {
    fetch('<?= site_url('admin/assign_subject') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ student_id: studentId, subject_id: subjectId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to show changes
        } else {
            alert('Failed to assign subject: ' + data.message);
        }
    });
}

function removeSubject(studentId, subjectId) {
    if (!confirm('Are you sure you want to remove this subject from the student?')) {
        return;
    }

    fetch('<?= site_url('admin/remove_subject') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ student_id: studentId, subject_id: subjectId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to show changes
        } else {
            alert('Failed to remove subject: ' + data.message);
        }
    });
}

// Close modal when clicking outside
document.getElementById('subjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSubjectModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentStudentId !== null) {
        closeSubjectModal();
    }
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>
