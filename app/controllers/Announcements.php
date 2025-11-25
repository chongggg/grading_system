<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Announcements extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Announcements_model', 'announcements');
        $this->call->library(['session', 'form_validation']);
        $this->call->helper(['url', 'Mail_helper']);
        
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
        $data['page_title'] = 'Announcements';
        
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        // Get announcements
        $data['announcements'] = $this->announcements->get_announcements($per_page, $offset);
        $total = $this->announcements->count_announcements();
        
        // Pagination
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($total / $per_page);
        
        // Pass flash messages to view
        $data['flash_success'] = $this->session->flashdata('success');
        $data['flash_error'] = $this->session->flashdata('error');
        $data['flash_email_success'] = $this->session->flashdata('email_success');
        $data['flash_email_warning'] = $this->session->flashdata('email_warning');
        
        $this->call->view('admin/announcements/index', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Create Announcement';
        $data['errors'] = [];
        
        if ($this->form_validation->submitted()) {
            // Validate fields using chaining
            $this->form_validation
                ->name('title')
                ->required('Title is required')
                ->max_length(150, 'Title must not exceed 150 characters');
            
            $this->form_validation
                ->name('content')
                ->required('Content is required');
            
            $this->form_validation
                ->name('role_target')
                ->required('Target audience is required')
                ->in_list('all,student,teacher', 'Invalid target audience selected');
            
            if ($this->form_validation->run()) {
                $announcement_data = [
                    'author_id' => $this->session->userdata('user_id'),
                    'title' => $this->io->post('title'),
                    'content' => $this->io->post('content'),
                    'role_target' => $this->io->post('role_target')
                ];
                
                $announcement_id = $this->announcements->create_announcement($announcement_data);
                
                if ($announcement_id) {
                    // Log action
                    $this->log_action('Created announcement', 'announcements', $announcement_id);
                    
                    // Send email notifications
                    $this->send_notifications($announcement_data);
                    
                    $this->session->set_flashdata('success', 'Announcement created successfully and notifications sent!');
                    redirect('announcements');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create announcement.');
                }
            } else {
                // Get all validation errors
                $data['validation_errors'] = $this->form_validation->errors();
            }
        }
        
        $this->call->view('admin/announcements/create', $data);
    }

    private function send_notifications($announcement_data)
    {
        $recipients = $this->announcements->get_recipients_by_role($announcement_data['role_target']);
        
        $success_count = 0;
        $fail_count = 0;
        
        foreach ($recipients as $recipient) {
            $name = $recipient['first_name'] . ' ' . $recipient['last_name'];
            $email = $recipient['email'];
            $subject = "New Announcement: " . $announcement_data['title'];
            
            // Build HTML email message
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
        .announcement-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .announcement-box .title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .announcement-box .content-text { color: #555; line-height: 1.8; white-space: pre-wrap; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎓 Grading Management System</h1>
            <p>New Announcement Posted</p>
        </div>
        <div class='content'>
            <h2>Hello, {$name}!</h2>
            <p>A new announcement has been posted for you:</p>
            
            <div class='announcement-box'>
                <div class='title'>📢 {$announcement_data['title']}</div>
                <div class='content-text'>{$announcement_data['content']}</div>
            </div>
            
            <p>Please log in to your dashboard to view more details and other announcements.</p>
            
            <center>
                <a href='" . BASE_URL . "' class='button'>View Dashboard</a>
            </center>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
            
            $result = mail_helper($name, $email, $subject, $message);
            
            if ($result === true) {
                $success_count++;
            } else {
                $fail_count++;
                // Log failed email
                $this->log_action("Failed to send email to {$email}: {$result}", 'announcements', null);
            }
        }
        
        // Set flashdata with email stats
        if ($success_count > 0) {
            $this->session->set_flashdata('email_success', "Successfully sent {$success_count} notification(s).");
        }
        if ($fail_count > 0) {
            $this->session->set_flashdata('email_warning', "Failed to send {$fail_count} notification(s).");
        }
    }

    public function edit($id)
    {
        $data['page_title'] = 'Edit Announcement';
        $data['announcement'] = $this->announcements->get_announcement($id);
        $data['errors'] = [];
        
        if (!$data['announcement']) {
            $this->session->set_flashdata('error', 'Announcement not found.');
            redirect('announcements');
        }
        
        if ($this->form_validation->submitted()) {
            // Validate fields using chaining
            $this->form_validation
                ->name('title')
                ->required('Title is required')
                ->max_length(150, 'Title must not exceed 150 characters');
            
            $this->form_validation
                ->name('content')
                ->required('Content is required');
            
            $this->form_validation
                ->name('role_target')
                ->required('Target audience is required')
                ->in_list('all,student,teacher', 'Invalid target audience selected');
            
            if ($this->form_validation->run()) {
                $update_data = [
                    'title' => $this->io->post('title'),
                    'content' => $this->io->post('content'),
                    'role_target' => $this->io->post('role_target')
                ];
                
                if ($this->announcements->update_announcement($id, $update_data)) {
                    // Log action
                    $this->log_action('Updated announcement', 'announcements', $id);
                    
                    $this->session->set_flashdata('success', 'Announcement updated successfully!');
                    redirect('announcements');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update announcement.');
                }
            } else {
                // Get all validation errors
                $data['validation_errors'] = $this->form_validation->errors();
            }
        }
        
        $this->call->view('admin/announcements/edit', $data);
    }

    public function delete($id)
    {
        if ($this->announcements->delete_announcement($id)) {
            // Log action
            $this->log_action('Deleted announcement', 'announcements', $id);
            
            $this->session->set_flashdata('success', 'Announcement deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete announcement.');
        }
        
        redirect('announcements');
    }

    public function view($id)
    {
        $data['page_title'] = 'View Announcement';
        $data['announcement'] = $this->announcements->get_announcement($id);
        
        if (!$data['announcement']) {
            $this->session->set_flashdata('error', 'Announcement not found.');
            redirect('announcements');
        }
        
        $this->call->view('admin/announcements/view', $data);
    }

    private function log_action($action, $table, $record_id)
    {
        $this->call->model('AuditLog_model', 'audit');
        $this->audit->log_action(
            $this->session->userdata('user_id'),
            $action,
            $table,
            $record_id,
            $_SERVER['REMOTE_ADDR']
        );
    }
}
