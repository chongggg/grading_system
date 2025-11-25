<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded card max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Add New Section</h2>
        <p class="text-sm text-gray-400 mt-1">Create a new class section</p>
    </div>

    <form method="POST" action="<?= site_url('admin/add_section') ?>" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Section Name <span class="text-red-500">*</span></label>
            <input type="text" 
                   name="section_name" 
                   required 
                   placeholder="e.g., Section A, Einstein, Maple" 
                   class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Grade Level <span class="text-red-500">*</span></label>
                <select name="grade_level" 
                        required 
                        class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500">
                    <option value="">Select Grade Level</option>
                    <?php if (!empty($grade_levels)): ?>
                        <?php foreach ($grade_levels as $level): ?>
                            <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for ($i = 7; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">School Year <span class="text-red-500">*</span></label>
                <select name="school_year" 
                        required 
                        class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500">
                    <?php 
                        $current_year = date('Y');
                        for ($i = 0; $i <= 2; $i++):
                            $year = ($current_year + $i) . '-' . ($current_year + $i + 1);
                    ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Semester <span class="text-red-500">*</span></label>
            <select name="semester" 
                    required 
                    class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500">
                <option value="1st">1st Semester</option>
                <option value="2nd">2nd Semester</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Class Adviser</label>
                <select name="adviser_id" 
                        class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500">
                    <option value="">No Adviser</option>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher['id'] ?>">
                                <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Maximum Capacity</label>
                <input type="number" 
                       name="max_capacity" 
                       value="40" 
                       min="1" 
                       max="100" 
                       class="w-full px-4 py-2 rounded bg-white/5 border border-white/10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
            <a href="<?= site_url('admin/sections') ?>" 
               class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700 transition">
                <i class="fas fa-times mr-1"></i> Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 transition">
                <i class="fas fa-save mr-1"></i> Create Section
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
