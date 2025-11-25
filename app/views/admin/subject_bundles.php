<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded-xl card hover-lift">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="color: #093FB4;">Subject Sets</h2>
            <p class="text-sm mt-1" style="color: #64748b;">Manage subject sets for each grade level and semester</p>
        </div>
        <a href="<?= site_url('admin/add_bundle') ?>" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift inline-flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
            <i class="fas fa-plus mr-2"></i> Create Sets
        </a>
    </div>

    <!-- Bundles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (!empty($bundles)): ?>
            <?php foreach ($bundles as $bundle): ?>
                <div class="p-4 rounded-xl border hover-lift transition" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.1); box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-bold" style="color: #093FB4;">
                                <?= htmlspecialchars($bundle['bundle_name']) ?>
                            </h3>
                            <p class="text-sm" style="color: #64748b;">
                                Grade <?= htmlspecialchars($bundle['grade_level']) ?> • <?= htmlspecialchars($bundle['semester']) ?> Semester
                            </p>
                            <p class="text-xs" style="color: #94a3b8;"><?= htmlspecialchars($bundle['school_year']) ?></p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="<?= site_url('admin/edit_bundle/' . $bundle['id']) ?>" 
                               class="hover:underline" style="color: #093FB4;" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('admin/delete_bundle/' . $bundle['id']) ?>" 
                               class="hover:underline" style="color: #dc2626;"
                               onclick="return confirm('Delete this bundle? This will NOT unenroll students.')"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($bundle['description'])): ?>
                        <p class="text-sm mb-3" style="color: #64748b;"><?= htmlspecialchars($bundle['description']) ?></p>
                    <?php endif; ?>

                    <div class="flex items-center text-sm mb-3" style="color: #475569;">
                        <i class="fas fa-book mr-2" style="color: #093FB4;"></i>
                        <span><?= $bundle['subject_count'] ?> subject(s)</span>
                    </div>

                    <a href="<?= site_url('admin/view_bundle/' . $bundle['id']) ?>" 
                       class="block text-center px-4 py-2 rounded-lg border text-sm font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                        <i class="fas fa-eye mr-1"></i> View & Manage
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-layer-group text-6xl mb-4" style="color: #93c5fd;"></i>
                <h3 class="text-xl font-semibold mb-2" style="color: #475569;">No Subject Sets Found</h3>
                <p style="color: #64748b;">Create your first bundle to organize subjects by grade level and semester.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
