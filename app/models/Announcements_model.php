<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Announcements_model extends Model
{
    public function get_announcements($limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                    a.*,
                    COALESCE(s.first_name, t.first_name) as author_first_name,
                    COALESCE(s.last_name, t.last_name) as author_last_name,
                    auth.role as author_role
                FROM announcements a
                LEFT JOIN auth ON a.author_id = auth.id
                LEFT JOIN students s ON auth.student_id = s.id
                LEFT JOIN teachers t ON auth.teacher_id = t.id
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->raw($sql, [$limit, $offset]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function get_announcement($id)
    {
        $sql = "SELECT 
                    a.*,
                    COALESCE(s.first_name, t.first_name) as author_first_name,
                    COALESCE(s.last_name, t.last_name) as author_last_name,
                    auth.role as author_role
                FROM announcements a
                LEFT JOIN auth ON a.author_id = auth.id
                LEFT JOIN students s ON auth.student_id = s.id
                LEFT JOIN teachers t ON auth.teacher_id = t.id
                WHERE a.id = ?";
        
        $stmt = $this->db->raw($sql, [$id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result[0] : null;
    }

    public function count_announcements()
    {
        $stmt = $this->db->raw("SELECT COUNT(*) as count FROM announcements");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return isset($result[0]['count']) ? $result[0]['count'] : 0;
    }

    public function create_announcement($data)
    {
        return $this->db->table('announcements')->insert($data);
    }

    public function update_announcement($id, $data)
    {
        return $this->db->table('announcements')
                        ->where('id', $id)
                        ->update($data);
    }

    public function delete_announcement($id)
    {
        return $this->db->table('announcements')
                        ->where('id', $id)
                        ->delete();
    }

    public function get_recipients_by_role($role_target)
    {
        $recipients = [];
        
        if ($role_target == 'all' || $role_target == 'student') {
            $sql = "SELECT 
                        s.first_name,
                        s.last_name,
                        s.email
                    FROM students s
                    WHERE s.deleted_at IS NULL 
                    AND s.email IS NOT NULL 
                    AND s.email != ''";
            
            $stmt = $this->db->raw($sql);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($students)) {
                $recipients = array_merge($recipients, $students);
            }
        }
        
        if ($role_target == 'all' || $role_target == 'teacher') {
            $sql = "SELECT 
                        t.first_name,
                        t.last_name,
                        t.email
                    FROM teachers t
                    WHERE t.email IS NOT NULL 
                    AND t.email != ''";
            
            $stmt = $this->db->raw($sql);
            $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($teachers)) {
                $recipients = array_merge($recipients, $teachers);
            }
        }
        
        return $recipients;
    }

    public function get_announcements_for_role($role, $limit = 5, $offset = 0)
    {
        $sql = "SELECT 
                    a.*,
                    COALESCE(s.first_name, t.first_name) as author_first_name,
                    COALESCE(s.last_name, t.last_name) as author_last_name
                FROM announcements a
                LEFT JOIN auth ON a.author_id = auth.id
                LEFT JOIN students s ON auth.student_id = s.id
                LEFT JOIN teachers t ON auth.teacher_id = t.id
                WHERE a.role_target IN ('all', ?)
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->raw($sql, [$role, $limit, $offset]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }
}
