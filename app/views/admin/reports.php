<?php 
if (!defined('PREVENT_DIRECT_ACCESS')) exit;
$page = 'Reports';
include 'app/views/admin/_header.php'; 
?>

<style>
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(59, 130, 246, 0.5);
        outline: none;
    }
    .form-input option {
        background: #1a1a2e;
        color: white;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card rounded-xl shadow-md p-6 stat-card hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm" style="color: #64748b;">Total Students</p>
                <p class="text-3xl font-bold" style="color: #093FB4;"><?= number_format($summary['total_students']) ?></p>
            </div>
            <i class="fas fa-user-graduate text-4xl" style="color: #93c5fd;"></i>
        </div>
    </div>
    
    <div class="card rounded-xl shadow-md p-6 stat-card hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm" style="color: #64748b;">Total Teachers</p>
                <p class="text-3xl font-bold" style="color: #16a34a;"><?= number_format($summary['total_teachers']) ?></p>
            </div>
            <i class="fas fa-chalkboard-teacher text-4xl" style="color: #86efac;"></i>
        </div>
    </div>
    
    <div class="card rounded-xl shadow-md p-6 stat-card hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm" style="color: #64748b;">Total Subjects</p>
                <p class="text-3xl font-bold" style="color: #7c3aed;"><?= number_format($summary['total_subjects']) ?></p>
            </div>
            <i class="fas fa-book text-4xl" style="color: #c4b5fd;"></i>
        </div>
    </div>
    
    <div class="card rounded-xl shadow-md p-6 stat-card hover-lift">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm" style="color: #64748b;">Average Grade</p>
                <p class="text-3xl font-bold" style="color: #ea580c;"><?= number_format($summary['avg_grade'], 2) ?></p>
            </div>
            <i class="fas fa-chart-bar text-4xl" style="color: #fdba74;"></i>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card rounded-xl shadow-md p-6 mb-8">
    <h2 class="text-xl font-bold mb-4" style="color: #093FB4;">
        <i class="fas fa-filter mr-2"></i>Filter Reports
    </h2>
    
    <form method="GET" action="<?= site_url('reports') ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #475569;">Date From</label>
            <input type="date" name="date_from" value="<?= $filters['date_from'] ?>" 
                   class="w-full rounded-lg px-4 py-2 border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #475569;">Date To</label>
            <input type="date" name="date_to" value="<?= $filters['date_to'] ?>" 
                   class="w-full rounded-lg px-4 py-2 border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #475569;">Subject</label>
            <select name="subject_id" class="w-full rounded-lg px-4 py-2 border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject['id'] ?>" <?= $filters['subject_id'] == $subject['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #475569;">Teacher</label>
            <select name="teacher_id" class="w-full rounded-lg px-4 py-2 border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                <option value="">All Teachers</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['id'] ?>" <?= $filters['teacher_id'] == $teacher['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #475569;">Section</label>
            <select name="section" class="w-full rounded-lg px-4 py-2 border" style="background: #FFFFFF; border-color: rgba(9, 63, 180, 0.2); color: #1e293b;">
                <option value="">All Sections</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?= $section['id'] ?>" <?= $filters['section'] == $section['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($section['section_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="md:col-span-5 flex space-x-4">
            <button type="submit" class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift" style="background: linear-gradient(135deg, #093FB4 0%, #0652d4 100%); color: #FFFFFF;">
                <i class="fas fa-search mr-2"></i>Apply Filters
            </button>
            <a href="<?= site_url('reports') ?>" class="px-6 py-3 rounded-lg border font-medium hover-lift" style="background: rgba(9, 63, 180, 0.05); border-color: rgba(9, 63, 180, 0.2); color: #093FB4;">
                <i class="fas fa-times mr-2"></i>Clear Filters
            </a>
            <a href="<?= site_url('reports/export_csv?' . http_build_query($filters)) ?>" 
               class="px-6 py-3 rounded-lg font-medium shadow-md hover-lift ml-auto" style="background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); color: #FFFFFF;">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </a>
        </div>
    </form>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Submission Status Chart -->
    <div class="card rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold mb-4" style="color: #093FB4;">Submission Status</h3>
        <canvas id="submissionChart"></canvas>
    </div>
    
    <!-- Teacher Performance Chart -->
    <div class="card rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold mb-4" style="color: #093FB4;">Teacher Performance</h3>
        <canvas id="teacherChart"></canvas>
    </div>
</div>

<!-- Grade Statistics Table -->
<div class="card rounded-xl shadow-md overflow-hidden">
    <div class="p-6" style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
        <h2 class="text-xl font-bold" style="color: #093FB4;">
            <i class="fas fa-table mr-2"></i>Grade Statistics
        </h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr style="border-bottom: 2px solid rgba(9, 63, 180, 0.1);">
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #475569;">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #475569;">Teacher</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color: #475569;">Students</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color: #475569;">Avg Grade</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color: #475569;">Submission %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grade_statistics)): ?>
                    <?php foreach ($grade_statistics as $stat): ?>
                        <tr class="border-t hover:bg-blue-50 transition" style="border-color: rgba(9, 63, 180, 0.1);">
                            <td class="px-6 py-4 whitespace-nowrap font-medium" style="color: #1e293b;"><?= htmlspecialchars($stat['subject_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap" style="color: #475569;"><?= htmlspecialchars($stat['teacher_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center" style="color: #475569;"><?= number_format($stat['total_students']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    style="<?php if ($stat['avg_grade'] >= 90): ?>background: #d1fae5; color: #065f46;
                                    <?php elseif ($stat['avg_grade'] >= 75): ?>background: #dbeafe; color: #1e40af;
                                    <?php else: ?>background: #fee2e2; color: #991b1b;
                                    <?php endif; ?>">
                                    <?= number_format($stat['avg_grade'], 2) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center" style="color: #475569;"><?= number_format($stat['submission_percentage'], 2) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-6xl mb-4" style="color: #93c5fd;"></i>
                            <p style="color: #64748b;">No data available</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Submission Status Chart
    const submissionData = <?= json_encode($submission_status) ?>;
    const submissionLabels = submissionData.map(item => item.status);
    const submissionCounts = submissionData.map(item => item.count);
    
    new Chart(document.getElementById('submissionChart'), {
        type: 'pie',
        data: {
            labels: submissionLabels,
            datasets: [{
                data: submissionCounts,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#d1d5db'
                    }
                }
            }
        }
    });

    // Teacher Performance Chart
    const teacherData = <?= json_encode($teacher_performance) ?>;
    const teacherLabels = teacherData.map(item => item.teacher_name);
    const teacherGrades = teacherData.map(item => parseFloat(item.avg_grade));
    
    new Chart(document.getElementById('teacherChart'), {
        type: 'bar',
        data: {
            labels: teacherLabels,
            datasets: [{
                label: 'Average Grade',
                data: teacherGrades,
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        color: '#d1d5db'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#d1d5db'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#d1d5db'
                    }
                }
            }
        }
    });
</script>

<?php include 'app/views/admin/_footer.php'; ?>
