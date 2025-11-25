<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuditLog_model extends Model
{
    public function log_action($user_id, $action, $table_name, $record_id = null, $ip_address = null)
    {
        $data = [
            'user_id' => $user_id,
            'action' => $action,
            'table_name' => $table_name,
            'record_id' => $record_id,
            'ip_address' => $ip_address ?? $_SERVER['REMOTE_ADDR']
        ];
        
        return $this->db->table('audit_logs')->insert($data);
    }

    public function get_logs($filters = [], $limit = 50, $offset = 0)
    {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "al.timestamp >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "al.timestamp <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $where[] = "al.action LIKE ?";
            $params[] = '%' . $filters['action_type'] . '%';
        }
        
        if (!empty($filters['table_name'])) {
            $where[] = "al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT 
                    al.*,
                    a.username,
                    a.role,
                    COALESCE(s.first_name, t.first_name) as user_first_name,
                    COALESCE(s.last_name, t.last_name) as user_last_name
                FROM audit_logs al
                LEFT JOIN auth a ON al.user_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE $where_clause
                ORDER BY al.timestamp DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->raw($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function get_logs_count($filters = [])
    {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "timestamp >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "timestamp <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $where[] = "action LIKE ?";
            $params[] = '%' . $filters['action_type'] . '%';
        }
        
        if (!empty($filters['table_name'])) {
            $where[] = "table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE $where_clause";
        
        $stmt = $this->db->raw($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return isset($result[0]['count']) ? $result[0]['count'] : 0;
    }

    public function get_all_users()
    {
        $sql = "SELECT 
                    a.id,
                    a.username,
                    a.role,
                    COALESCE(s.first_name, t.first_name) as first_name,
                    COALESCE(s.last_name, t.last_name) as last_name
                FROM auth a
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                ORDER BY a.username";
        
        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_all_tables()
    {
        $stmt = $this->db->raw("SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_action_types()
    {
        $stmt = $this->db->raw("SELECT DISTINCT action FROM audit_logs ORDER BY action");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clear_old_logs($days = 90)
    {
        $sql = "DELETE FROM audit_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        try {
            $this->db->raw($sql, [$days]);
            return $this->db->affected_rows();
        } catch (Exception $e) {
            return false;
        }
    }
}
