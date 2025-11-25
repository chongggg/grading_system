<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php $page = 'Import Grades'; ?>
<?php include 'app/views/teacher/_header.php'; ?>

<!-- Breadcrumb -->
<nav class="mb-4 text-sm">
    <ol class="list-none p-0 inline-flex" style="color: #64748b;">
        <li class="flex items-center">
            <a href="<?= site_url('teacher/subjects') ?>" class="transition" style="color: #10b981;">My Subjects</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="flex items-center">
            <?php 
            $breadcrumb_section = !empty($selected_section) && $selected_section !== 'all' ? '?section_id=' . $selected_section : '';
            ?>
            <a href="<?= site_url('teacher/subjects/' . (isset($subject) ? $subject->id : '') . '/students' . $breadcrumb_section) ?>" class="transition" style="color: #10b981;"><?= isset($subject) ? htmlspecialchars($subject->name) : 'Subject' ?></a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li style="color: #1e293b;">Import Grades</li>
    </ol>
</nav>

<?php if (isset($subject) && $subject): ?>
    <!-- Subject Info Card -->
    <div class="card rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold" style="color: #093FB4;"><?= htmlspecialchars($subject->name) ?></h2>
                <div class="mt-1 text-sm" style="color: #1e293b;">
                    <span class="mr-4">Code: <?= htmlspecialchars($subject->code ?? '') ?></span>
                    <?php if (isset($subject->section)): ?>
                        <span class="mr-4">Section: <?= htmlspecialchars($subject->section) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm" style="color: #1e293b;">
                    <?php
                        $total = isset($subject->students) ? count($subject->students) : 0;
                        echo "<span style=\"color: #10b981;\">{$total}</span> student" . ($total !== 1 ? 's' : '');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="card rounded-lg p-6 mb-6">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-2" style="color: #093FB4;">How to Import Grades</h3>
                <ol class="space-y-2 text-sm" style="color: #1e293b;">
                    <li><strong>Step 1:</strong> Download the Excel template with pre-filled student list</li>
                    <li><strong>Step 2:</strong> Fill in the grades (Prelim, Midterm, Finals) for each student</li>
                    <li><strong>Step 3:</strong> Upload the completed Excel file</li>
                    <li><strong>Step 4:</strong> Review the preview and confirm import</li>
                </ol>
                <div class="mt-4 p-3 rounded-lg" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3);">
                    <p class="text-sm" style="color: #ca8a04;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> Do not modify the Student ID or Student Name columns. Grades must be between 0 and 100.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Section -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <!-- Download Template -->
        <div class="card rounded-lg p-6">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                    <i class="fas fa-file-download text-green-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2" style="color: #093FB4;">Download Template</h3>
                <p class="text-sm mb-4" style="color: #1e293b;">Get the Excel template with student list pre-filled</p>
                <?php 
                $section_param = !empty($selected_section) ? '?section_id=' . $selected_section : '';
                ?>
                <a href="<?= site_url('teacher/download_template/' . $subject->id . $section_param) ?>" 
                   class="inline-block px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-download mr-2"></i>Download Template
                </a>
            </div>
        </div>

        <!-- Upload File -->
        <div class="card rounded-lg p-6">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-blue-500/20 flex items-center justify-center mb-4">
                    <i class="fas fa-file-upload text-blue-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2" style="color: #093FB4;">Upload Excel File</h3>
                <p class="text-sm mb-4" style="color: #1e293b;">Select the completed Excel file to upload</p>
                
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="subject_id" value="<?= $subject->id ?>">
                    <div class="mb-4">
                        <label for="excel_file" class="cursor-pointer inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-file-excel mr-2"></i>Choose File
                        </label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" class="hidden" required>
                        <div id="fileName" class="mt-2 text-sm" style="color: #64748b;"></div>
                    </div>
                    <button type="submit" id="uploadBtn" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors" disabled>
                        <i class="fas fa-upload mr-2"></i>Upload & Validate
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Error Display -->
    <div id="errorContainer" class="hidden card rounded-lg p-6 mb-6" style="border: 1px solid rgba(239, 68, 68, 0.5); background: rgba(239, 68, 68, 0.1);">
        <div class="flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-xl mt-1" style="color: #ef4444;"></i>
            <div class="flex-1">
                <h4 class="text-lg font-semibold mb-2" style="color: #ef4444;">Validation Errors</h4>
                <ul id="errorList" class="space-y-1 text-sm" style="color: #dc2626;"></ul>
            </div>
        </div>
    </div>

    <!-- Preview Table -->
    <div id="previewContainer" class="hidden card rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold" style="color: #093FB4;">Preview Import Data</h3>
            <div class="text-sm" style="color: #1e293b;">
                <span id="previewCount">0</span> records ready to import
            </div>
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full">
                <thead>
                    <tr style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <th class="px-4 py-3 text-left" style="color: #FFFFFF;">Student ID</th>
                        <th class="px-4 py-3 text-left" style="color: #FFFFFF;">Student Name</th>
                        <th class="px-4 py-3 text-center" style="color: #FFFFFF;">Prelim</th>
                        <th class="px-4 py-3 text-center" style="color: #FFFFFF;">Midterm</th>
                        <th class="px-4 py-3 text-center" style="color: #FFFFFF;">Finals</th>
                        <th class="px-4 py-3 text-center" style="color: #FFFFFF;">Average</th>
                    </tr>
                </thead>
                <tbody id="previewTableBody" class="divide-y" style="border-color: #e5e7eb;">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button" id="cancelBtn" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="button" id="confirmImportBtn" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-check mr-2"></i>Confirm Import
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 flex items-center justify-center" style="z-index: 9999; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
        <div class="rounded-lg p-8 text-center" style="background: rgba(255, 255, 255, 0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <div class="animate-spin rounded-full h-16 w-16 mx-auto mb-4" style="border: 4px solid #10b981; border-top-color: transparent;"></div>
            <p class="text-lg font-semibold" id="loadingText" style="color: #1e293b;">Processing...</p>
        </div>
    </div>

