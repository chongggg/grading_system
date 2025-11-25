<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class PendingAccounts extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Auth_model');
        $this->call->library('session');
        $this->call->helper('url');
        $this->call->helper('Mail_helper');
        
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

    public function index()
    {
        $data['page'] = 'Pending Accounts';
        $data['page_title'] = 'Pending Student Accounts';
        
        // Get all pending accounts with 'user' role using QueryBuilder
        $data['pending_users'] = $this->db->table('auth a')
            ->select('a.id, a.username, a.created_at, s.first_name, s.last_name, s.email, s.id as student_id')
            ->left_join('students s', 'a.student_id = s.id')
            ->where('a.role', 'user')
            ->order_by('a.created_at', 'DESC')
            ->get_all();
        
        $this->call->view('admin/pending_accounts/index', $data);
    }

    public function approve($user_id)
    {
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Invalid user ID.');
            redirect('pendingaccounts');
            return;
        }

        // Get user details before approval
        $user = $this->db->table('auth a')
            ->select('a.*, s.first_name, s.last_name, s.email')
            ->left_join('students s', 'a.student_id = s.id')
            ->where('a.id', $user_id)
            ->where('a.role', 'user')
            ->get();

        if (!$user) {
            $this->session->set_flashdata('error', 'User not found or already processed.');
            redirect('pendingaccounts');
            return;
        }

        // Approve user - change role to 'student'
        $updated = $this->db->table('auth')
            ->where('id', $user_id)
            ->update(['role' => 'student', 'updated_at' => date('Y-m-d H:i:s')]);

        if ($updated) {
            // Send approval email
            $this->send_approval_email($user);
            
            // Log action
            $this->log_action($user_id, 'APPROVE_STUDENT_ACCOUNT', 'auth', $user_id);
            
            $this->session->set_flashdata('success', 'Student account approved successfully! Email notification sent.');
        } else {
            $this->session->set_flashdata('error', 'Failed to approve account.');
        }

        redirect('pendingaccounts');
    }

    public function reject($user_id)
    {
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Invalid user ID.');
            redirect('pendingaccounts');
            return;
        }

        // Get user details before rejection
        $user = $this->db->table('auth a')
            ->select('a.*, s.first_name, s.last_name, s.email')
            ->left_join('students s', 'a.student_id = s.id')
            ->where('a.id', $user_id)
            ->where('a.role', 'user')
            ->get();

        if (!$user) {
            $this->session->set_flashdata('error', 'User not found or already processed.');
            redirect('pendingaccounts');
            return;
        }

        // Delete from auth table
        $deleted_auth = $this->db->table('auth')
            ->where('id', $user_id)
            ->delete();

        // Delete from students table if exists
        if ($user['student_id']) {
            $this->db->table('students')
                ->where('id', $user['student_id'])
                ->delete();
        }

        if ($deleted_auth) {
            // Send rejection email
            $this->send_rejection_email($user);
            
            // Log action
            $this->log_action($user_id, 'REJECT_STUDENT_ACCOUNT', 'auth', $user_id);
            
            $this->session->set_flashdata('success', 'Student account rejected and deleted. Email notification sent.');
        } else {
            $this->session->set_flashdata('error', 'Failed to reject account.');
        }

        redirect('pendingaccounts');
    }

    private function send_approval_email($user)
    {
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $email = $user['email'];
        $subject = 'Account Approved - Grading Management System';
        $login_url = site_url('auth/login');
        $message = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .content h2 { color: #333; margin-top: 0; }
        .content p { color: #555; line-height: 1.6; }
        .success-box { background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .success-box .label { font-weight: bold; color: #333; }
        .success-box .value { font-family: monospace; color: #10b981; font-size: 16px; }
        .button { display: inline-block; padding: 12px 30px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>✅ Account Approved!</h1>
            <p>Grading Management System</p>
        </div>
        <div class='content'>
            <h2>Hello, {$user['first_name']} {$user['last_name']}!</h2>
            <p>Great news! Your student account has been approved by the administrator.</p>
            
            <div class='success-box'>
                <p><span class='label'>Username:</span> <span class='value'>{$user['username']}</span></p>
                <p style='margin-top: 10px; color: #555;'>You can now log in to the system using your registered credentials.</p>
            </div>
            
            <center>
                <a href='{$login_url}' class='button'>Log In Now</a>
            </center>
            
            <p style='text-align: center; color: #888; font-size: 14px;'>Or copy this link: {$login_url}</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

        try {
            mail_helper($name, $email, $subject, $message);
        } catch (Exception $e) {
            // Log email error but don't stop the process
            error_log('Email sending failed: ' . $e->getMessage());
        }
    }

    private function send_rejection_email($user)
    {
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $email = $user['email'];
        $subject = 'Account Registration - Grading Management System';
        $message = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .content h2 { color: #333; margin-top: 0; }
        .content p { color: #555; line-height: 1.6; }
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>📧 Registration Status</h1>
            <p>Grading Management System</p>
        </div>
        <div class='content'>
            <h2>Hello, {$user['first_name']} {$user['last_name']}</h2>
            <p>Thank you for your interest in our Grading Management System.</p>
            
            <div class='info-box'>
                <p style='margin: 0;'><strong>ℹ️ Registration Status:</strong></p>
                <p style='margin: 10px 0 0;'>Unfortunately, your account registration could not be approved at this time.</p>
            </div>
            
            <p>If you believe this is an error or have questions, please contact the system administrator for assistance.</p>
            
            <p>Thank you for your understanding.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

        try {
            mail_helper($name, $email, $subject, $message);
        } catch (Exception $e) {
            // Log email error but don't stop the process
            error_log('Email sending failed: ' . $e->getMessage());
        }
    }

    private function log_action($user_id, $action, $table, $record_id)
    {
        $admin_id = $this->session->userdata('user_id');
        
        $this->db->table('audit_logs')->insert([
            'user_id' => $admin_id,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $record_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
