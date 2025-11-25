<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Landing_model extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get public announcements (only target_role = 'all')
     * @return array
     */
    public function getPublicAnnouncements($limit = 5)
    {
        return $this->db->table('announcements a')
            ->select('a.id, a.title, a.content, a.created_at, COALESCE(s.first_name, t.first_name) as first_name, COALESCE(s.last_name, t.last_name) as last_name')
            ->left_join('auth u', 'a.author_id = u.id')
            ->left_join('students s', 'u.student_id = s.id')
            ->left_join('teachers t', 'u.teacher_id = t.id')
            ->where('a.role_target', 'all')
            ->order_by('a.created_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }

    /**
     * Get system statistics for landing page
     * @return array
     */
    public function getSystemStats()
    {
        $stats = [];
        
        // Total students
        $students = $this->db->table('auth')
            ->where('role', 'student')
            ->get_all();
        $stats['total_students'] = count($students);
        
        // Total teachers
        $teachers = $this->db->table('auth')
            ->where('role', 'teacher')
            ->get_all();
        $stats['total_teachers'] = count($teachers);
        
        // Total subjects
        $subjects = $this->db->table('subjects')->get_all();
        $stats['total_subjects'] = count($subjects);
        
        return $stats;
    }
}
