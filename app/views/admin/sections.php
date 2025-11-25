<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="p-6 rounded-xl card hover-lift">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="color: #093FB4;">Sections Management</h2>
            <p class="text-sm mt-1" style="color: #64748b;">Manage class sections and student assignments</p>
        </div>
        <a href="<?= site_url('admin/add_section') ?>" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift inline-flex items-center" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
            <i class="fas fa-plus mr-2"></i> Add Section
        </a>
    </div>

    <!-- Filter Options -->
    <div class="mb-6 flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium mb-1" style="color: #475569;">Grade Level</label>
            <select id="filterGradeLevel" class="w-full px-4 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                <option value="">All Grade Levels</option>
                <?php foreach ($grade_levels as $level): ?>
                    <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium mb-1" style="color: #475569;">School Year</label>
            <select id="filterSchoolYear" class="w-full px-4 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                <option value="">All School Years</option>
                <?php foreach ($school_years as $year): ?>
                    <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium mb-1" style="color: #475569;">Search</label>
            <input type="text" 
                   id="sectionSearch" 
                   placeholder="Search sections..." 
                   class="w-full px-4 py-2 rounded-lg border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
        </div>
    </div>

    <!-- Sections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="sectionsGrid">
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section): ?>
                <div class="p-4 rounded-xl border hover-lift transition section-card" 
                     data-grade="<?= htmlspecialchars($section['grade_level']) ?>" 
                     data-year="<?= htmlspecialchars($section['school_year']) ?>"
                     data-name="<?= htmlspecialchars($section['section_name']) ?>"
                     style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.1); box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-bold" style="color: #093FB4;">
                                <?= htmlspecialchars($section['section_name']) ?>
                            </h3>
                            <p class="text-sm" style="color: #64748b;">
                                Grade <?= htmlspecialchars($section['grade_level']) ?> • <?= htmlspecialchars($section['school_year']) ?>
                                <?php if (!empty($section['semester'])): ?>
                                    • <span class="badge-primary px-2 py-0.5 rounded-full text-xs"><?= htmlspecialchars($section['semester']) ?> Sem</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="<?= site_url('admin/edit_section/' . $section['id']) ?>" 
                               class="hover:underline"
                               style="color: #093FB4;"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('admin/delete_section/' . $section['id']) ?>" 
                               class="hover:underline"
                               style="color: #dc2626;"
                               onclick="return confirm('Are you sure you want to delete this section?')"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <div class="space-y-2 mb-3">
                        <div class="flex items-center text-sm" style="color: #475569;">
                            <i class="fas fa-users mr-2" style="color: #093FB4;"></i>
                            <span><?= $section['student_count'] ?> / <?= $section['max_capacity'] ?> students</span>
                        </div>
                        <?php if (!empty($section['adviser_first_name'])): ?>
                            <div class="flex items-center text-sm" style="color: #475569;">
                                <i class="fas fa-chalkboard-teacher mr-2" style="color: #093FB4;"></i>
                                <span><?= htmlspecialchars($section['adviser_first_name'] . ' ' . $section['adviser_last_name']) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center text-sm" style="color: #94a3b8;">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>
                                <span>No adviser assigned</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Bar -->
                    <?php 
                        $percentage = ($section['max_capacity'] > 0) ? 
                            round(($section['student_count'] / $section['max_capacity']) * 100) : 0;
                        $barColor = $percentage >= 90 ? '#dc2626' : ($percentage >= 70 ? '#eab308' : '#16a34a');
                    ?>
                    <div class="w-full rounded-full h-2 mb-3" style="background: #E8F9FF;">
                        <div class="h-2 rounded-full" style="width: <?= $percentage ?>%; background: <?= $barColor ?>;"></div>
                    </div>

                    <a href="<?= site_url('admin/view_section/' . $section['id']) ?>" 
                       class="block text-center px-4 py-2 rounded-lg border text-sm font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-chalkboard text-6xl mb-4" style="color: #93c5fd;"></i>
                <h3 class="text-xl font-semibold mb-2" style="color: #475569;">No Sections Found</h3>
                <p style="color: #64748b;">Create your first section to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const gradeFilter = document.getElementById('filterGradeLevel');
    const yearFilter = document.getElementById('filterSchoolYear');
    const searchInput = document.getElementById('sectionSearch');
    const sectionCards = document.querySelectorAll('.section-card');

    function filterSections() {
        const selectedGrade = gradeFilter.value.toLowerCase();
        const selectedYear = yearFilter.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();

        sectionCards.forEach(card => {
            const grade = card.dataset.grade.toLowerCase();
            const year = card.dataset.year.toLowerCase();
            const name = card.dataset.name.toLowerCase();

            const matchesGrade = !selectedGrade || grade === selectedGrade;
            const matchesYear = !selectedYear || year === selectedYear;
            const matchesSearch = !searchTerm || name.includes(searchTerm);

            if (matchesGrade && matchesYear && matchesSearch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    gradeFilter.addEventListener('change', filterSections);
    yearFilter.addEventListener('change', filterSections);
    searchInput.addEventListener('input', filterSections);
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>
