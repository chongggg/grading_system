<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Reports_model extends Model
{
    public function get_summary()
    {
        $summary = [];
        
        // Total students
        $stmt = $this->db->raw("SELECT COUNT(*) as count FROM students WHERE deleted_at IS NULL");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary['total_students'] = $result[0]['count'];
        
        // Total teachers
        $stmt = $this->db->raw("SELECT COUNT(*) as count FROM teachers");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary['total_teachers'] = $result[0]['count'];
        
        // Total subjects
        $stmt = $this->db->raw("SELECT COUNT(*) as count FROM subjects");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary['total_subjects'] = $result[0]['count'];
        
        // Average grade
        $stmt = $this->db->raw("SELECT AVG(final_grade) as avg_grade FROM grades WHERE final_grade IS NOT NULL");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary['avg_grade'] = $result[0]['avg_grade'] ?? 0;
        
        return $summary;
    }

    public function get_grade_statistics($filters = [])
    {
        $where = ["g.final_grade IS NOT NULL"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "g.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "g.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['subject_id'])) {
            $where[] = "g.subject_id = ?";
            $params[] = $filters['subject_id'];
        }
        
        if (!empty($filters['teacher_id'])) {
            $where[] = "g.teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }
        
        if (!empty($filters['section'])) {
            $where[] = "s.section_id = ?";
            $params[] = $filters['section'];
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT 
                    sub.subject_name,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name,
                    COUNT(DISTINCT g.student_id) as total_students,
                    AVG(g.final_grade) as avg_grade,
                    SUM(CASE WHEN g.status = 'Submitted' THEN 1 ELSE 0 END) as submitted_count,
                    COUNT(*) as total_count,
                    ROUND((SUM(CASE WHEN g.status = 'Submitted' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as submission_percentage
                FROM grades g
                INNER JOIN subjects sub ON g.subject_id = sub.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                INNER JOIN students s ON g.student_id = s.id
                WHERE $where_clause
                GROUP BY sub.id, t.id
                ORDER BY avg_grade DESC";
        
        $stmt = $this->db->raw($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function get_submission_status($filters = [])
    {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['subject_id'])) {
            $where[] = "subject_id = ?";
            $params[] = $filters['subject_id'];
        }
        
        if (!empty($filters['teacher_id'])) {
            $where[] = "teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM grades
                WHERE $where_clause
                GROUP BY status";
        
        $stmt = $this->db->raw($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function get_teacher_performance($filters = [])
    {
        $where = ["g.final_grade IS NOT NULL"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "g.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "g.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['teacher_id'])) {
            $where[] = "g.teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT 
                    t.id,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name,
                    COUNT(DISTINCT g.student_id) as students_handled,
                    AVG(g.final_grade) as avg_grade,
                    COUNT(*) as total_grades
                FROM grades g
                INNER JOIN teachers t ON g.teacher_id = t.id
                WHERE $where_clause
                GROUP BY t.id
                ORDER BY avg_grade DESC";
        
        $stmt = $this->db->raw($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function get_all_subjects()
    {
        $result = $this->db->table('subjects')->get_all();
        return is_array($result) ? $result : [];
    }

    public function get_all_teachers()
    {
        $result = $this->db->table('teachers')->get_all();
        return is_array($result) ? $result : [];
    }

    public function get_all_sections()
    {
        $stmt = $this->db->raw("SELECT id, section_name FROM sections ORDER BY section_name");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }
}
