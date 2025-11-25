<?php $this->call->view('student/_header'); ?>

<!-- Grades Header -->
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold mb-2" style="color: #093FB4;">My Grades</h1>
        <p style="color: #1e293b;">View your reviewed academic performance</p>
    </div>
    <?php if (!empty($grades)): ?>
        <a href="<?= site_url('student/download_pdf') ?>" id="download-pdf-btn" 
           class="px-6 py-3 rounded-lg transition-all duration-300 flex items-center shadow-lg hover:shadow-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #FFFFFF;">
            <i class="fas fa-file-pdf mr-2"></i>Download PDF Report
        </a>
    <?php endif; ?>
</div>

<?php if (!empty($grades)): ?>
    <!-- Grades Table -->
    <div class="card rounded-lg overflow-hidden" style="opacity: 0; transform: translateY(20px);" id="grades-card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #FFFFFF;">Subject Code</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #FFFFFF;">Subject Name</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">Prelim</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">Midterm</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">Finals</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">Final Grade</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">Remarks</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #FFFFFF;">Teacher</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold" style="color: #FFFFFF;">School Year</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php 
                    $currentSchoolYear = '';
                    foreach ($grades as $index => $grade): 
                        // Group by school year
                        if ($currentSchoolYear !== $grade['school_year']):
                            $currentSchoolYear = $grade['school_year'];
                    ?>
                        <tr class="bg-white/5">
                            <td colspan="9" class="px-6 py-3">
                                <span class="text-sm font-semibold text-green-400">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    School Year: <?= htmlspecialchars($grade['school_year']) ?> - Semester <?= htmlspecialchars($grade['semester']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr class="hover:bg-white/5 transition-all duration-200 grade-row" data-index="<?= $index ?>">
                        <td class="px-6 py-4 text-sm font-mono" style="color: #1e293b;"><?= htmlspecialchars($grade['subject_code']) ?></td>
                        <td class="px-6 py-4 text-sm font-medium" style="color: #1e293b;">
                            <?= htmlspecialchars($grade['subject_name']) ?>
                            <?php if (!empty($grade['subject_description'])): ?>
                                <br><span class="text-xs" style="color: #64748b;"><?= htmlspecialchars($grade['subject_description']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center text-sm" style="color: #1e293b;">
                            <span class="font-semibold"><?= number_format($grade['prelim'], 2) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm" style="color: #1e293b;">
                            <span class="font-semibold"><?= number_format($grade['midterm'], 2) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm" style="color: #1e293b;">
                            <span class="font-semibold"><?= number_format($grade['finals'], 2) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-bold text-green-400"><?= number_format($grade['final_grade'], 2) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php
                            $remarksClass = '';
                            $remarksIcon = '';
                            switch($grade['remarks']) {
                                case 'Passed':
                                    $remarksClass = 'bg-green-500/20 text-green-400 border-green-500/30';
                                    $remarksIcon = 'fa-check-circle';
                                    break;
                                case 'Failed':
                                    $remarksClass = 'bg-red-500/20 text-red-400 border-red-500/30';
                                    $remarksIcon = 'fa-times-circle';
                                    break;
                                case 'Incomplete':
                                    $remarksClass = 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
                                    $remarksIcon = 'fa-exclamation-circle';
                                    break;
                            }
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border <?= $remarksClass ?>">
                                <i class="fas <?= $remarksIcon ?> mr-1"></i>
                                <?= htmlspecialchars($grade['remarks']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                <i class="fas fa-chalkboard-teacher mr-2" style="color: #3b82f6;"></i>
                                <div>
                                    <div class="font-medium" style="color: #1e293b;"><?= htmlspecialchars($grade['teacher_name']) ?></div>
                                    <?php if (!empty($grade['teacher_email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($grade['teacher_email']) ?>" 
                                           class="text-xs transition" style="color: #3b82f6;">
                                            <?= htmlspecialchars($grade['teacher_email']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm" style="color: #64748b;">
                            <?= htmlspecialchars($grade['school_year']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grade Summary -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        // Calculate statistics
        $total = count($grades);
        $passed = count(array_filter($grades, fn($g) => $g['remarks'] === 'Passed'));
        $failed = count(array_filter($grades, fn($g) => $g['remarks'] === 'Failed'));
        $incomplete = count(array_filter($grades, fn($g) => $g['remarks'] === 'Incomplete'));
        $average = $total > 0 ? array_sum(array_column($grades, 'final_grade')) / $total : 0;
        ?>
        
        <div class="card rounded-lg p-6 text-center">
            <i class="fas fa-trophy text-4xl mb-3" style="color: #f59e0b;"></i>
            <h3 class="text-3xl font-bold mb-1" style="color: #093FB4;"><?= number_format($average, 2) ?></h3>
            <p class="text-sm" style="color: #1e293b;">Overall Average</p>
        </div>

        <div class="card rounded-lg p-6 text-center">
            <i class="fas fa-check-circle text-4xl mb-3" style="color: #10b981;"></i>
            <h3 class="text-3xl font-bold mb-1" style="color: #093FB4;"><?= $passed ?>/<?= $total ?></h3>
            <p class="text-sm" style="color: #1e293b;">Subjects Passed</p>
        </div>

        <div class="card rounded-lg p-6 text-center">
            <i class="fas fa-chart-line text-4xl mb-3" style="color: #3b82f6;"></i>
            <h3 class="text-3xl font-bold mb-1" style="color: #093FB4;"><?= $total > 0 ? number_format(($passed / $total) * 100, 1) : 0 ?>%</h3>
            <p class="text-sm" style="color: #1e293b;">Success Rate</p>
        </div>
    </div>

<?php else: ?>
    <!-- No Grades Available -->
    <div class="card rounded-lg p-12 text-center">
        <i class="fas fa-clipboard-list text-6xl mb-4" style="color: #cbd5e1;"></i>
        <h3 class="text-2xl font-semibold mb-2" style="color: #093FB4;">No Grades Available</h3>
        <p class="mb-6" style="color: #1e293b;">Your grades haven't been reviewed yet. Check back later!</p>
        <div class="flex items-center justify-center space-x-2 text-sm" style="color: #1e293b;">
            <i class="fas fa-info-circle"></i>
            <span>Only reviewed grades are displayed here</span>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Slide down grades table
    $('#grades-card').css('transition', 'all 0.8s ease');
    setTimeout(() => {
        $('#grades-card').css('opacity', '1').css('transform', 'translateY(0)');
    }, 200);

    // Animate each grade row with stagger
    $('.grade-row').each(function(index) {
        const row = $(this);
        row.css('opacity', '0').css('transform', 'translateX(-30px)');
        
        setTimeout(() => {
            row.css('transition', 'all 0.5s ease');
            row.css('opacity', '1').css('transform', 'translateX(0)');
        }, 400 + (index * 50));
    });

    // Highlight row on hover
    $('.grade-row').on('mouseenter', function() {
        $(this).find('.font-bold').addClass('scale-110');
    }).on('mouseleave', function() {
        $(this).find('.font-bold').removeClass('scale-110');
    });

    // PDF Download with loading
    $('#download-pdf-btn').on('click', function(e) {
        const btn = $(this);
        const originalHtml = btn.html();
        
        // Show loading state
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating PDF...');
        btn.prop('disabled', true);
        
        // Show loading overlay
        showLoading('Generating your grade report...');
        
        // Simulate delay (actual PDF generation happens server-side)
        setTimeout(() => {
            hideLoading();
            btn.html(originalHtml);
            btn.prop('disabled', false);
            toastr.success('Grade report downloaded successfully!');
        }, 2000);
    });

    // Pulse animation on final grade cells
    $('td:has(.text-green-400)').each(function(index) {
        setTimeout(() => {
            pulseElement($(this).find('.text-green-400')[0]);
        }, 1000 + (index * 100));
    });

    // Animate summary cards
    $('.card').slice(-3).each(function(index) {
        $(this).css('opacity', '0').css('transform', 'scale(0.8)');
        setTimeout(() => {
            $(this).css('transition', 'all 0.5s ease');
            $(this).css('opacity', '1').css('transform', 'scale(1)');
        }, 1200 + (index * 150));
    });
});
</script>

<?php $this->call->view('student/_footer'); ?>
