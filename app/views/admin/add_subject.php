<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">Add New Subject</h2>
        <a href="<?= site_url('admin/subjects') ?>" class="px-3 py-2 rounded bg-gray-600 text-sm">Back to Subjects</a>
    </div>

    <form method="POST" action="<?= site_url('admin/add_subject') ?>" class="space-y-4">
        <div>
            <label class="block text-gray-300 mb-1">Subject Code</label>
            <input type="text" name="subject_code" required 
                   class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                   value="<?= isset($_POST['subject_code']) ? htmlspecialchars($_POST['subject_code']) : '' ?>">
        </div>

        <div>
            <label class="block text-gray-300 mb-1">Subject Name</label>
            <input type="text" name="subject_name" required
                   class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                   value="<?= isset($_POST['subject_name']) ? htmlspecialchars($_POST['subject_name']) : '' ?>">
        </div>

        <div>
            <label class="block text-gray-300 mb-1">Grade Level</label>
            <select name="grade_level" 
                    class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">Select Grade Level</option>
                <?php 
                $grades = range(7, 12);
                foreach ($grades as $grade): 
                    $selected = (isset($_POST['grade_level']) && $_POST['grade_level'] == $grade) ? 'selected' : '';
                ?>
                    <option value="<?= $grade ?>" <?= $selected ?>>Grade <?= $grade ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-gray-300 mb-1">Semester</label>
            <select name="semester" 
                    class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="1st" <?= (isset($_POST['semester']) && $_POST['semester'] == '1st') ? 'selected' : '' ?>>1st Semester</option>
                <option value="2nd" <?= (isset($_POST['semester']) && $_POST['semester'] == '2nd') ? 'selected' : '' ?>>2nd Semester</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-300 mb-1">Description</label>
            <textarea name="description" rows="3" 
                      class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            ><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>


<div>
    <label class="block text-gray-300 mb-1">Assign Teacher</label>
    <select name="teacher_id" class="w-full p-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <option value="">-- Select Teacher --</option>
        <?php if (!empty($teachers)): foreach ($teachers as $t): ?>
            <?php 
                // Get the correct ID field - check multiple possible fields
                $t_id = $t['id'] ?? $t['teacher_id'] ?? $t['student_id'] ?? null;
                $selected = (isset($_POST['teacher_id']) && $_POST['teacher_id'] == $t_id) ? 'selected' : '';
                
                // Build display name
                $display_name = trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''));
                if (empty($display_name)) {
                    $display_name = $t['email'] ?? 'Unknown';
                }
            ?>
            <?php if ($t_id): ?>
                <option value="<?= htmlspecialchars($t_id) ?>" <?= $selected ?>>
                    <?= htmlspecialchars($display_name) ?>
                    <?php if (!empty($t['is_legacy'])): ?> (Legacy)<?php endif; ?>
                </option>
            <?php endif; ?>
        <?php endforeach; endif; ?>
    </select>
</div>

        <div class="pt-4">
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                Save Subject
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/_footer.php'; ?>