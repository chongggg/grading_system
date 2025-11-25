<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Review Grades'; ?>
<?php include 'app/views/admin/_header.php'; ?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2" style="color: #093FB4;">Review Submitted Grades</h2>
            <p style="color: #1e293b;">Review and approve or reject grade submissions from teachers. Grouped by section for easier management.</p>
        </div>
        <?php if (isset($total_pending) && $total_pending > 0): ?>
            <div class="text-right">
                <span class="inline-block px-4 py-2 rounded-lg font-semibold" style="background: #fef3c7; color: #92400e;">
                    <i class="fas fa-clock mr-2"></i><?= $total_pending ?> Pending
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($grouped_grades) && !empty($grouped_grades)): ?>
    <?php foreach ($grouped_grades as $section): ?>
        <div class="card rounded-xl overflow-hidden mb-6">
            <!-- Section Header -->
            <div class="px-6 py-4 flex items-center justify-between" style="background: linear-gradient(135deg, #093FB4 0%, #0c4fd9 100%);">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-users text-xl" style="color: #FFFFFF;"></i>
                    <div>
                        <h3 class="text-lg font-bold" style="color: #FFFFFF;">
                            <?php if ($section['grade_level'] && $section['grade_level'] !== 'N/A'): ?>
                                <i class="fas fa-layer-group mr-2"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($section['section_name']) ?>
                        </h3>
                        <p class="text-sm" style="color: #FFFFFF; opacity: 0.9;"><?= count($section['grades']) ?> grade(s) pending review</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        onclick="bulkApproveSection('<?= $section['section_id'] ?>')" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all shadow-md"
                        style="background: #10b981; color: white;">
                        <i class="fas fa-check-double mr-2"></i>Approve All
                    </button>
                    <button 
                        onclick="bulkRejectSection('<?= $section['section_id'] ?>')" 
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all shadow-md"
                        style="background: #ef4444; color: white;">
                        <i class="fas fa-times-circle mr-2"></i>Reject All
                    </button>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1); background: #f8fafc;">
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" class="section-checkbox" data-section="<?= $section['section_id'] ?>" onchange="toggleSectionCheckboxes(this)">
                            </th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: #1e293b;">Teacher</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: #1e293b;">Student</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: #1e293b;">Subject</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Prelim</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Midterm</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Finals</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Final Grade</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Remarks</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color: #1e293b;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="section-tbody" data-section="<?= $section['section_id'] ?>">
                        <?php foreach ($section['grades'] as $grade): ?>
                            <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);" id="grade-row-<?= htmlspecialchars($grade['grade_id']) ?>">
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="grade-checkbox" data-grade-id="<?= $grade['grade_id'] ?>" data-section="<?= $section['section_id'] ?>">
                                </td>
                                <td class="px-4 py-3 text-sm" style="color: #1e293b;">
                                    <?= htmlspecialchars($grade['teacher_first_name'] . ' ' . $grade['teacher_last_name']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium" style="color: #1e293b;"><?= htmlspecialchars($grade['student_last_name'] . ', ' . $grade['student_first_name']) ?></div>
                                    <?php if (!empty($grade['grade_level']) && !empty($grade['section_name'])): ?>
                                        <div class="text-xs mt-1" style="color: #64748b;">
                                            <i class="fas fa-layer-group mr-1"></i>Grade <?= htmlspecialchars($grade['grade_level']) ?> - <?= htmlspecialchars($grade['section_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium" style="color: #1e293b;"><?= htmlspecialchars($grade['subject_name']) ?></div>
                                    <div class="text-xs" style="color: #64748b;"><?= htmlspecialchars($grade['subject_code']) ?></div>
                                    <div class="text-xs mt-1" style="color: #64748b;"><?= htmlspecialchars($grade['school_year']) ?> • <?= htmlspecialchars($grade['semester']) ?></div>
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-mono">
                                    <?= $grade['prelim'] ? number_format($grade['prelim'], 2) : '-' ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-mono">
                                    <?= $grade['midterm'] ? number_format($grade['midterm'], 2) : '-' ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-mono">
                                    <?= $grade['finals'] ? number_format($grade['finals'], 2) : '-' ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-semibold font-mono">
                                    <?= $grade['final_grade'] ? number_format($grade['final_grade'], 2) : '-' ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php 
                                    $remarks = $grade['remarks'] ?? 'Incomplete';
                                    $badgeStyle = '';
                                    if ($remarks === 'Passed') {
                                        $badgeStyle = 'background: #d1fae5; color: #065f46;';
                                    } elseif ($remarks === 'Failed') {
                                        $badgeStyle = 'background: #fee2e2; color: #991b1b;';
                                    } else {
                                        $badgeStyle = 'background: #fef3c7; color: #92400e;';
                                    }
                                    ?>
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold" style="<?= $badgeStyle ?>">
                                        <?= htmlspecialchars($remarks) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button 
                                            onclick="approveGrade(<?= $grade['grade_id'] ?>)" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium shadow-md hover-lift"
                                            style="background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); color: #FFFFFF;"
                                            title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button 
                                            onclick="rejectGrade(<?= $grade['grade_id'] ?>)" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium shadow-md hover-lift"
                                            style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #FFFFFF;"
                                            title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card rounded-xl p-12 text-center">
        <i class="fas fa-clipboard-check text-6xl mb-4" style="color: #93c5fd;"></i>
        <p class="text-lg font-semibold mb-2" style="color: #1e293b;">No pending grade submissions</p>
        <p class="text-sm" style="color: #1e293b;">All submitted grades have been reviewed.</p>
    </div>
<?php endif; ?>

<?php include 'app/views/admin/_footer.php'; ?>

<script>
function toggleSectionCheckboxes(sectionCheckbox) {
    const section = sectionCheckbox.dataset.section;
    const checkboxes = document.querySelectorAll(`.grade-checkbox[data-section="${section}"]`);
    checkboxes.forEach(cb => cb.checked = sectionCheckbox.checked);
}

function bulkApproveSection(sectionId) {
    const checkboxes = document.querySelectorAll(`.grade-checkbox[data-section="${sectionId}"]:checked`);
    
    if (checkboxes.length === 0) {
        alert('Please select at least one grade to approve.');
        return;
    }
    
    if (!confirm(`Are you sure you want to approve ${checkboxes.length} grade(s)? They will be visible to students.`)) {
        return;
    }
    
    const gradeIds = Array.from(checkboxes).map(cb => cb.dataset.gradeId);
    bulkAction('approve', gradeIds);
}

function bulkRejectSection(sectionId) {
    const checkboxes = document.querySelectorAll(`.grade-checkbox[data-section="${sectionId}"]:checked`);
    
    if (checkboxes.length === 0) {
        alert('Please select at least one grade to reject.');
        return;
    }
    
    const reason = prompt(`Please enter the reason for rejecting ${checkboxes.length} grade(s):`);
    if (!reason || reason.trim() === '') {
        alert('Rejection reason is required');
        return;
    }
    
    const gradeIds = Array.from(checkboxes).map(cb => cb.dataset.gradeId);
    bulkAction('reject', gradeIds, reason);
}

function bulkAction(action, gradeIds, reason = null) {
    let completed = 0;
    let failed = 0;
    
    const processNext = (index) => {
        if (index >= gradeIds.length) {
            alert(`Bulk ${action} completed:\n✓ Success: ${completed}\n✗ Failed: ${failed}`);
            location.reload();
            return;
        }
        
        const gradeId = gradeIds[index];
        const url = action === 'approve' ? '<?= site_url("admin/approve_grade") ?>' : '<?= site_url("admin/reject_grade") ?>';
        const body = action === 'approve' 
            ? `grade_id=${encodeURIComponent(gradeId)}`
            : `grade_id=${encodeURIComponent(gradeId)}&reason=${encodeURIComponent(reason)}`;
        
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(response => response.json())
        .then(function(res) {
            if (res.success) {
                completed++;
                const row = document.getElementById('grade-row-' + gradeId);
                if (row) row.remove();
            } else {
                failed++;
            }
            processNext(index + 1);
        })
        .catch(function(err) {
            failed++;
            processNext(index + 1);
        });
    };
    
    processNext(0);
}

function approveGrade(gradeId) {
    if (!confirm('Are you sure you want to approve this grade? It will be visible to the student.')) {
        return;
    }
    
    fetch('<?= site_url("admin/approve_grade") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `grade_id=${encodeURIComponent(gradeId)}`
    })
    .then(response => response.json())
    .then(function(res) {
        if (res.success) {
            const row = document.getElementById('grade-row-' + gradeId);
            if (row) {
                row.remove();
                // Check if section is now empty
                const tbody = row.closest('.section-tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    location.reload();
                }
            }
        } else {
            alert(res.message || 'Failed to approve grade');
        }
    })
    .catch(function(err) {
        console.error('Error:', err);
        alert('An error occurred while approving the grade');
    });
}

function rejectGrade(gradeId) {
    const reason = prompt('Please enter the reason for rejection:');
    if (!reason || reason.trim() === '') {
        alert('Rejection reason is required');
        return;
    }
    
    fetch('<?= site_url("admin/reject_grade") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `grade_id=${encodeURIComponent(gradeId)}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(function(res) {
        if (res.success) {
            const row = document.getElementById('grade-row-' + gradeId);
            if (row) {
                row.remove();
                // Check if section is now empty
                const tbody = row.closest('.section-tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    location.reload();
                }
            }
        } else {
            alert(res.message || 'Failed to reject grade');
        }
    })
    .catch(function(err) {
        console.error('Error:', err);
        alert('An error occurred while rejecting the grade');
    });
}
</script>