<?php else: ?>
    <div class="card rounded-lg p-6 text-center">
        <i class="fas fa-exclamation-triangle text-4xl mb-4" style="color: #f59e0b;"></i>
        <p style="color: #1e293b;">Subject not found or you don't have access to this subject.</p>
        <a href="<?= site_url('teacher/subjects') ?>" class="mt-4 inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            Back to Subjects
        </a>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    let previewData = null;

    // File selection handler
    $('#excel_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#fileName').text(file.name);
            $('#uploadBtn').prop('disabled', false);
        } else {
            $('#fileName').text('');
            $('#uploadBtn').prop('disabled', true);
        }
    });

    // Upload form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $('#loadingOverlay').removeClass('hidden');
        $('#loadingText').text('Uploading and validating file...');
        $('#errorContainer').addClass('hidden');
        $('#previewContainer').addClass('hidden');

        $.ajax({
            url: '<?= site_url("teacher/upload_excel") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('#loadingOverlay').addClass('hidden');
                
                if (response.success) {
                    previewData = response.data;
                    displayPreview(response.data);
                    $('#previewCount').text(response.total_rows);
                    $('#previewContainer').removeClass('hidden');
                    
                    // Reset form
                    $('#uploadForm')[0].reset();
                    $('#fileName').text('');
                    $('#uploadBtn').prop('disabled', true);
                } else {
                    displayErrors(response.errors || [response.message]);
                }
            },
            error: function() {
                $('#loadingOverlay').addClass('hidden');
                displayErrors(['Failed to upload file. Please try again.']);
            }
        });
    });

    // Display preview table
    function displayPreview(data) {
        const tbody = $('#previewTableBody');
        tbody.empty();

        data.forEach(function(row) {
            const avg = calculateAverage(row.prelim, row.midterm, row.finals);
            const avgClass = avg >= 75 ? 'text-green-400' : (avg > 0 ? 'text-red-400' : 'text-gray-400');
            
            const tr = $('<tr>').addClass('hover:bg-white/5').html(`
                <td class="px-4 py-3">${row.student_id}</td>
                <td class="px-4 py-3">${escapeHtml(row.student_name)}</td>
                <td class="px-4 py-3 text-center">${formatGrade(row.prelim)}</td>
                <td class="px-4 py-3 text-center">${formatGrade(row.midterm)}</td>
                <td class="px-4 py-3 text-center">${formatGrade(row.finals)}</td>
                <td class="px-4 py-3 text-center ${avgClass} font-semibold">${avg > 0 ? avg.toFixed(2) : '-'}</td>
            `);
            
            tbody.append(tr);
        });
    }

    // Calculate average
    function calculateAverage(prelim, midterm, finals) {
        if (prelim !== null && midterm !== null && finals !== null) {
            return (parseFloat(prelim) + parseFloat(midterm) + parseFloat(finals)) / 3;
        }
        return 0;
    }

    // Format grade display
    function formatGrade(grade) {
        return grade !== null && grade !== '' ? parseFloat(grade).toFixed(2) : '-';
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Display errors
    function displayErrors(errors) {
        const errorList = $('#errorList');
        errorList.empty();
        
        errors.forEach(function(error) {
            errorList.append($('<li>').text(error));
        });
        
        $('#errorContainer').removeClass('hidden');
        $('html, body').animate({ scrollTop: $('#errorContainer').offset().top - 100 }, 500);
    }

    // Cancel preview
    $('#cancelBtn').on('click', function() {
        $('#previewContainer').addClass('hidden');
        previewData = null;
    });

    // Confirm import
    $('#confirmImportBtn').on('click', function() {
        if (!previewData) {
            alert('No data to import');
            return;
        }

        if (!confirm('Are you sure you want to import these grades? This will update existing grades for these students.')) {
            return;
        }

        $('#loadingOverlay').removeClass('hidden');
        $('#loadingText').text('Importing grades...');

        $.ajax({
            url: '<?= site_url("teacher/execute_import") ?>',
            type: 'POST',
            data: {
                subject_id: <?= $subject->id ?>
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingOverlay').addClass('hidden');
                
                if (response.success) {
                    alert(`Import successful!\n\nNew grades: ${response.imported}\nUpdated grades: ${response.updated}`);
                    const sectionParam = '<?= !empty($selected_section) && $selected_section !== "all" ? "?section_id=" . $selected_section : "" ?>';
                    window.location.href = '<?= site_url("teacher/subjects/" . $subject->id . "/students") ?>' + sectionParam;
                } else {
                    let message = 'Import failed:\n\n';
                    if (response.errors && response.errors.length > 0) {
                        message += response.errors.join('\n');
                    } else {
                        message += 'Unknown error occurred';
                    }
                    alert(message);
                }
            },
            error: function() {
                $('#loadingOverlay').addClass('hidden');
                alert('Failed to import grades. Please try again.');
            }
        });
    });
});
</script>

<?php include 'app/views/teacher/_footer.php'; ?>
