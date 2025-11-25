<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Notification_model extends Model
{
    protected $table = "notifications";
    protected $primary_key = "id";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get notifications for a specific user (recipient_id = auth.id)
     */
    public function get_notifications($auth_id, $limit = 20)
    {
        $sql = "SELECT 
                    n.id,
                    n.message,
                    n.is_read,
                    n.created_at,
                    CONCAT(COALESCE(s.first_name, t.first_name, 'System'), ' ', 
                           COALESCE(s.last_name, t.last_name, '')) as sender_name,
                    a.role as sender_role
                FROM {$this->table} n
                LEFT JOIN auth a ON n.sender_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE n.recipient_id = ?
                ORDER BY n.created_at DESC
                LIMIT ?";
        
        $result = $this->db->raw($sql, [$auth_id, $limit]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Get unread notification count for a user
     */
    public function get_unread_count($auth_id)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE recipient_id = ? AND is_read = 0";
        $result = $this->db->raw($sql, [$auth_id]);
        
        if ($result) {
            $row = $result->fetch();
            return $row ? (int)$row['total'] : 0;
        }
        
        return 0;
    }

    /**
     * Mark a notification as read
     */
    public function mark_as_read($notification_id)
    {
        return $this->db->table($this->table)
            ->where('id', $notification_id)
            ->update(['is_read' => 1]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function mark_all_read($auth_id)
    {
        return $this->db->table($this->table)
            ->where('recipient_id', '=', $auth_id)
            ->where('is_read', '=', 0)
            ->update(['is_read' => 1]);
    }

    /**
     * Insert a new notification
     */
    public function insert_notification($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_read'] = 0; // Default to unread
        
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * Delete notification
     */
    public function delete_notification($notification_id)
    {
        return $this->db->table($this->table)
            ->where('id', '=', $notification_id)
            ->delete();
    }

    /**
     * Delete old notifications (older than specified days)
     */
    public function delete_old_notifications($days = 30)
    {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->db->table($this->table)
            ->where('created_at', '<', $cutoff_date)
            ->where('is_read', '=', 1)
            ->delete();
    }

    /**
     * Get notification by ID
     */
    public function get_notification($notification_id)
    {
        return $this->db->table($this->table)
            ->where('id', '=', $notification_id)
            ->get();
    }

    /**
     * Send notification to multiple recipients
     */
    public function send_bulk_notification($recipient_ids, $sender_id, $message)
    {
        $success_count = 0;
        
        foreach ($recipient_ids as $recipient_id) {
            $data = [
                'recipient_id' => $recipient_id,
                'sender_id' => $sender_id,
                'message' => $message
            ];
            
            if ($this->insert_notification($data)) {
                $success_count++;
            }
        }
        
        return $success_count;
    }

    /**
     * Get recent notifications (last 7 days)
     */
    public function get_recent_notifications($auth_id, $days = 7)
    {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = "SELECT 
                    n.id,
                    n.message,
                    n.is_read,
                    n.created_at,
                    CONCAT(COALESCE(s.first_name, t.first_name, 'System'), ' ', 
                           COALESCE(s.last_name, t.last_name, '')) as sender_name
                FROM {$this->table} n
                LEFT JOIN auth a ON n.sender_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE n.recipient_id = ?
                AND n.created_at >= ?
                ORDER BY n.created_at DESC";
        
        $result = $this->db->raw($sql, [$auth_id, $cutoff_date]);
        
        return $result ? $result->fetchAll() : [];
    }

    // ==================== THREADING METHODS ====================

    /**
     * Send a message (create new thread or reply to existing)
     */
    public function send_message($sender_id, $recipient_id, $subject, $message, $thread_id = null, $parent_id = null)
    {
        $data = [
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'subject' => $subject,
            'message' => $message,
            'thread_id' => $thread_id,
            'parent_id' => $parent_id,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->db->table($this->table)->insert($data);
        
        if ($result && !$thread_id) {
            // If this is a new thread, set thread_id to the message id itself
            $message_id = $this->db->last_id();
            $this->db->table($this->table)
                ->where('id', $message_id)
                ->update(['thread_id' => $message_id]);
            return $message_id;
        }
        
        return $result ? $this->db->last_id() : false;
    }

    /**
     * Get all message threads for a user (conversations grouped by thread)
     */
    public function get_threads($auth_id, $limit = 50)
    {
        $sql = "SELECT 
                    n.thread_id,
                    n.subject,
                    MAX(n.created_at) as last_message_at,
                    COUNT(n.id) as message_count,
                    SUM(CASE WHEN n.recipient_id = ? AND n.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                    -- Get the other party in the conversation
                    CASE 
                        WHEN n.sender_id = ? THEN n.recipient_id
                        ELSE n.sender_id
                    END as other_party_id,
                    -- Get other party details
                    CONCAT(COALESCE(s.first_name, t.first_name, 'System'), ' ', 
                           COALESCE(s.last_name, t.last_name, '')) as other_party_name,
                    a.role as other_party_role,
                    -- Get last message preview
                    (SELECT n2.message 
                     FROM {$this->table} n2 
                     WHERE n2.thread_id = n.thread_id 
                     ORDER BY n2.created_at DESC LIMIT 1) as last_message
                FROM {$this->table} n
                LEFT JOIN auth a ON (CASE WHEN n.sender_id = ? THEN n.recipient_id ELSE n.sender_id END) = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE (n.sender_id = ? OR n.recipient_id = ?)
                AND n.thread_id IS NOT NULL
                GROUP BY n.thread_id, n.subject, other_party_id, other_party_name, other_party_role
                ORDER BY last_message_at DESC
                LIMIT ?";
        
        $result = $this->db->raw($sql, [$auth_id, $auth_id, $auth_id, $auth_id, $auth_id, $limit]);
        
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Get all messages in a thread
     */
    public function get_thread_messages($thread_id, $auth_id = null)
    {
        $sql = "SELECT 
                    n.id,
                    n.sender_id,
                    n.recipient_id,
                    n.subject,
                    n.message,
                    n.parent_id,
                    n.is_read,
                    n.created_at,
                    CONCAT(COALESCE(s.first_name, t.first_name, 'System'), ' ', 
                           COALESCE(s.last_name, t.last_name, '')) as sender_name,
                    a.role as sender_role
                FROM {$this->table} n
                LEFT JOIN auth a ON n.sender_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE n.thread_id = ?
                ORDER BY n.created_at ASC";
        
        $result = $this->db->raw($sql, [$thread_id]);
        $messages = $result ? $result->fetchAll() : [];
        
        // Mark messages as read if auth_id is provided and user is recipient
        if ($auth_id && !empty($messages)) {
            $this->mark_thread_as_read($thread_id, $auth_id);
        }
        
        return $messages;
    }

    /**
     * Mark all messages in a thread as read for a specific user
     */
    public function mark_thread_as_read($thread_id, $auth_id)
    {
        return $this->db->table($this->table)
            ->where('thread_id', $thread_id)
            ->where('recipient_id', $auth_id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    /**
     * Get unread thread count for a user
     */
    public function get_unread_thread_count($auth_id)
    {
        $sql = "SELECT COUNT(DISTINCT thread_id) as total 
                FROM {$this->table} 
                WHERE recipient_id = ? 
                AND is_read = 0 
                AND thread_id IS NOT NULL";
        
        $result = $this->db->raw($sql, [$auth_id]);
        
        if ($result) {
            $row = $result->fetch();
            return $row ? (int)$row['total'] : 0;
        }
        
        return 0;
    }

    /**
     * Delete entire thread (all messages in the thread)
     */
    public function delete_thread($thread_id, $auth_id)
    {
        // Only delete if user is part of the conversation
        $sql = "DELETE FROM {$this->table} 
                WHERE thread_id = ? 
                AND (sender_id = ? OR recipient_id = ?)";
        
        $result = $this->db->raw($sql, [$thread_id, $auth_id, $auth_id]);
        
        return $result !== false;
    }

    /**
     * Get thread by ID (single message that started the thread)
     */
    public function get_thread($thread_id)
    {
        $sql = "SELECT 
                    n.*,
                    CONCAT(COALESCE(s.first_name, t.first_name, 'System'), ' ', 
                           COALESCE(s.last_name, t.last_name, '')) as sender_name,
                    a.role as sender_role
                FROM {$this->table} n
                LEFT JOIN auth a ON n.sender_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE n.id = ?";
        
        $result = $this->db->raw($sql, [$thread_id]);
        
        return $result ? $result->fetch() : null;
    }

    /**
     * Check if user has access to thread
     */
    public function has_thread_access($thread_id, $auth_id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE thread_id = ? 
                AND (sender_id = ? OR recipient_id = ?)
                LIMIT 1";
        
        $result = $this->db->raw($sql, [$thread_id, $auth_id, $auth_id]);
        
        if ($result) {
            $row = $result->fetch();
            return $row && (int)$row['total'] > 0;
        }
        
        return false;
    }

    /**
     * Get list of users that can be messaged (teachers and admin)
     */
    public function get_messageable_users($current_user_id, $role)
    {
        if ($role === 'teacher') {
            // Teachers can message admin and their students
            // Get admin users
            $sql = "SELECT 
                        a.id,
                        CONCAT(COALESCE(t.first_name, 'Admin'), ' ', 
                               COALESCE(t.last_name, '')) as name,
                        a.role,
                        'Admin' as user_type
                    FROM auth a
                    LEFT JOIN teachers t ON a.teacher_id = t.id
                    WHERE a.role IN ('admin', 'superadmin')
                    AND a.id != ?
                    
                    UNION
                    
                    SELECT DISTINCT
                        a.id,
                        CONCAT(s.first_name, ' ', s.last_name) as name,
                        a.role,
                        'Student' as user_type
                    FROM auth a
                    INNER JOIN students s ON a.student_id = s.id
                    INNER JOIN student_subjects ss ON ss.student_id = s.id
                    INNER JOIN subjects subj ON subj.id = ss.subject_id
                    INNER JOIN auth teacher_auth ON teacher_auth.id = ?
                    LEFT JOIN teachers teach ON teach.id = teacher_auth.teacher_id OR teach.id = teacher_auth.id
                    WHERE a.role = 'student'
                    AND subj.teacher_id = COALESCE(teach.id, teacher_auth.id)
                    AND (s.deleted_at IS NULL OR s.deleted_at = '')
                    
                    ORDER BY user_type, name";
                    
            $result = $this->db->raw($sql, [$current_user_id, $current_user_id]);
        } elseif ($role === 'student') {
            // Students can message their teachers and admin
            $sql = "SELECT 
                        a.id,
                        CONCAT(COALESCE(t.first_name, 'Admin'), ' ', 
                               COALESCE(t.last_name, '')) as name,
                        a.role,
                        'Admin' as user_type
                    FROM auth a
                    LEFT JOIN teachers t ON a.teacher_id = t.id
                    WHERE a.role IN ('admin', 'superadmin')
                    AND a.id != ?
                    
                    UNION
                    
                    SELECT DISTINCT
                        a.id,
                        CONCAT(t.first_name, ' ', t.last_name) as name,
                        a.role,
                        'Teacher' as user_type
                    FROM auth a
                    INNER JOIN teachers t ON a.teacher_id = t.id OR t.id = a.id
                    INNER JOIN subjects subj ON subj.teacher_id = t.id
                    INNER JOIN student_subjects ss ON ss.subject_id = subj.id
                    INNER JOIN auth student_auth ON student_auth.id = ?
                    INNER JOIN students s ON s.id = student_auth.student_id OR s.id = student_auth.id
                    WHERE a.role = 'teacher'
                    AND ss.student_id = s.id
                    
                    ORDER BY user_type, name";
                    
            $result = $this->db->raw($sql, [$current_user_id, $current_user_id]);
        } else {
            // Admin can message all teachers
            $sql = "SELECT 
                        a.id,
                        CONCAT(t.first_name, ' ', t.last_name) as name,
                        a.role
                    FROM auth a
                    INNER JOIN teachers t ON a.teacher_id = t.id
                    WHERE a.role = 'teacher'
                    AND a.id != ?
                    ORDER BY name";
                    
            $result = $this->db->raw($sql, [$current_user_id]);
        }
        
        return $result ? $result->fetchAll() : [];
    }
}
