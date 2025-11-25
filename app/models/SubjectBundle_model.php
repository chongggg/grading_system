<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class SubjectBundle_model extends Model
{
    /**
     * Get all subject bundles
     */
    public function get_all_bundles()
    {
        $bundles = $this->db->table('subject_bundles')
            ->order_by('school_year', 'DESC')
            ->order_by('grade_level', 'ASC')
            ->order_by('semester', 'ASC')
            ->get_all();
        
        if ($bundles) {
            foreach ($bundles as $idx => $bundle) {
                // Get subject count
                $count = $this->db->table('subject_bundle_items')
                    ->where('bundle_id', $bundle['id'])
                    ->get_all();
                $bundles[$idx]['subject_count'] = count($count ?: []);
            }
        }
        
        return $bundles ?: [];
    }

    /**
     * Get a single bundle with its subjects
     */
    public function get_bundle($id)
    {
        $bundle = $this->db->table('subject_bundles')
            ->where('id', $id)
            ->get();
        
        if ($bundle) {
            // Get subjects in this bundle
            $sql = "SELECT s.*, t.first_name as teacher_first_name, t.last_name as teacher_last_name, sbi.id as item_id 
                    FROM subject_bundle_items sbi 
                    JOIN subjects s ON sbi.subject_id = s.id 
                    LEFT JOIN teachers t ON s.teacher_id = t.id 
                    WHERE sbi.bundle_id = ? 
                    ORDER BY s.subject_name ASC";
            
            $stmt = $this->db->raw($sql, [$id]);
            $subjects = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            
            $bundle['subjects'] = $subjects ?: [];
            $bundle['subject_count'] = count($subjects ?: []);
        }
        
        return $bundle;
    }

    /**
     * Create a new bundle
     */
    public function create_bundle($data)
    {
        $bundle_data = [
            'bundle_name' => $data['bundle_name'],
            'grade_level' => $data['grade_level'],
            'semester' => $data['semester'],
            'school_year' => $data['school_year'],
            'description' => !empty($data['description']) ? $data['description'] : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('subject_bundles')->insert($bundle_data);
    }

    /**
     * Update a bundle
     */
    public function update_bundle($id, $data)
    {
        $bundle_data = [
            'bundle_name' => $data['bundle_name'],
            'grade_level' => $data['grade_level'],
            'semester' => $data['semester'],
            'school_year' => $data['school_year'],
            'description' => !empty($data['description']) ? $data['description'] : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('subject_bundles')
            ->where('id', $id)
            ->update($bundle_data);
    }

    /**
     * Delete a bundle
     */
    public function delete_bundle($id)
    {
        // Bundle items will be cascade deleted due to foreign key constraint
        $result = $this->db->table('subject_bundles')->where('id', $id)->delete();
        return ['success' => $result, 'message' => $result ? 'Bundle deleted successfully' : 'Failed to delete bundle'];
    }

    /**
     * Add subject to bundle
     */
    public function add_subject_to_bundle($bundle_id, $subject_id)
    {
        // Check if already exists
        $existing = $this->db->table('subject_bundle_items')
            ->where('bundle_id', $bundle_id)
            ->where('subject_id', $subject_id)
            ->get();
        
        if ($existing) {
            return ['success' => false, 'message' => 'Subject already in bundle'];
        }
        
        $result = $this->db->table('subject_bundle_items')->insert([
            'bundle_id' => $bundle_id,
            'subject_id' => $subject_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => $result, 'message' => $result ? 'Subject added to bundle' : 'Failed to add subject'];
    }

    /**
     * Remove subject from bundle
     */
    public function remove_subject_from_bundle($item_id)
    {
        $result = $this->db->table('subject_bundle_items')
            ->where('id', $item_id)
            ->delete();
        
        return ['success' => $result, 'message' => $result ? 'Subject removed from bundle' : 'Failed to remove subject'];
    }

    /**
     * Get available subjects for a bundle (matching grade level)
     */
    public function get_available_subjects($grade_level, $semester, $bundle_id = null)
    {
        $sql = "SELECT s.*, t.first_name as teacher_first_name, t.last_name as teacher_last_name 
                FROM subjects s 
                LEFT JOIN teachers t ON s.teacher_id = t.id 
                WHERE s.grade_level = ? AND s.semester = ? 
                ORDER BY s.subject_name ASC";
        
        $stmt = $this->db->raw($sql, [$grade_level, $semester]);
        $all_subjects = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        if (!$all_subjects || !$bundle_id) {
            return $all_subjects ?: [];
        }
        
        // Filter out subjects already in bundle
        $bundle_subjects = $this->db->table('subject_bundle_items')
            ->where('bundle_id', $bundle_id)
            ->get_all();
        
        $bundle_subject_ids = array_column($bundle_subjects ?: [], 'subject_id');
        
        return array_filter($all_subjects, function($subject) use ($bundle_subject_ids) {
            return !in_array($subject['id'], $bundle_subject_ids);
        });
    }

    /**
     * Get bundle by grade level and semester
     */
    public function get_bundle_by_grade_semester($grade_level, $semester, $school_year)
    {
        return $this->db->table('subject_bundles')
            ->where('grade_level', $grade_level)
            ->where('semester', $semester)
            ->where('school_year', $school_year)
            ->get();
    }

    /**
     * Enroll student in all subjects from a bundle
     */
    public function enroll_student_in_bundle($student_id, $bundle_id)
    {
        $bundle = $this->get_bundle($bundle_id);
        
        if (!$bundle || empty($bundle['subjects'])) {
            return ['success' => false, 'message' => 'Bundle not found or has no subjects'];
        }
        
        $enrolled_count = 0;
        $skipped_count = 0;
        
        foreach ($bundle['subjects'] as $subject) {
            // Check if already enrolled
            $existing = $this->db->table('student_subjects')
                ->where('student_id', $student_id)
                ->where('subject_id', $subject['id'])
                ->get();
            
            if ($existing) {
                $skipped_count++;
                continue;
            }
            
            // Enroll student
            $result = $this->db->table('student_subjects')->insert([
                'student_id' => $student_id,
                'subject_id' => $subject['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                $enrolled_count++;
            }
        }
        
        $message = "Enrolled in {$enrolled_count} subject(s)";
        if ($skipped_count > 0) {
            $message .= ", skipped {$skipped_count} already enrolled";
        }
        
        return [
            'success' => $enrolled_count > 0,
            'message' => $message,
            'enrolled_count' => $enrolled_count,
            'skipped_count' => $skipped_count
        ];
    }

    /**
     * Auto-enroll students when assigned to section with bundle
     */
    public function auto_enroll_section_students($section_id)
    {
        // Get section details
        $section = $this->db->table('sections')->where('id', $section_id)->get();
        
        if (!$section) {
            return [
                'success' => false, 
                'message' => 'Section not found',
                'total_enrolled' => 0,
                'total_skipped' => 0,
                'debug' => 'Section ID: ' . $section_id
            ];
        }
        
        // Get semester from section (defaults to 1st if not set for backward compatibility)
        $semester = !empty($section['semester']) ? $section['semester'] : '1st';
        
        // Find matching bundle - try exact match first
        $bundle = $this->get_bundle_by_grade_semester(
            $section['grade_level'],
            $semester,
            $section['school_year']
        );
        
        // If not found, try without school year
        if (!$bundle) {
            $bundle = $this->db->table('subject_bundles')
                ->where('grade_level', $section['grade_level'])
                ->where('semester', $semester)
                ->get();
        }
        
        // If still not found, return error with details
        if (!$bundle) {
            return [
                'success' => false, 
                'message' => 'No subject bundle found for Grade ' . $section['grade_level'] . ', Semester ' . $semester . '. Please create a bundle first.',
                'total_enrolled' => 0,
                'total_skipped' => 0,
                'debug' => [
                    'section_grade' => $section['grade_level'],
                    'section_semester' => $semester,
                    'section_sy' => $section['school_year']
                ]
            ];
        }
        
        // Get full bundle with subjects
        $bundle_with_subjects = $this->get_bundle($bundle['id']);
        
        if (!$bundle_with_subjects || empty($bundle_with_subjects['subjects'])) {
            return [
                'success' => false, 
                'message' => 'Bundle found but has no subjects assigned. Please add subjects to the bundle.',
                'total_enrolled' => 0,
                'total_skipped' => 0,
                'debug' => [
                    'bundle_id' => $bundle['id'],
                    'bundle_name' => $bundle['bundle_name']
                ]
            ];
        }
        
        // Get students in section
        $sql = "SELECT * FROM students 
                WHERE section_id = ? 
                AND (deleted_at IS NULL OR deleted_at = '')";
        $stmt = $this->db->raw($sql, [$section_id]);
        $students = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        if (empty($students)) {
            return [
                'success' => true, 
                'message' => 'No students in section to enroll',
                'total_enrolled' => 0,
                'total_skipped' => 0
            ];
        }
        
        $total_enrolled = 0;
        $total_skipped = 0;
        $errors = [];
        
        foreach ($students as $student) {
            try {
                $result = $this->enroll_student_in_bundle($student['id'], $bundle_with_subjects['id']);
                if ($result['success'] || $result['enrolled_count'] > 0) {
                    $total_enrolled += $result['enrolled_count'];
                    $total_skipped += $result['skipped_count'];
                } else if (!$result['success']) {
                    $errors[] = "Student {$student['id']}: " . $result['message'];
                }
            } catch (Exception $e) {
                $errors[] = "Error enrolling student {$student['id']}: " . $e->getMessage();
            }
        }
        
        $message = "Auto-enrolled " . count($students) . " student(s) in bundle '{$bundle_with_subjects['bundle_name']}': {$total_enrolled} new enrollment(s), {$total_skipped} already enrolled";
        
        return [
            'success' => true,
            'message' => $message,
            'total_enrolled' => $total_enrolled,
            'total_skipped' => $total_skipped,
            'bundle_name' => $bundle_with_subjects['bundle_name'],
            'subject_count' => count($bundle_with_subjects['subjects']),
            'errors' => $errors
        ];
    }
}
