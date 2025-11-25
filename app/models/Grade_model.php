<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Grade_model extends Model
{
    protected $table = "grades";
    protected $primary_key = "id";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Count enrolled subjects for a student
     */
    public function count_enrolled_subjects($student_id)
    {
        $sql = "SELECT COUNT(*) as total FROM student_subjects WHERE student_id = ?";
        $result = $this->db->raw($sql, [$student_id]);
        
        if ($result) {
            $row = $result->fetch();
            return $row ? (int)$row['total'] : 0;
        }
        
        return 0;
    }

    /**
     * Count reviewed grades for a student
     */
    public function count_reviewed_grades($student_id)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE student_id = ? AND status = 'Reviewed'";
        $result = $this->db->raw($sql, [$student_id]);
        
        if ($result) {
            $row = $result->fetch();
            return $row ? (int)$row['total'] : 0;
        }
        
        return 0;
    }

    /**
     * Get only reviewed grades for a student with subject and teacher details
     * Students can only see grades with status = 'Reviewed'
     */
    public function get_reviewed_grades($student_id)
    {
        $sql = "SELECT 
                    g.id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grade,
                    g.remarks,
                    g.school_year,
                    g.semester,
                    s.subject_code,
                    s.subject_name,
                    s.description as subject_description,
                    CONCAT(t.first_name, ' ', COALESCE(t.middle_name, ''), ' ', t.last_name) as teacher_name,
                    t.email as teacher_email
                FROM {$this->table} g
                INNER JOIN subjects s ON g.subject_id = s.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                WHERE g.student_id = ?
                AND g.status = 'Reviewed'
                ORDER BY g.school_year DESC, g.semester DESC, s.subject_code ASC";
        
        $result = $this->db->raw($sql, [$student_id]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Get comprehensive grade report data for PDF generation
     */
    public function get_grade_report_data($student_id)
    {
        // Get student information with section details
        $sql = "SELECT 
                    s.id,
                    s.first_name,
                    s.middle_name,
                    s.last_name,
                    s.email,
                    s.gender,
                    s.birthdate,
                    s.address,
                    s.grade_level,
                    s.section_id,
                    sec.section_name,
                    sec.grade_level as section_grade_level
                FROM students s
                LEFT JOIN sections sec ON s.section_id = sec.id
                WHERE s.id = ?";
        
        $result = $this->db->raw($sql, [$student_id]);
        $student = $result ? $result->fetch() : null;
        
        if (!$student) {
            return null;
        }
        
        // Add formatted section display
        $student['section'] = $student['section_name'] ?? 'N/A';
        
        // Get reviewed grades with full details
        $grades = $this->get_reviewed_grades($student_id);
        
        return [
            'student' => $student,
            'grades' => $grades,
            'total_subjects' => count($grades),
            'generated_date' => date('F d, Y')
        ];
    }

    /**
     * Get grade by ID
     */
    public function get_grade($grade_id)
    {
        return $this->db->table($this->table)
            ->where('id', '=', $grade_id)
            ->get();
    }

    /**
     * Get all grades for a student (including non-reviewed) - for admin/teacher use
     */
    public function get_all_grades($student_id)
    {
        $sql = "SELECT 
                    g.id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grade,
                    g.remarks,
                    g.status,
                    g.school_year,
                    g.semester,
                    s.subject_code,
                    s.subject_name,
                    CONCAT(t.first_name, ' ', COALESCE(t.middle_name, ''), ' ', t.last_name) as teacher_name
                FROM {$this->table} g
                INNER JOIN subjects s ON g.subject_id = s.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                WHERE g.student_id = ?
                ORDER BY g.school_year DESC, g.semester DESC, s.subject_code ASC";
        
        $result = $this->db->raw($sql, [$student_id]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Insert grade
     */
    public function insert_grade($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * Update grade
     */
    public function update_grade($grade_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)
            ->where('id', '=', $grade_id)
            ->update($data);
    }

    /**
     * Delete grade
     */
    public function delete_grade($grade_id)
    {
        return $this->db->table($this->table)
            ->where('id', '=', $grade_id)
            ->delete();
    }

    /**
     * Get grades by teacher
     */
    public function get_grades_by_teacher($teacher_id)
    {
        $sql = "SELECT 
                    g.id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grade,
                    g.remarks,
                    g.status,
                    g.school_year,
                    g.semester,
                    CONCAT(st.first_name, ' ', COALESCE(st.middle_name, ''), ' ', st.last_name) as student_name,
                    st.grade_level,
                    st.section,
                    s.subject_code,
                    s.subject_name
                FROM {$this->table} g
                INNER JOIN students st ON g.student_id = st.id
                INNER JOIN subjects s ON g.subject_id = s.id
                WHERE g.teacher_id = ?
                ORDER BY g.school_year DESC, st.last_name ASC";
        
        $result = $this->db->raw($sql, [$teacher_id]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Get grades by subject
     */
    public function get_grades_by_subject($subject_id)
    {
        $sql = "SELECT 
                    g.id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grade,
                    g.remarks,
                    g.status,
                    g.school_year,
                    g.semester,
                    CONCAT(st.first_name, ' ', COALESCE(st.middle_name, ''), ' ', st.last_name) as student_name,
                    st.grade_level,
                    st.section,
                    CONCAT(t.first_name, ' ', COALESCE(t.middle_name, ''), ' ', t.last_name) as teacher_name
                FROM {$this->table} g
                INNER JOIN students st ON g.student_id = st.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                WHERE g.subject_id = ?
                ORDER BY g.school_year DESC, st.last_name ASC";
        
        $result = $this->db->raw($sql, [$subject_id]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Get pending grades for review (admin use)
     */
    public function get_pending_grades()
    {
        $sql = "SELECT 
                    g.id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grade,
                    g.remarks,
                    g.status,
                    g.school_year,
                    g.semester,
                    g.created_at,
                    CONCAT(st.first_name, ' ', COALESCE(st.middle_name, ''), ' ', st.last_name) as student_name,
                    st.grade_level,
                    st.section,
                    s.subject_code,
                    s.subject_name,
                    CONCAT(t.first_name, ' ', COALESCE(t.middle_name, ''), ' ', t.last_name) as teacher_name
                FROM {$this->table} g
                INNER JOIN students st ON g.student_id = st.id
                INNER JOIN subjects s ON g.subject_id = s.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                WHERE g.status IN ('Draft', 'Submitted')
                ORDER BY g.created_at DESC";
        
        $result = $this->db->raw($sql);
        
        return $result ? $result->fetchAll() : [];
    }
}
