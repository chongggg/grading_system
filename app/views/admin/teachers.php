<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded-xl card hover-lift">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold" style="color: #093FB4;">Teachers</h2>
        <a href="<?= site_url('admin/add_teacher') ?>" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift inline-flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
            <i class="fas fa-plus mr-2"></i>Add Teacher
        </a>
    </div>

    <div class="mt-4 overflow-x-auto">
        <?php if (!empty($teachers) && is_array($teachers)): ?>
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Name</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Email</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Username</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Assigned Subjects</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Source</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Specialization</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $t): ?>
                        <?php
                            $name = htmlspecialchars(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')));
                            $email = htmlspecialchars($t['email'] ?? '');
                            $username = htmlspecialchars($t['username'] ?? ($t['auth_username'] ?? ($t['username'] ?? '')));
                            $source = isset($t['username']) ? 'auth' : 'teachers';
                            $editId = $t['id'] ?? $t['auth_id'] ?? $t['student_id'] ?? $t['student_id'] ?? '';
                        ?>
                        <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);">
                            <td class="px-4 py-3" style="color: #1e293b;"><?= $name ?></td>
                            <td class="px-4 py-3" style="color: #475569;"><?= $email ?></td>
                            <td class="px-4 py-3" style="color: #475569;"><?= $username ?></td>
                            <td class="px-4 py-3">
                                <?php if (!empty($t['assigned_subjects']) && is_array($t['assigned_subjects'])): ?>
                                    <?php foreach ($t['assigned_subjects'] as $sub): ?>
                                        <span class="inline-block px-3 py-1 mr-1 mb-1 rounded-full text-xs font-medium badge-primary"><?php echo htmlspecialchars($sub['subject_name']); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-xs" style="color: #64748b;">No subjects</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3" style="color: #475569;"><?= $source ?></td>
                            <td class="px-4 py-3" style="color: #475569;"><?= htmlspecialchars($t['specialization'] ?? '') ?></td>
                            <td class="px-4 py-3">
                                <?php if ($source === 'teachers'): ?>
                                    <a href="<?= site_url('admin/edit_teacher/' . $editId) ?>" class="mr-3 font-medium hover:underline" style="color: #093FB4;">Edit</a>
                                    <a href="<?= site_url('admin/delete_teacher/' . $editId) ?>" data-confirm="Are you sure you want to delete this teacher?" class="font-medium hover:underline" style="color: #dc2626;">Delete</a>
                                <?php else: ?>
                                    <a href="<?= site_url('admin/sync_teacher/' . ($t['auth_id'] ?? ($t['id'] ?? ''))) ?>" class="mr-2 font-medium hover:underline" style="color: #16a34a;">Migrate</a>
                                    <span style="color: #64748b;">(legacy)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-12" style="color: #64748b;">
                <i class="fas fa-chalkboard-teacher text-6xl mb-4" style="color: #93c5fd;"></i>
                <p class="text-lg">No teachers found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
