<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded-xl card hover-lift">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold" style="color: #093FB4;">Subjects</h2>
        <a href="<?= site_url('admin/add_subject') ?>" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift inline-flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
            <i class="fas fa-plus mr-2"></i>Add Subject
        </a>
    </div>

    <div class="mt-4 overflow-x-auto">
        <?php if (!empty($subjects) && is_array($subjects)): ?>
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Code</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Name</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Grade Level</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Semester</th>
                        <th class="px-4 py-3 font-semibold" style="color: #475569;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $sub): ?>
                        <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);">
                            <td class="px-4 py-3" style="color: #1e293b; font-weight: 600;"><?= htmlspecialchars($sub['subject_code'] ?? '') ?></td>
                            <td class="px-4 py-3" style="color: #1e293b;"><?= htmlspecialchars($sub['subject_name'] ?? '') ?></td>
                            <td class="px-4 py-3" style="color: #475569;"><?= htmlspecialchars($sub['grade_level'] ?? '') ?></td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-medium badge-primary">
                                    <?= htmlspecialchars($sub['semester'] ?? '') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?= site_url('admin/edit_subject/' . ($sub['id'] ?? '')) ?>" class="mr-3 font-medium hover:underline" style="color: #093FB4;">Edit</a>
                                <a href="<?= site_url('admin/delete_subject/' . ($sub['id'] ?? '')) ?>" data-confirm="Are you sure you want to delete this subject?" class="font-medium hover:underline" style="color: #dc2626;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-12" style="color: #64748b;">
                <i class="fas fa-book text-6xl mb-4" style="color: #93c5fd;"></i>
                <p class="text-lg">No subjects found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
