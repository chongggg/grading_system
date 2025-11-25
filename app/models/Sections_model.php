<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Sections_model extends Model
{
    /**
     * Get all sections
     */
    public function get_all_sections()
    {
        $sql = "SELECT s.*, t.first_name as adviser_first_name, t.last_name as adviser_last_name 
                FROM sections s 
                LEFT JOIN teachers t ON s.adviser_id = t.id 
                ORDER BY s.school_year DESC, s.grade_level ASC, s.section_name ASC";
        
        $stmt = $this->db->raw($sql);
        $sections = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        if ($sections) {
            // Get student count for each section
            foreach ($sections as $idx => $section) {
                $count = $this->db->table('students')
                    ->where('section_id', $section['id'])
                    ->where_null('deleted_at')
                    ->get_all();
                $sections[$idx]['student_count'] = count($count ?: []);
            }
        }
        
        return $sections ?: [];
    }

    /**
     * Get sections by grade level and school year
     */
    public function get_sections_by_grade_year($grade_level, $school_year)
    {
        return $this->db->table('sections')
            ->where('grade_level', $grade_level)
            ->where('school_year', $school_year)
            ->order_by('section_name', 'ASC')
            ->get_all() ?: [];
    }

    /**
     * Get a single section by ID
     */
    public function get_section($id)
    {
        $sql = "SELECT s.*, t.first_name as adviser_first_name, t.last_name as adviser_last_name, t.email as adviser_email 
                FROM sections s 
                LEFT JOIN teachers t ON s.adviser_id = t.id 
                WHERE s.id = ?";
        
        $stmt = $this->db->raw($sql, [$id]);
        $section = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        
        if ($section) {
            // Get students in this section
            $students = $this->db->table('students')
                ->where('section_id', $id)
                ->where_null('deleted_at')
                ->order_by('last_name', 'ASC')
                ->order_by('first_name', 'ASC')
                ->get_all();
            $section['students'] = $students ?: [];
            $section['student_count'] = count($students ?: []);
        }
        
        return $section;
    }

    /**
     * Create a new section
     */
    public function create_section($data)
    {
        $section_data = [
            'section_name' => $data['section_name'],
            'grade_level' => $data['grade_level'],
            'school_year' => $data['school_year'],
            'semester' => !empty($data['semester']) ? $data['semester'] : '1st',
            'adviser_id' => !empty($data['adviser_id']) ? $data['adviser_id'] : null,
            'max_capacity' => !empty($data['max_capacity']) ? $data['max_capacity'] : 40,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('sections')->insert($section_data);
    }

    /**
     * Update a section
     */
    public function update_section($id, $data)
    {
        $section_data = [
            'section_name' => $data['section_name'],
            'grade_level' => $data['grade_level'],
            'school_year' => $data['school_year'],
            'semester' => !empty($data['semester']) ? $data['semester'] : '1st',
            'adviser_id' => !empty($data['adviser_id']) ? $data['adviser_id'] : null,
            'max_capacity' => !empty($data['max_capacity']) ? $data['max_capacity'] : 40,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('sections')
            ->where('id', $id)
            ->update($section_data);
    }

    /**
     * Delete a section (only if no students assigned)
     */
    public function delete_section($id)
    {
        // Check if any students are assigned
        $student_count = $this->db->table('students')
            ->where('section_id', $id)
            ->where_null('deleted_at')
            ->get_all();
        
        if (count($student_count ?: []) > 0) {
            return ['success' => false, 'message' => 'Cannot delete section with assigned students'];
        }
        
        $result = $this->db->table('sections')->where('id', $id)->delete();
        return ['success' => $result, 'message' => $result ? 'Section deleted successfully' : 'Failed to delete section'];
    }

    /**
     * Get available teachers (for adviser assignment)
     */
    public function get_available_teachers()
    {
        return $this->db->table('teachers')
            ->select('id, first_name, middle_name, last_name, email')
            ->order_by('last_name', 'ASC')
            ->order_by('first_name', 'ASC')
            ->get_all() ?: [];
    }

    /**
     * Assign student to section
     */
    public function assign_student_to_section($student_id, $section_id)
    {
        // Check if section is at capacity
        $section = $this->get_section($section_id);
        if (!$section) {
            return ['success' => false, 'message' => 'Section not found'];
        }
        
        if ($section['student_count'] >= $section['max_capacity']) {
            return ['success' => false, 'message' => 'Section is at maximum capacity'];
        }
        
        $result = $this->db->table('students')
            ->where('id', $student_id)
            ->update([
                'section_id' => $section_id,
                'grade_level' => $section['grade_level'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        return ['success' => $result, 'message' => $result ? 'Student assigned to section' : 'Failed to assign student'];
    }

    /**
     * Remove student from section
     */
    public function remove_student_from_section($student_id)
    {
        $result = $this->db->table('students')
            ->where('id', $student_id)
            ->update([
                'section_id' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        return ['success' => $result, 'message' => $result ? 'Student removed from section' : 'Failed to remove student'];
    }

    /**
     * Bulk assign students to section
     */
    public function bulk_assign_students($student_ids, $section_id)
    {
        if (empty($student_ids) || !is_array($student_ids)) {
            return ['success' => false, 'message' => 'No students selected'];
        }

        $section = $this->get_section($section_id);
        if (!$section) {
            return ['success' => false, 'message' => 'Section not found'];
        }

        // Check capacity
        $available_slots = $section['max_capacity'] - $section['student_count'];
        if (count($student_ids) > $available_slots) {
            return ['success' => false, 'message' => "Section only has {$available_slots} available slots"];
        }

        $success_count = 0;
        foreach ($student_ids as $student_id) {
            $result = $this->db->table('students')
                ->where('id', $student_id)
                ->update([
                    'section_id' => $section_id,
                    'grade_level' => $section['grade_level'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            if ($result) {
                $success_count++;
            }
        }

        return [
            'success' => $success_count > 0,
            'message' => "{$success_count} student(s) assigned to section"
        ];
    }

    /**
     * Get unassigned students (students without section)
     */
    public function get_unassigned_students($grade_level = null)
    {
        if ($grade_level) {
            $sql = "SELECT * FROM students 
                    WHERE section_id IS NULL 
                    AND deleted_at IS NULL 
                    AND grade_level = ?
                    ORDER BY last_name ASC, first_name ASC";
            
            $stmt = $this->db->raw($sql, [$grade_level]);
        } else {
            $sql = "SELECT * FROM students 
                    WHERE section_id IS NULL 
                    AND deleted_at IS NULL 
                    ORDER BY last_name ASC, first_name ASC";
            
            $stmt = $this->db->raw($sql);
        }
        
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get distinct grade levels from subjects table
     */
    public function get_grade_levels()
    {
        $result = $this->db->raw("SELECT DISTINCT grade_level FROM subjects WHERE grade_level IS NOT NULL ORDER BY grade_level ASC");
        $levels = $result ? $result->fetchAll() : [];
        return array_column($levels, 'grade_level');
    }

    /**
     * Get distinct school years
     */
    public function get_school_years()
    {
        $result = $this->db->raw("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
        $years = $result ? $result->fetchAll() : [];
        $all_years = array_column($years, 'school_year');
        
        // Add current school year if not present
        $current_year = date('Y') . '-' . (date('Y') + 1);
        if (!in_array($current_year, $all_years)) {
            array_unshift($all_years, $current_year);
        }
        
        return $all_years;
    }
}
