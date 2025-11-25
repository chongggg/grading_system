<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="mb-6 pb-4 border-b border-white/10">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-blue-400"><?= htmlspecialchars($bundle['bundle_name']) ?></h2>
                <p class="text-gray-400 mt-1">
                    Grade <?= htmlspecialchars($bundle['grade_level']) ?> • 
                    <?= htmlspecialchars($bundle['semester']) ?> Semester • 
                    <?= htmlspecialchars($bundle['school_year']) ?>
                </p>
                <?php if (!empty($bundle['description'])): ?>
                    <p class="text-sm text-gray-400 mt-2"><?= htmlspecialchars($bundle['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex space-x-2">
                <a href="<?= site_url('admin/edit_bundle/' . $bundle['id']) ?>" 
                   class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="<?= site_url('admin/subject_bundles') ?>" 
                   class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Subjects in Bundle -->
    <div class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Subjects in Sets (<?= $bundle['subject_count'] ?>)</h3>
            <button type="button" 
                    onclick="openAddSubjectModal()" 
                    class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 transition">
                <i class="fas fa-plus mr-1"></i> Add Subject
            </button>
        </div>

        <?php if (!empty($bundle['subjects'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($bundle['subjects'] as $subject): ?>
                    <div class="p-4 rounded bg-white/5 border border-white/10">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-blue-400"><?= htmlspecialchars($subject['subject_name']) ?></h4>
                                <p class="text-sm text-gray-400"><?= htmlspecialchars($subject['subject_code']) ?></p>
                                <?php if (!empty($subject['teacher_first_name'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                                        <?= htmlspecialchars($subject['teacher_first_name'] . ' ' . $subject['teacher_last_name']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <button type="button" 
                                    onclick="removeSubject(<?= $subject['item_id'] ?>)" 
                                    class="text-red-400 hover:text-red-300 ml-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-book-open text-4xl mb-4"></i>
                <p>No subjects added yet. Click "Add Subject" to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Subject to Bundle</h3>
                    <button type="button" onclick="closeAddSubjectModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 160px);">
                <?php if (!empty($available_subjects)): ?>
                    <div class="space-y-2">
                        <?php foreach ($available_subjects as $subject): ?>
                            <div class="flex items-center justify-between p-3 rounded bg-white/5 hover:bg-white/10">
                                <div class="flex-1">
                                    <div class="font-medium"><?= htmlspecialchars($subject['subject_name']) ?></div>
                                    <div class="text-sm text-gray-400"><?= htmlspecialchars($subject['subject_code']) ?></div>
                                </div>
                                <button type="button" 
                                        onclick="addSubjectToBundle(<?= $subject['id'] ?>)" 
                                        class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 transition text-sm">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <p>No more subjects available for this grade level.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const bundleId = <?= $bundle['id'] ?>;

function openAddSubjectModal() {
    document.getElementById('addSubjectModal').classList.remove('hidden');
}

function closeAddSubjectModal() {
    document.getElementById('addSubjectModal').classList.add('hidden');
}

function addSubjectToBundle(subjectId) {
    $.ajax({
        url: '<?= site_url("admin/add_subject_to_bundle") ?>',
        type: 'POST',
        data: { bundle_id: bundleId, subject_id: subjectId },
        dataType: 'json',
        success: function(response) {
            alert(response.message);
            if (response.success) location.reload();
        }
    });
}

function removeSubject(itemId) {
    if (!confirm('Remove this subject from the bundle?')) return;
    
    $.ajax({
        url: '<?= site_url("admin/remove_subject_from_bundle") ?>',
        type: 'POST',
        data: { item_id: itemId },
        dataType: 'json',
        success: function(response) {
            alert(response.message);
            if (response.success) location.reload();
        }
    });
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
