<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Auth_model');
        $this->call->model('Student_model');
    $this->call->model('Admin_model');
        $this->call->library('session');
        $this->call->library('upload');
        $this->call->helper('url');
        $this->call->helper('form');
    }

    /**
     * Show login form
     */
    public function login()
    {
        // If user is already logged in, redirect to appropriate page
        if ($this->session->userdata('logged_in')) {
            $role = $this->session->userdata('role');
            if ($role === 'admin') {
                redirect('students');
            } else {
                redirect('auth/profile');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->process_login();
        } else {
            $this->call->view('auth/login');
        }
    }

    /**
     * Process login
     */
    private function process_login()
    {
        $username = trim($this->io->post('username'));
        $password = $this->io->post('password');

        // Basic validation
        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Username and password are required!');
            $this->call->view('auth/login');
            return;
        }

        // Get auth record with student details
        $auth = $this->Auth_model->get_auth_with_student($username);

        if (!$auth) {
            $this->session->set_flashdata('error', 'Invalid username or password!');
            $this->call->view('auth/login');
            return;
        }

        // Verify password
        if (!password_verify($password, $auth['password'])) {
            $this->session->set_flashdata('error', 'Invalid username or password!');
            $this->call->view('auth/login');
            return;
        }

        // Set session data
        $this->session->set_userdata([
            'user_id' => $auth['id'],
            'student_id' => $auth['student_id'],
            'username' => $auth['username'],
            'first_name' => $auth['first_name'],
            'last_name' => $auth['last_name'],
            'email' => $auth['email'],
            'role' => $auth['role'],
            'profile_image' => $auth['profile_image'] ?: $auth['student_profile_image'],
            'logged_in' => true
        ]);

        // Redirect based on role
        switch ($auth['role']) {
            case 'admin':
                redirect('admin/');
                break;
            case 'teacher':
                redirect('teacher/subjects');
                break;
            case 'student':
                redirect('student/dashboard');
                break;
            case 'user':
                // Pending approval - redirect to waiting page
                redirect('landing/waiting');
                break;
            default:
                redirect('auth/profile');
        }
    }

    /**
     * Send 2FA code via email
     */
    private function send_2fa_code($email, $first_name, $code)
    {
        $subject = 'Two-Factor Authentication Code';
        
        $message = "<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #3b82f6, #6366f1); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .code-box { background: white; border: 2px dashed #3b82f6; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .code { font-size: 32px; font-weight: bold; color: #3b82f6; letter-spacing: 8px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎓 Grading Management System</h1>
            <p>Two-Factor Authentication</p>
        </div>
        <div class='content'>
            <h2>Hello, {$first_name}!</h2>
            <p>To complete your authentication, please use the following verification code:</p>
            
            <div class='code-box'>
                <div class='code'>{$code}</div>
            </div>
            
            <div class='warning'>
                <strong>⚠️ Important:</strong>
                <ul>
                    <li>This code will expire in <strong>10 minutes</strong></li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this code, please ignore this email</li>
                </ul>
            </div>
            
            <p>For security reasons, please keep this code confidential.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
        
        return mail_helper($first_name, $email, $subject, $message);
    }

    /**
     * Verify 2FA code (GET/POST)
     */
    public function verify_2fa()
    {
        // Check if pending 2FA session exists
        if (!$this->session->userdata('pending_2fa_user_id')) {
            // If it's an AJAX request, return JSON error
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid session. Please login again.']);
                exit;
            }
            $this->session->set_flashdata('error', 'Invalid session. Please login again.');
            redirect('auth/login');
            return;
        }

        // Handle GET request - show verification form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $last_sent = $this->session->userdata('pending_2fa_last_sent');
            $data = [
                'email' => $this->session->userdata('pending_2fa_email'),
                'last_sent' => $last_sent ? $last_sent : time()
            ];
            $this->call->view('auth/verify_2fa', $data);
            return;
        }

        // Handle POST request - verify code
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            $code = trim($this->io->post('code'));
            $user_id = $this->session->userdata('pending_2fa_user_id');

            if (empty($code)) {
                echo json_encode(['success' => false, 'message' => 'Please enter the verification code']);
                exit;
            }

            // Verify code from database
            try {
                $current_time = date('Y-m-d H:i:s');
                $verification = $this->db->table('two_factor_codes')
                    ->where('user_id', '=', $user_id)
                    ->where('code', '=', $code)
                    ->where('used', '=', 0)
                    ->where('expires_at', '>', $current_time)
                    ->order_by('created_at', 'DESC')
                    ->get();

                if (!$verification) {
                    echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                    exit;
                }

                // Mark code as used
                $this->db->table('two_factor_codes')
                    ->where('id', $verification['id'])
                    ->update(['used' => 1]);

                // Complete login - transfer pending session data
                $this->session->set_userdata([
                    'user_id' => $this->session->userdata('pending_2fa_user_id'),
                    'student_id' => $this->session->userdata('pending_2fa_student_id'),
                    'username' => $this->session->userdata('pending_2fa_username'),
                    'first_name' => $this->session->userdata('pending_2fa_first_name'),
                    'last_name' => $this->session->userdata('pending_2fa_last_name'),
                    'email' => $this->session->userdata('pending_2fa_email'),
                    'role' => $this->session->userdata('pending_2fa_role'),
                    'profile_image' => $this->session->userdata('pending_2fa_profile_image'),
                    'logged_in' => true
                ]);

                // Clear pending 2FA session data
                $this->session->unset_userdata([
                    'pending_2fa_user_id',
                    'pending_2fa_student_id',
                    'pending_2fa_username',
                    'pending_2fa_first_name',
                    'pending_2fa_last_name',
                    'pending_2fa_email',
                    'pending_2fa_role',
                    'pending_2fa_profile_image'
                ]);

                // Determine redirect URL based on role
                $role = $this->session->userdata('role');
                $redirect_url = '';
                
                switch ($role) {
                    case 'admin':
                        $redirect_url = site_url('admin/');
                        break;
                    case 'teacher':
                        $redirect_url = site_url('teacher/subjects');
                        break;
                    case 'student':
                        $redirect_url = site_url('student/dashboard');
                        break;
                    case 'user':
                        $redirect_url = site_url('landing/waiting');
                        break;
                    default:
                        $redirect_url = site_url('auth/profile');
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Verification successful! Redirecting...',
                    'redirect' => $redirect_url
                ]);
                exit;
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        }
    }

    /**
     * Resend 2FA code
     */
    public function resend_2fa()
    {
        header('Content-Type: application/json');
        
        // Check if pending 2FA session exists
        if (!$this->session->userdata('pending_2fa_user_id')) {
            echo json_encode(['success' => false, 'message' => 'Invalid session. Please login again.']);
            exit;
        }

        // Check cooldown period (60 seconds)
        $last_sent = $this->session->userdata('pending_2fa_last_sent');
        if ($last_sent && (time() - $last_sent) < 60) {
            $remaining = 60 - (time() - $last_sent);
            echo json_encode(['success' => false, 'message' => "Please wait {$remaining} seconds before resending."]);
            exit;
        }

        $user_id = $this->session->userdata('pending_2fa_user_id');
        $email = $this->session->userdata('pending_2fa_email');
        $first_name = $this->session->userdata('pending_2fa_first_name');

        // Generate new code
        $code = sprintf('%06d', mt_rand(0, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        try {
            // Mark old codes as used
            $this->db->table('two_factor_codes')
                ->where('user_id', $user_id)
                ->where('used', 0)
                ->update(['used' => 1]);
            
            // Save new code
            $this->db->table('two_factor_codes')->insert([
                'user_id' => $user_id,
                'code' => $code,
                'expires_at' => $expires_at,
                'used' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send new code via email
            $this->call->helper('mail');
            $email_sent = $this->send_2fa_code($email, $first_name, $code);
            
            if ($email_sent && !is_string($email_sent)) {
                // Update last sent timestamp
                $this->session->set_userdata('pending_2fa_last_sent', time());
                echo json_encode(['success' => true, 'message' => 'New verification code sent to your email']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send verification code. Please try again.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->session->userdata('user_id')) {
            redirect('');
        }

        if ($_POST) {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];

            if (empty($first_name)) $errors[] = 'First name is required';
            if (empty($last_name)) $errors[] = 'Last name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (empty($username)) $errors[] = 'Username is required';
            if (empty($password)) $errors[] = 'Password is required';
            if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
            if (empty(trim($_POST['grade_level'] ?? ''))) $errors[] = 'Grade level is required';

            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            // Check if email already exists
            if ($this->Student_model->email_exists($email)) {
                $errors[] = 'Email already exists';
            }

            // Check if username already exists
            if ($this->Auth_model->username_exists($username)) {
                $errors[] = 'Username already exists';
            }

            if (empty($errors)) {
                // Prepare data
                $student_data = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'grade_level' => trim($_POST['grade_level'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $auth_data = [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'user', // Changed from 'student' to 'user' - requires admin approval
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Register user
                $result = $this->Auth_model->register($student_data, $auth_data);

                if ($result) {
                    // Get the newly created auth record
                    $auth = $this->Auth_model->get_auth_with_student($username);
                    if ($auth) {
                        // Generate 2FA code
                        $code = sprintf('%06d', mt_rand(0, 999999));
                        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                        
                        // Save code to database
                        $this->db->table('two_factor_codes')->insert([
                            'user_id' => $auth['id'],
                            'code' => $code,
                            'expires_at' => $expires_at,
                            'used' => 0,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Send 2FA code via email
                        $this->call->helper('mail');
                        $email_sent = $this->send_2fa_code($email, $first_name, $code);
                        
                        if (!$email_sent || is_string($email_sent)) {
                            $this->session->set_flashdata('error', 'Registration successful but failed to send verification code. Please login to continue.');
                            redirect('auth/login');
                            return;
                        }
                        
                        // Store temporary session data for 2FA verification
                        $this->session->set_userdata([
                            'pending_2fa_user_id' => $auth['id'],
                            'pending_2fa_student_id' => $result,
                            'pending_2fa_username' => $auth['username'],
                            'pending_2fa_first_name' => $first_name,
                            'pending_2fa_last_name' => $last_name,
                            'pending_2fa_email' => $email,
                            'pending_2fa_role' => 'user',
                            'pending_2fa_profile_image' => '',
                            'pending_2fa_last_sent' => time()
                        ]);
                        
                        // Redirect to 2FA verification page
                        redirect('auth/verify_2fa');
                    } else {
                        $this->session->set_flashdata('success', 'Registration successful! Please wait for admin approval.');
                        redirect('auth/login');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Registration failed. Please try again.');
                }
            } else {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            }
        }

        $this->call->view('auth/register');
    }


    /**
     * Profile page
     */
    public function profile()
    {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $auth_user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        
        // Debug: Check if user_id exists in session
        if (!$auth_user_id) {
            $this->session->set_flashdata('error', 'Session expired. Please login again.');
            redirect('auth/login');
        }
        
        // Get user data based on role
        if ($role === 'teacher') {
            $data['user'] = $this->Auth_model->get_teacher_profile($auth_user_id);
            $data['is_teacher'] = true;
            $data['page'] = 'Profile';
        } else {
            // Student or admin
            $student_id = $this->session->userdata('student_id');
            if (!$student_id) {
                $this->session->set_flashdata('error', 'Session expired. Please login again.');
                redirect('auth/login');
            }
            $data['user'] = $this->Auth_model->get_auth_with_student_by_id($student_id);
            $data['is_teacher'] = false;
            $data['page'] = 'Profile';
        }
        
        // Debug: Check if user data was found
        if (!$data['user']) {
            $this->session->set_flashdata('error', 'User data not found. Please login again.');
            redirect('auth/login');
        }
        
        // Get auth data for profile image
        $data['auth'] = $this->Auth_model->get_auth_by_id($auth_user_id);

        if ($_POST) {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');

            // Validation
            $errors = [];

            if (empty($first_name)) $errors[] = 'First name is required';
            if (empty($last_name)) $errors[] = 'Last name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (empty($username)) $errors[] = 'Username is required';

            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            // Check if email already exists (excluding current user)
            if ($role === 'teacher') {
                if ($this->Auth_model->teacher_email_exists($email, $data['user']['teacher_id'])) {
                    $errors[] = 'Email already exists';
                }
            } else {
                if ($this->Student_model->email_exists($email, $data['user']['id'])) {
                    $errors[] = 'Email already exists';
                }
            }

            // Check if username already exists (excluding current user)
            if ($this->Auth_model->username_exists($username, $auth_user_id)) {
                $errors[] = 'Username already exists';
            }

            if (empty($errors)) {
                $auth_data = [
                    'username' => $username,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($role === 'teacher') {
                    // Update teacher profile
                    $teacher_data = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'contact_number' => $contact_number,
                        'specialization' => $specialization,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($this->Auth_model->update_teacher_profile($data['user']['teacher_id'], $teacher_data, $auth_data, $auth_user_id)) {
                        // Update session data
                        $this->session->set_userdata([
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'username' => $username
                        ]);

                        $this->session->set_flashdata('success', 'Profile updated successfully!');
                        redirect('auth/profile');
                    } else {
                        $this->session->set_flashdata('error', 'Failed to update profile. Please try again.');
                    }
                } else {
                    // Update student profile
                    $student_data = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($this->Auth_model->update_profile($data['user']['id'], $student_data, $auth_data)) {
                        // Update session data
                        $this->session->set_userdata([
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'username' => $username
                        ]);

                        $this->session->set_flashdata('success', 'Profile updated successfully!');
                        redirect('auth/profile');
                    } else {
                        $this->session->set_flashdata('error', 'Failed to update profile. Please try again.');
                    }
                }
            } else {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            }
        }

        // Load role-specific profile view with header/footer
        if ($role === 'teacher') {
            $this->call->view('teacher/profile', $data);
        } elseif ($role === 'admin') {
            $this->call->view('admin/profile', $data);
        } else {
            $this->call->view('auth/profile', $data);
        }
    }

    /**
     * Upload profile image
     */
    public function upload_image()
    {
        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
        }

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = $this->session->userdata('user_id');
            
            // Initialize upload
            $this->call->library('upload');
            
            // Set the file to upload
            $this->upload->file = $_FILES['profile_image'];
            
            $this->upload
                ->max_size(5) // 5MB max
                ->min_size(0.1) // 0.1MB min
                ->set_dir('public/uploads/')
                ->allowed_extensions(['jpg', 'jpeg', 'png', 'gif'])
                ->allowed_mimes(['image/jpeg', 'image/png', 'image/gif'])
                ->is_image()
                ->encrypt_name();

            if ($this->upload->do_upload()) {
                $filename = $this->upload->get_filename();
                
                // Update database
                $student_data = [
                    'profile_image' => $filename,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $auth_data = [
                    'profile_image' => $filename,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->Auth_model->update_profile($user_id, $student_data, $auth_data)) {
                    // Update session
                    $this->session->set_userdata('profile_image', $filename);
                    $this->session->set_flashdata('success', 'Profile image updated successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update profile image.');
                }
            } else {
                $errors = $this->upload->get_errors();
                $this->session->set_flashdata('error', implode('<br>', $errors));
            }
        } else {
            $this->session->set_flashdata('error', 'Please select a valid image file.');
        }

        redirect('auth/profile');
    }

    /**
     * Change password
     */
    public function change_password()
    {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        if ($_POST) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $user_id = $this->session->userdata('student_id');
            $user = $this->Auth_model->get_auth_with_student_by_id($user_id);

            // Validation
            $errors = [];

            if (empty($current_password)) $errors[] = 'Current password is required';
            if (empty($new_password)) $errors[] = 'New password is required';
            if ($new_password !== $confirm_password) $errors[] = 'New passwords do not match';

            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = 'Current password is incorrect';
            }

            if (empty($errors)) {
                if ($this->Auth_model->update_password($user_id, $new_password)) {
                    $this->session->set_flashdata('success', 'Password changed successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Failed to change password. Please try again.');
                }
            } else {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            }
        }

        redirect('auth/profile');
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->unset_userdata(['user_id', 'student_id', 'username', 'first_name', 'last_name', 'email', 'role', 'profile_image', 'logged_in']);
        $this->session->set_flashdata('success', 'You have been logged out successfully!');
        redirect('auth/login');
    }

    /**
     * Admin dashboard
     */
    public function admin_dashboard()
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        $data = [
            'page' => 'Dashboard',
            'total_students' => $this->Auth_model->count_students(),
            'total_teachers' => $this->Auth_model->count_teachers(),
            'total_subjects' => $this->Auth_model->count_subjects(),
            'recent_logs' => $this->Auth_model->get_recent_logs(10),
            'recent_announcements' => $this->Auth_model->get_recent_announcements(5),
            'recent_grades' => $this->Auth_model->get_recent_grades(5)
        ];

        $this->call->view('admin/dashboard', $data);
    }

    /**
     * Admin subjects list
     */
    public function admin_subjects()
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        $filters = [];
        if (isset($_GET['grade_level']) && $_GET['grade_level'] !== '') {
            $filters['grade_level'] = $_GET['grade_level'];
        }
        if (isset($_GET['semester']) && $_GET['semester'] !== '') {
            $filters['semester'] = $_GET['semester'];
        }

        $data['page'] = 'Subjects';
        $data['subjects'] = $this->Auth_model->get_all_subjects($filters);
        $this->call->view('admin/subjects', $data);
    }

    /**
     * Create Subject (GET/POST)
     */
    public function create_subject()
    {
        // Only allow admin
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        // Show form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data['page'] = 'Add Subject';
            // Get all teachers
            $teachers = $this->Admin_model->getAllTeachers() ?: [];
            $data['teachers'] = $teachers;
            return $this->call->view('admin/add_subject', $data);
        }

        // Process form on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject_code = trim($_POST['subject_code'] ?? '');
            $subject_name = trim($_POST['subject_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $grade_level = trim($_POST['grade_level'] ?? '');
            $semester = trim($_POST['semester'] ?? '1st');

            // Validation
            $errors = [];
            if (empty($subject_code)) $errors[] = 'Subject code is required';
            if (empty($subject_name)) $errors[] = 'Subject name is required';
            
            // Check if subject code already exists
            if ($this->Auth_model->get_subject_by_code($subject_code)) {
                $errors[] = 'Subject code already exists';
            }

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
                $data['teachers'] = $this->Admin_model->getAllTeachers() ?: [];
                return $this->call->view('admin/add_subject', $data);
            }

            $data = [
                'subject_code' => $subject_code,
                'subject_name' => $subject_name,
                'teacher_id' => !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null,
                'description' => $description,
                'grade_level' => $grade_level,
                'semester' => $semester,
            ];

            $res = $this->Auth_model->create_subject($data);
            
            if ($res) {
                $this->session->set_flashdata('success', 'Subject created successfully');
                redirect('admin/subjects');
            } else {
                $this->session->set_flashdata('error', 'Failed to create subject');
                $data['teachers'] = $this->Admin_model->getAllTeachers() ?: [];
                return $this->call->view('admin/add_subject', $data);
            }
        }
    }

    /**
     * Update Subject (GET/POST)
     */
    public function update_subject($id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        if (!$id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('admin/subjects');
        }

        // Get existing subject
        $subject = $this->Auth_model->get_subject($id);
        if (!$subject) {
            $this->session->set_flashdata('error', 'Subject not found');
            redirect('admin/subjects');
        }

        // Show form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data['page'] = 'Edit Subject';
            $data['subject'] = $subject;
            // Get all teachers
            $teachers = $this->Admin_model->getAllTeachers() ?: [];
            $data['teachers'] = $teachers;
            return $this->call->view('admin/edit_subject', $data);
        }

        // Process form on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject_code = trim($_POST['subject_code'] ?? '');
            $subject_name = trim($_POST['subject_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $grade_level = trim($_POST['grade_level'] ?? '');
            $semester = trim($_POST['semester'] ?? '1st');

            // Validation
            $errors = [];
            if (empty($subject_code)) $errors[] = 'Subject code is required';
            if (empty($subject_name)) $errors[] = 'Subject name is required';
            
            // Check if subject code exists but belongs to different subject
            $existing = $this->Auth_model->get_subject_by_code($subject_code);
            if ($existing && $existing['id'] != $id) {
                $errors[] = 'Subject code already exists';
            }

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
                $data['page'] = 'Edit Subject';
                $data['subject'] = array_merge($subject, $_POST);
                $teachers = $this->Admin_model->getAllTeachers() ?: [];
                $data['teachers'] = $teachers;
                return $this->call->view('admin/edit_subject', $data);
            }

            $teacher_id = $_POST['teacher_id'] ?? $subject['teacher_id'] ?? null;
            $data = [
                'subject_code' => $subject_code,
                'subject_name' => $subject_name,
                'teacher_id' => !empty($teacher_id) ? intval($teacher_id) : null,
                'description' => $description,
                'grade_level' => $grade_level,
                'semester' => $semester,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $res = $this->Auth_model->update_subject($id, $data);
            
            if ($res) {
                $this->session->set_flashdata('success', 'Subject updated successfully');
                redirect('admin/subjects');
            } else {
                $this->session->set_flashdata('error', 'Failed to update subject');
                $data['page'] = 'Edit Subject';
                $data['subject'] = array_merge($subject, $_POST);
                $teachers = $this->Admin_model->getAllTeachers() ?: [];
                $data['teachers'] = $teachers;
                return $this->call->view('admin/edit_subject', $data);
            }
        }
    }

    /**
     * Delete subject
     */
    public function delete_subject($id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        if (!$id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('admin/subjects');
        }

        $res = $this->Auth_model->delete_subject($id);
        $this->session->set_flashdata($res ? 'success' : 'error', $res ? 'Subject deleted' : 'Failed to delete subject');
        redirect('admin/subjects');
    }

    /**
     * Assign teacher to subject (upsert into class_assignments)
     */
    public function assign_teacher($subject_id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        if (!$subject_id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('admin/subjects');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teacher_id = intval($_POST['teacher_id'] ?? 0);
            $section = trim($_POST['section'] ?? '');
            $school_year = trim($_POST['school_year'] ?? date('Y') . '-' . (date('Y')+1));
            $semester = trim($_POST['semester'] ?? '1st');

            if (!$teacher_id) {
                $this->session->set_flashdata('error', 'Teacher is required');
                redirect('admin/subjects');
            }

            $res = $this->Auth_model->upsert_class_assignment($subject_id, $teacher_id, $section, $school_year, $semester);
            $this->session->set_flashdata($res ? 'success' : 'error', $res ? 'Teacher assigned successfully' : 'Failed to assign teacher');
        }

        redirect('admin/subjects');
    }

    /**
     * Teacher dashboard
     */
    public function teacher_dashboard()
    {
        // Check if user is logged in and is a teacher
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        $auth_user_id = $this->session->userdata('user_id');
        // Resolve to canonical teachers.id when possible so counts reflect assigned subjects
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        $data = [
            'page' => 'Dashboard',
            'total_subjects' => $this->Auth_model->count_teacher_subjects($resolved_teacher_id),
            'total_students' => $this->Auth_model->count_teacher_students($resolved_teacher_id),
            'pending_grades' => $this->Auth_model->count_pending_grades($resolved_teacher_id),
            'recent_activities' => $this->Auth_model->get_teacher_activities($resolved_teacher_id, 10)
        ];

        $this->call->view('teacher/dashboard', $data);
    }

    /**
     * Teacher subjects list with grades management
     */
    public function teacher_subjects()
    {
        // Check if user is logged in and is a teacher
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        $auth_user_id = $this->session->userdata('user_id');
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        $data = [
            'page' => 'Subjects',
            'subjects' => $this->Auth_model->get_teacher_subjects_with_students($resolved_teacher_id)
        ];

        $this->call->view('teacher/subjects', $data);
    }

    /**
     * Show class list for a specific subject
     */
    public function teacher_class_list($subject_id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        if (!$subject_id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('teacher/subjects');
        }

        $auth_user_id = $this->session->userdata('user_id');
        // Resolve canonical teacher id (may return teachers.id or null)
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        // Get section filter from query parameter
        $section_id = $this->io->get('section_id');

        $data = [
            'page' => 'Class List',
            'subject' => $this->Auth_model->get_teacher_subject_with_students($resolved_teacher_id, $subject_id, $section_id),
            'sections' => $this->Auth_model->get_sections_for_subject($subject_id),
            'selected_section' => $section_id
        ];

        $this->call->view('teacher/class_list', $data);
    }

    /**
     * Show grades page for a specific subject
     */
    public function teacher_grades($subject_id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        if (!$subject_id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('teacher/subjects');
        }

        $auth_user_id = $this->session->userdata('user_id');
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        // Get section filter from query parameter
        $section_id = $this->io->get('section_id');

        $data = [
            'page' => 'Grades',
            'subject' => $this->Auth_model->get_teacher_subject_with_students($resolved_teacher_id, $subject_id, $section_id),
            'sections' => $this->Auth_model->get_sections_for_subject($subject_id),
            'selected_section' => $section_id
        ];

        $this->call->view('teacher/grades', $data);
    }

    /**
     * Update student grade via AJAX
     */
    public function update_grade()
    {
        // Check if user is logged in and is a teacher
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $student_id = intval($this->io->post('student_id'));
        $subject_id = intval($this->io->post('subject_id'));
        $period = intval($this->io->post('period'));
        $grade = floatval($this->io->post('grade'));

        // Validate inputs
        if (!$student_id || !$subject_id || !$period || $period < 1 || $period > 4) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        // Validate grade range
        if ($grade < 0 || $grade > 100) {
            echo json_encode(['success' => false, 'message' => 'Grade must be between 0 and 100']);
            return;
        }

        // Verify teacher has access to this subject
        $teacher_id = $this->session->userdata('user_id');
        if (!$this->Auth_model->verify_teacher_subject($teacher_id, $subject_id)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized to grade this subject']);
            return;
        }

        // Update grade
        $result = $this->Auth_model->update_student_grade($student_id, $subject_id, $period, $grade);
        $notify = $this->io->post('notify') ? true : false;

        if ($result) {
            // Log the grade update
            $this->Auth_model->log_grade_update($teacher_id, $student_id, $subject_id, $period, $grade);
            
            // Calculate and update final grade (also updates remarks)
            $final_grade = $this->Auth_model->calculate_final_grade($student_id, $subject_id);
            
            // Get updated remarks
            $gradeRow = $this->Auth_model->get_student_grade($student_id, $subject_id);
            $remarks = isset($gradeRow['remarks']) ? $gradeRow['remarks'] : 'Incomplete';
            
            // Optionally create a notification for the student (recipient is auth.id)
            if ($notify) {
                // Try to resolve the student's auth.id by matching student_id -> auth.student_id
                $authRow = $this->Auth_model->get_auth_by_student_id($student_id);
                if ($authRow && isset($authRow['id'])) {
                    $student_auth_id = $authRow['id'];
                    $sender_auth_id = $this->session->userdata('user_id');
                    $msg = "Your grade for subject ID {$subject_id} has been updated. Please check the portal.";
                    $this->Auth_model->create_notification($student_auth_id, $sender_auth_id, $msg);
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Grade updated successfully',
                'final_grade' => round($final_grade, 2),
                'remarks' => $remarks
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update grade']);
        }
    }
    
    /**
     * Submit grades for review (teacher submits all grades for a subject)
     */
    public function submit_grades()
    {
        // Check if user is logged in and is a teacher
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $subject_id = intval($this->io->post('subject_id'));
        $section_id = $this->io->post('section_id'); // Get section filter
        $teacher_auth_id = $this->session->userdata('user_id');
        
        // Resolve teacher_id
        $teacher_id = $this->Auth_model->get_teacher_id_from_auth($teacher_auth_id) ?: $teacher_auth_id;
        
        // Verify teacher has access to this subject
        if (!$this->Auth_model->verify_teacher_subject($teacher_auth_id, $subject_id)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized to submit grades for this subject']);
            return;
        }
        
        // Submit grades (change status from Draft to Submitted) - now section-aware
        $result = $this->Auth_model->submit_grades_for_subject($subject_id, $teacher_id, $section_id);
        
        if ($result !== false && $result > 0) {
            // Log the submission
            $section_text = (!empty($section_id) && $section_id !== 'all') ? " for section {$section_id}" : '';
            $this->Auth_model->log_grade_update($teacher_auth_id, 0, $subject_id, 0, "Submitted grades for review{$section_text}");
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully submitted {$result} grade(s) for admin review"
            ]);
        } elseif ($result === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'No draft grades found to submit. All grades may have already been submitted.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit grades']);
        }
    }
    
    /**
     * Show Excel import page for a subject
     */
    public function import_grades($subject_id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        if (!$subject_id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('teacher/subjects');
        }

        $auth_user_id = $this->session->userdata('user_id');
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        // Verify teacher has access
        if (!$this->Auth_model->verify_teacher_subject($auth_user_id, $subject_id)) {
            $this->session->set_flashdata('error', 'Unauthorized access to this subject');
            redirect('teacher/subjects');
        }

        // Get section filter from query parameter
        $section_id = $this->io->get('section_id');

        // Get subject data
        $subject = $this->Auth_model->get_teacher_subject_with_students($resolved_teacher_id, $subject_id, $section_id);
        
        // If subject not found with section filter, try without filter
        if (!$subject && $section_id) {
            $subject = $this->Auth_model->get_teacher_subject_with_students($resolved_teacher_id, $subject_id, null);
        }
        
        $data = [
            'page' => 'Import Grades',
            'subject' => $subject,
            'selected_section' => $section_id
        ];

        $this->call->view('teacher/import_grades', $data);
    }
    
    /**
     * Download Excel template for grade import
     */
    public function download_template($subject_id = null)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        if (!$subject_id) {
            $this->session->set_flashdata('error', 'Invalid subject id');
            redirect('teacher/subjects');
        }

        $auth_user_id = $this->session->userdata('user_id');
        
        // Verify teacher has access
        if (!$this->Auth_model->verify_teacher_subject($auth_user_id, $subject_id)) {
            $this->session->set_flashdata('error', 'Unauthorized access to this subject');
            redirect('teacher/subjects');
        }

        // Get section filter from query parameter
        $section_id = $this->io->get('section_id');

        $this->call->model('GradeImport_model');
        $spreadsheet = $this->GradeImport_model->generate_template($subject_id, $section_id);
        
        if (!$spreadsheet) {
            $this->session->set_flashdata('error', 'Failed to generate template');
            redirect('teacher/import_grades/' . $subject_id);
        }

        // Get subject name for filename
        $resolved_teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;
        $subject = $this->Auth_model->get_teacher_subject_with_students($resolved_teacher_id, $subject_id);
        $filename = 'grades_template_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $subject->code ?? $subject_id) . '.xlsx';

        // Clean output buffer to prevent corruption
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Send file to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Upload and validate Excel file
     */
    public function upload_excel()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $subject_id = intval($this->io->post('subject_id'));
        $auth_user_id = $this->session->userdata('user_id');

        // Verify teacher has access
        if (!$this->Auth_model->verify_teacher_subject($auth_user_id, $subject_id)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred']);
            return;
        }

        // Validate file type
        $file_ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, ['xlsx', 'xls'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload Excel file (.xlsx or .xls)']);
            return;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = 'public/uploads/grades/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $filename = 'import_' . $subject_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['excel_file']['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
            return;
        }

        // Validate Excel file
        $this->call->model('GradeImport_model');
        $validation = $this->GradeImport_model->validate_excel($file_path, $subject_id);

        if (!$validation['valid']) {
            // Delete invalid file
            unlink($file_path);
            echo json_encode([
                'success' => false,
                'message' => 'Excel file validation failed',
                'errors' => $validation['errors']
            ]);
            return;
        }

        // Store file path and data in session for import
        $this->session->set_userdata('import_file_path', $file_path);
        $this->session->set_userdata('import_data', json_encode($validation['data']));

        echo json_encode([
            'success' => true,
            'message' => 'File validated successfully',
            'data' => $validation['data'],
            'total_rows' => $validation['total_rows']
        ]);
    }
    
    /**
     * Execute grade import from validated file
     */
    public function execute_import()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $subject_id = intval($this->io->post('subject_id'));
        $auth_user_id = $this->session->userdata('user_id');
        $teacher_id = $this->Auth_model->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        // Verify teacher has access
        if (!$this->Auth_model->verify_teacher_subject($auth_user_id, $subject_id)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get import data from session
        $import_data = $this->session->userdata('import_data');
        $file_path = $this->session->userdata('import_file_path');

        if (!$import_data) {
            echo json_encode(['success' => false, 'message' => 'No import data found. Please upload file again.']);
            return;
        }

        $data = json_decode($import_data, true);
        
        if (!$data || !is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Invalid import data format.', 'errors' => []]);
            return;
        }

        // Execute import
        $this->call->model('GradeImport_model');
        
        try {
            $result = $this->GradeImport_model->import_grades($data, $subject_id, $teacher_id);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'imported' => 0,
                'updated' => 0,
                'failed' => count($data),
                'message' => 'Import error: ' . $e->getMessage(),
                'errors' => ['Exception: ' . $e->getMessage()]
            ]);
            return;
        }

        // Clean up
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }
        $this->session->unset_userdata(['import_data', 'import_file_path']);

        // Log the import
        if ($result['success']) {
            $this->Auth_model->log_grade_update(
                $auth_user_id, 
                0, 
                $subject_id, 
                0, 
                "Imported grades via Excel: {$result['imported']} new, {$result['updated']} updated"
            );
        }

        echo json_encode($result);
    }

    // ========== MESSAGING SYSTEM ==========

    public function messages()
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        $data = [
            'page' => 'Messages',
            'threads' => $this->Notification_model->get_threads($auth_id),
            'messageable_users' => $this->Notification_model->get_messageable_users($auth_id, 'teacher'),
            'unread_count' => $this->Notification_model->get_unread_thread_count($auth_id)
        ];
        
        $this->call->view('teacher/messages', $data);
    }

    public function view_thread($thread_id)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        // Check access
        if (!$this->Notification_model->has_thread_access($thread_id, $auth_id)) {
            $this->session->set_flashdata('error', 'Unauthorized access to this conversation.');
            redirect('teacher/messages');
            return;
        }
        
        $data = [
            'page' => 'Conversation',
            'thread' => $this->Notification_model->get_thread($thread_id),
            'messages' => $this->Notification_model->get_thread_messages($thread_id, $auth_id)
        ];
        
        $this->call->view('teacher/view_thread', $data);
    }

    public function send_message()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $this->call->model('Notification_model');
        
        $sender_id = $this->session->userdata('user_id');
        $recipient_id = $this->io->post('recipient_id');
        $subject = trim($this->io->post('subject'));
        $message = trim($this->io->post('message'));
        $thread_id = $this->io->post('thread_id'); // null for new thread
        $parent_id = $this->io->post('parent_id'); // null for new message
        
        if (empty($recipient_id) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Recipient and message are required']);
            return;
        }
        
        if (!$thread_id && empty($subject)) {
            echo json_encode(['success' => false, 'message' => 'Subject is required for new messages']);
            return;
        }
        
        $result = $this->Notification_model->send_message(
            $sender_id,
            $recipient_id,
            $subject,
            $message,
            $thread_id,
            $parent_id
        );
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Message sent successfully', 'message_id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
    }

    public function delete_thread($thread_id)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'teacher') {
            redirect('auth/login');
        }

        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        $result = $this->Notification_model->delete_thread($thread_id, $auth_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Conversation deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete conversation');
        }
        
        redirect('teacher/messages');
    }
}
