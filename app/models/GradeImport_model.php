<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class GradeImport_model extends Model
{
    /**
     * Validate Excel file structure and data
     * Returns array with 'valid' boolean and 'errors' array
     */
    public function validate_excel($file_path, $subject_id)
    {
        $errors = [];
        $warnings = [];
        
        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            // Check if file has data
            if ($highestRow < 2) {
                $errors[] = 'Excel file is empty or has no data rows';
                return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
            }
            
            // Validate headers (row 1)
            $requiredHeaders = ['Student ID', 'Student Name', 'Prelim', 'Midterm', 'Finals'];
            for ($col = 1; $col <= 5; $col++) {
                $header = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if ($header !== $requiredHeaders[$col - 1]) {
                    $errors[] = "Invalid header in column {$col}. Expected '{$requiredHeaders[$col - 1]}', got '{$header}'";
                }
            }
            
            if (!empty($errors)) {
                return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
            }
            
            // Get valid student IDs for this subject
            $valid_students = $this->get_students_for_subject($subject_id);
            $valid_student_ids = array_column($valid_students, 'id');
            
            // Validate data rows
            $data_rows = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $student_id = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $student_name = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $prelim = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $midterm = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $finals = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                
                // Skip empty rows
                if (empty($student_id) && empty($student_name) && empty($prelim) && empty($midterm) && empty($finals)) {
                    continue;
                }
                
                $row_errors = [];
                
                // Validate student ID
                if (empty($student_id)) {
                    $row_errors[] = "Row {$row}: Student ID is required";
                } elseif (!in_array($student_id, $valid_student_ids)) {
                    $row_errors[] = "Row {$row}: Student ID {$student_id} is not enrolled in this subject";
                }
                
                // Validate grades
                foreach (['Prelim' => $prelim, 'Midterm' => $midterm, 'Finals' => $finals] as $period => $grade) {
                    if ($grade !== null && $grade !== '') {
                        if (!is_numeric($grade)) {
                            $row_errors[] = "Row {$row}: {$period} grade must be numeric";
                        } elseif ($grade < 0 || $grade > 100) {
                            $row_errors[] = "Row {$row}: {$period} grade must be between 0 and 100";
                        }
                    }
                }
                
                if (!empty($row_errors)) {
                    $errors = array_merge($errors, $row_errors);
                } else {
                    $data_rows[] = [
                        'student_id' => $student_id,
                        'student_name' => $student_name,
                        'prelim' => $prelim !== '' ? floatval($prelim) : null,
                        'midterm' => $midterm !== '' ? floatval($midterm) : null,
                        'finals' => $finals !== '' ? floatval($finals) : null
                    ];
                }
            }
            
            if (!empty($errors)) {
                return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
            }
            
            if (empty($data_rows)) {
                $errors[] = 'No valid data rows found in Excel file';
                return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
            }
            
            return [
                'valid' => true,
                'errors' => [],
                'warnings' => $warnings,
                'data' => $data_rows,
                'total_rows' => count($data_rows)
            ];
            
        } catch (Exception $e) {
            $errors[] = 'Error reading Excel file: ' . $e->getMessage();
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }
    }
    
    /**
     * Import grades from validated data
     */
    public function import_grades($data, $subject_id, $teacher_id)
    {
        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        
        try {
            // Get current school year and semester from subject
            $subject = $this->db->table('subjects')
                ->where('id', $subject_id)
                ->get();
            
            if (!$subject) {
                return [
                    'success' => false,
                    'imported' => 0,
                    'updated' => 0,
                    'failed' => count($data),
                    'errors' => ['Subject not found']
                ];
            }
            
            // Use current academic year (subjects table doesn't have school_year)
            $current_year = date('Y');
            $current_month = date('n');
            // If it's July or later, use current year - next year, otherwise use previous year - current year
            $school_year = ($current_month >= 7) ? $current_year . '-' . ($current_year + 1) : ($current_year - 1) . '-' . $current_year;
            $semester = $subject['semester'] ?? '1st';
            
            foreach ($data as $row) {
                $student_id = $row['student_id'];
                
                // Check if grade record exists
                $existing = $this->db->table('grades')
                    ->where('student_id', $student_id)
                    ->where('subject_id', $subject_id)
                    ->get();
                
                $grade_data = [
                    'prelim' => $row['prelim'],
                    'midterm' => $row['midterm'],
                    'finals' => $row['finals'],
                    'teacher_id' => $teacher_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Calculate final grade if all periods are filled
                if ($row['prelim'] !== null && $row['midterm'] !== null && $row['finals'] !== null) {
                    $final_grade = ($row['prelim'] + $row['midterm'] + $row['finals']) / 3;
                    $grade_data['final_grade'] = $final_grade;
                    $grade_data['remarks'] = $final_grade >= 75 ? 'Passed' : 'Failed';
                } else {
                    $grade_data['remarks'] = 'Incomplete';
                }
                
                if ($existing) {
                    // Update existing record
                    $result = $this->db->table('grades')
                        ->where('student_id', $student_id)
                        ->where('subject_id', $subject_id)
                        ->update($grade_data);
                    
                    if ($result) {
                        $updated++;
                    } else {
                        $failed++;
                        $errors[] = "Failed to update grades for student ID {$student_id}";
                    }
                } else {
                    // Insert new record
                    $grade_data['student_id'] = $student_id;
                    $grade_data['subject_id'] = $subject_id;
                    $grade_data['school_year'] = $school_year;
                    $grade_data['semester'] = $semester;
                    $grade_data['status'] = 'Draft';
                    
                    $result = $this->db->table('grades')->insert($grade_data);
                    
                    if ($result) {
                        $imported++;
                    } else {
                        $failed++;
                        $errors[] = "Failed to import grades for student ID {$student_id}";
                    }
                }
            }
            
            return [
                'success' => $failed === 0,
                'imported' => $imported,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'imported' => $imported,
                'updated' => $updated,
                'failed' => $failed + (count($data) - $imported - $updated),
                'errors' => array_merge($errors, ['Database error: ' . $e->getMessage()])
            ];
        }
    }
    
    /**
     * Get students enrolled in a subject
     */
    private function get_students_for_subject($subject_id, $section_id = null)
    {
        try {
            $sql = "SELECT DISTINCT s.id, s.first_name, s.last_name, s.email
                FROM students s
                INNER JOIN student_subjects ss ON ss.student_id = s.id
                WHERE ss.subject_id = ? AND (s.deleted_at IS NULL OR s.deleted_at = '')";
            
            $params = [$subject_id];
            
            // Apply section filter if provided
            if ($section_id !== null && $section_id !== '' && $section_id !== 'all') {
                $sql .= " AND s.section_id = ?";
                $params[] = intval($section_id);
            }
            
            $sql .= " ORDER BY s.last_name, s.first_name";
            
            $students = $this->db->raw($sql, $params);
            
            return $students ? $students->fetchAll() : [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate sample Excel template for download
     */
    public function generate_template($subject_id, $section_id = null)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = ['Student ID', 'Student Name', 'Prelim', 'Midterm', 'Finals'];
            foreach ($headers as $col => $header) {
                $worksheet->setCellValueByColumnAndRow($col + 1, 1, $header);
                $worksheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }
            
            // Get students for this subject
            $students = $this->get_students_for_subject($subject_id, $section_id);
            
            // Add student data
            $row = 2;
            foreach ($students as $student) {
                $worksheet->setCellValueByColumnAndRow(1, $row, $student['id']);
                $worksheet->setCellValueByColumnAndRow(2, $row, $student['last_name'] . ', ' . $student['first_name']);
                $worksheet->setCellValueByColumnAndRow(3, $row, ''); // Prelim
                $worksheet->setCellValueByColumnAndRow(4, $row, ''); // Midterm
                $worksheet->setCellValueByColumnAndRow(5, $row, ''); // Finals
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'E') as $col) {
                $worksheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            return $spreadsheet;
            
        } catch (Exception $e) {
            return null;
        }
    }
}
