<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuditLogs extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('AuditLog_model', 'audit');
        $this->call->library('session');
        $this->call->helper('url');
        
        // Check if user is admin
        $this->check_admin();
    }

    private function check_admin()
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Access denied. Admin privileges required.');
            redirect('auth/login');
        }
    }

    public function index($page = 1)
    {
        $data['page_title'] = 'Audit Logs';
        
        $per_page = 50;
        $offset = ($page - 1) * $per_page;
        
        // Get filter parameters with default empty strings
        $filters = [
            'date_from' => $this->io->get('date_from') ?: '',
            'date_to' => $this->io->get('date_to') ?: '',
            'user_id' => $this->io->get('user_id') ?: '',
            'action_type' => $this->io->get('action_type') ?: '',
            'table_name' => $this->io->get('table_name') ?: ''
        ];
        
        // Create filtered version for queries (remove empty values)
        $query_filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        // Get logs
        $data['logs'] = $this->audit->get_logs($query_filters, $per_page, $offset);
        $total = $this->audit->get_logs_count($query_filters);
        
        // Pagination
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($total / $per_page);
        
        // Get filter options
        $data['users'] = $this->audit->get_all_users();
        $data['tables'] = $this->audit->get_all_tables();
        $data['action_types'] = $this->audit->get_action_types();
        $data['filters'] = $filters; // Pass full array with all keys
        
        // Pass flash messages to view
        $data['flash_success'] = $this->session->flashdata('success');
        $data['flash_error'] = $this->session->flashdata('error');
        
        $this->call->view('admin/audit_logs/index', $data);
    }

    public function clear_old_logs()
    {
        // Clear logs older than 90 days
        $days = $this->io->post('days') ?? 90;
        
        $deleted = $this->audit->clear_old_logs($days);
        
        if ($deleted !== false) {
            $this->session->set_flashdata('success', "Successfully cleared {$deleted} old log(s).");
        } else {
            $this->session->set_flashdata('error', 'Failed to clear logs.');
        }
        
        redirect('auditlogs');
    }
}
