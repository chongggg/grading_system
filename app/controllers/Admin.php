<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Admin_model');
        $this->call->model('Student_model');
        $this->call->library('session');
        $this->call->helper('url');
        
        // Check if logged in and is admin
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }
    }

    /** Dashboard */
    public function index()
    {
        $data['page'] = 'Dashboard';
        $data['total_students'] = $this->Admin_model->count_students();
        $data['total_teachers'] = $this->Admin_model->count_teachers();
        $data['total_subjects'] = $this->Admin_model->count_subjects();
        $data['recent_logs'] = $this->Admin_model->get_recent_logs();
        $this->call->view('admin/dashboard', $data);
    }


    /** Reports */
    public function reports()
    {
        $data['page'] = 'Reports';
        $data['grade_reports'] = $this->Admin_model->get_reports();
        $this->call->view('admin/reports', $data);
    }

    /** Announcements */
    public function announcements()
    {
        $data['page'] = 'Announcements';
        $data['announcements'] = $this->Admin_model->get_announcements();
        $this->call->view('admin/announcements', $data);
    }

    public function add_announcement()
    {
        if ($_POST) {
            $announcement_data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $result = $this->Admin_model->add_announcement($announcement_data);
            $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Announcement posted!' : 'Failed to post announcement.');
            redirect('admin/announcements');
        }
    }

    /** Audit Logs */
    public function audit_logs($page = 1)
    {
        $this->call->library('pagination');
        
        // Set pagination theme
        $this->pagination->set_theme('custom');
        $this->pagination->set_custom_classes([
            'nav'    => 'flex justify-center mt-6',
            'ul'     => 'inline-flex items-center space-x-2',
            'li'     => 'inline',
            'a'      => 'px-4 py-2 rounded-lg font-medium transition',
            'active' => 'px-4 py-2 rounded-lg font-bold'
        ]);
        
        $per_page = 50; // Show 50 logs per page
        $page = max(1, intval($page));
        $offset = ($page - 1) * $per_page;
        
        $total_logs = $this->Admin_model->get_audit_logs_count();
        
        // Initialize pagination
        $pagination_data = $this->pagination->initialize(
            $total_logs,
            $per_page,
            $page,
            'admin/audit_logs',
            5
        );
        
        $data['page'] = 'Audit Logs';
        $data['logs'] = $this->Admin_model->get_audit_logs($per_page, $offset);
        $data['total_logs'] = $total_logs;
        $data['per_page'] = $per_page;
        $data['current_page'] = $page;
        $data['pagination_links'] = $this->pagination->paginate();
        
        $this->call->view('admin/audit_logs', $data);
    }


    // ========== TEACHERS ==========
public function teachers()
{
    $data['page'] = 'Teachers';
    
    // Get all teachers
    $teachers = $this->Admin_model->getAllTeachers() ?: [];
    
    // Attach assigned subjects for each teacher
    foreach ($teachers as $idx => $t) {
        // Determine the correct ID to lookup
        $lookupId = null;
        
        if (isset($t['teacher_id']) && !empty($t['teacher_id'])) {
            $lookupId = $t['teacher_id'];
        } elseif (isset($t['id']) && !empty($t['id'])) {
            $lookupId = $t['id'];
        } elseif (isset($t['student_id']) && !empty($t['student_id'])) {
            $lookupId = $t['student_id'];
        }
        
        // Get assigned subjects
        $assigned_subjects = [];
        if ($lookupId) {
            $assigned_subjects = $this->Admin_model->get_subjects_by_teacher($lookupId);
        }
        
        $teachers[$idx]['assigned_subjects'] = $assigned_subjects;
        $teachers[$idx]['subject_count'] = count($assigned_subjects);
    }
    
    $data['teachers'] = $teachers ?: [];
    $this->call->view('admin/teachers', $data);
}



    public function add_teacher()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Expecting create_teacher($teacher_data, $auth_data)
            $teacher_data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'middle_name' => trim($_POST['middle_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'specialization' => trim($_POST['specialization'] ?? ''),
                'contact_number' => trim($_POST['contact_number'] ?? '')
            ];

            // Pass raw password to model; model will hash or preserve if already hashed
            $auth_data = [
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? null,
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Ensure we have a plaintext password to email. If none provided, generate one.
            if (empty($auth_data['password'])) {
                try {
                    $plain_password = substr(bin2hex(random_bytes(4)), 0, 8);
                } catch (Exception $e) {
                    $plain_password = 'teacher123';
                }
                $auth_data['password'] = $plain_password;
            } else {
                $plain_password = $auth_data['password'];
            }

            // Merge teacher and auth arrays into single payload to match model signature
            $payload = array_merge($teacher_data, $auth_data);

            // Add teacher and create auth record
            $new_teacher_id = $this->Admin_model->addTeacher($payload);

            if ($new_teacher_id) {
                // Load mail helper and send credentials email
                $this->call->helper('Mail');
                $full_name = trim(($teacher_data['first_name'] ?? '') . ' ' . ($teacher_data['last_name'] ?? ''));
                $to_email = $teacher_data['email'] ?? '';
                $subject = 'Your teacher account has been created';
                $username = $auth_data['username'] ?? '';
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
        .credentials-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .credentials-box .label { font-weight: bold; color: #333; }
        .credentials-box .value { font-family: monospace; color: #667eea; font-size: 16px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎓 Grading Management System</h1>
            <p>Teacher Account Created</p>
        </div>
        <div class='content'>
            <h2>Hello, {$full_name}!</h2>
            <p>Your teacher account has been created successfully. You can now log in to the system using the following credentials:</p>
            
            <div class='credentials-box'>
                <p><span class='label'>Username:</span> <span class='value'>{$username}</span></p>
                <p><span class='label'>Password:</span> <span class='value'>{$plain_password}</span></p>
            </div>
            
            <div class='warning'>
                <strong>⚠️ Important Security Notice:</strong>
                <p style='margin: 10px 0 0;'>Please change your password immediately after logging in for the first time.</p>
            </div>
            
            <p>You can access the system at: <a href='" . BASE_URL . "'>" . BASE_URL . "</a></p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

                if (!empty($to_email)) {
                    $mailResult = mail_helper($full_name ?: ($auth_data['username'] ?? ''), $to_email, $subject, $message);
                    // Optionally log or set flash on mail failure
                    if ($mailResult !== true) {
                        // log but do not block the flow
                        error_log('Mail send result: ' . print_r($mailResult, true));
                    }
                }

                $this->session->set_flashdata('success', 'Teacher added successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to add teacher');
            }

            redirect('admin/teachers');
        }

        // Show add form on GET
        $data['page'] = 'Add Teacher';
        $this->call->view('admin/add_teacher', $data);
    }

    public function edit_teacher($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update teachers table (Admin_model has updateTeacher)
            $update = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => trim($_POST['email'] ?? '')
            ];
            $this->Admin_model->updateTeacher($id, $update);
            redirect('admin/teachers');
        }

        // For GET show edit form
        $data['page'] = 'Edit Teacher';
        $data['teacher'] = $this->Admin_model->getTeacher($id);
        $this->call->view('admin/edit_teacher', $data);
    }

    public function delete_teacher($id)
    {
        $this->Admin_model->deleteTeacher($id);
        redirect('admin/teachers');
    }

    /**
     * Migrate a legacy auth-based teacher into the normalized teachers table.
     */
    public function sync_teacher($auth_id)
    {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth/login');
        }

        $auth_id = intval($auth_id);
        if (!$auth_id) {
            $this->session->set_flashdata('error', 'Invalid teacher id');
            redirect('admin/teachers');
        }

        $newId = $this->Admin_model->create_teacher_from_auth($auth_id);
        if ($newId) {
            $this->session->set_flashdata('success', 'Teacher migrated to teachers table');
        } else {
            $this->session->set_flashdata('error', 'Failed to migrate teacher.');
        }

        redirect('admin/teachers');
    }

    // ========== STUDENTS ==========
    public function students() {
        $data['page'] = 'Students';

        // Pagination parameters
        $per_page = 25;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $students_page = $this->Admin_model->get_students($per_page, $page);

        // If pagination returned no visible students (possible when legacy rows were filtered),
        // fall back to a simpler full-list to ensure the Manage Students UI isn't empty.
        if (empty($students_page['data'])) {
            $all = $this->Admin_model->getAllStudents();
            $data['students'] = $all ?: [];

            // Build a minimal pagination object for the view
            $data['pagination'] = [
                'total' => count($all),
                'per_page' => count($all),
                'current_page' => 1,
                'last_page' => 1
            ];
        } else {
            // Ensure view compatibility: pass students array and pagination metadata
            $data['students'] = $students_page['data'] ?? [];
            $data['pagination'] = [
                'total' => $students_page['total'] ?? 0,
                'per_page' => $students_page['per_page'] ?? $per_page,
                'current_page' => $students_page['current_page'] ?? $page,
                'last_page' => $students_page['last_page'] ?? 1
            ];
        }

        $data['available_subjects'] = []; // Will be loaded via AJAX when needed
        $this->call->view('admin/students', $data);
    }

    public function add_student() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'username', 'password', 'confirm_password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $this->session->set_flashdata('error', 'All required fields must be filled out');
                    redirect('admin/add_student');
                    return;
                }
            }

            // Validate password match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $this->session->set_flashdata('error', 'Passwords do not match');
                redirect('admin/add_student');
                return;
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Invalid email format');
                redirect('admin/add_student');
                return;
            }

            // Check if username exists
            $existing = $this->db->table('auth')->where('username', $_POST['username'])->get();
            if ($existing) {
                $this->session->set_flashdata('error', 'Username already exists');
                redirect('admin/add_student');
                return;
            }

            // Check if email exists
            $existing = $this->db->table('students')->where('email', $_POST['email'])->get();
            if ($existing) {
                $this->session->set_flashdata('error', 'Email already exists');
                redirect('admin/add_student');
                return;
            }

            $result = $this->Admin_model->addStudent($_POST);
            if ($result) {
                $this->session->set_flashdata('success', 'Student added successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to add student');
            }
            redirect('admin/students');
        }

        // Show the add form
        $data['page'] = 'Add Student';
        $data['subjects'] = $this->Admin_model->getAllSubjects();
        $this->call->view('admin/add_student', $data);
    }

    public function edit_student($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'username'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $this->session->set_flashdata('error', 'All required fields must be filled out');
                    redirect('admin/edit_student/' . $id);
                    return;
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Invalid email format');
                redirect('admin/edit_student/' . $id);
                return;
            }

            // Check if username exists (excluding current student)
            $existing = $this->db->table('auth')
                                ->where('username', $_POST['username'])
                                ->where('student_id', '!=', $id)
                                ->get();
            if ($existing) {
                $this->session->set_flashdata('error', 'Username already exists');
                redirect('admin/edit_student/' . $id);
                return;
            }

            // Check if email exists (excluding current student)
            $existing = $this->db->table('students')
                                ->where('email', $_POST['email'])
                                ->where('id', '!=', $id)
                                ->get();
            if ($existing) {
                $this->session->set_flashdata('error', 'Email already exists');
                redirect('admin/edit_student/' . $id);
                return;
            }

            // If password is being changed, validate confirmation
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $this->session->set_flashdata('error', 'Passwords do not match');
                    redirect('admin/edit_student/' . $id);
                    return;
                }
            }

            $result = $this->Admin_model->updateStudent($id, $_POST);
            if ($result) {
                $this->session->set_flashdata('success', 'Student updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to update student');
            }
            redirect('admin/students');
            return;
        }

        // Show the edit form
        $data['page'] = 'Edit Student';
        
        // Get student data including auth info
        $student = $this->db->table('students s')
            ->join('auth a', 's.id = a.student_id')
            ->select('s.*, a.username')
            ->where('s.id', $id)
            ->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found');
            redirect('admin/students');
            return;
        }

        $data['student'] = $student;
        $data['subjects'] = $this->Admin_model->getAllSubjects();
        $this->call->view('admin/edit_student', $data);
    }

    public function delete_student($id) {
        $this->Admin_model->deleteStudent($id);
        redirect('admin/students');
    }

    public function get_available_subjects($student_id) {
        header('Content-Type: application/json');
        $subjects = $this->Admin_model->get_available_subjects($student_id);
        echo json_encode($subjects);
    }

    public function assign_subject() {
        header('Content-Type: application/json');
        
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!isset($data['student_id']) || !isset($data['subject_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }

        $result = $this->Admin_model->assign_subject($data['student_id'], $data['subject_id']);
        echo json_encode($result);
    }

    public function remove_subject() {
        header('Content-Type: application/json');
        
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!isset($data['student_id']) || !isset($data['subject_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }

        $result = $this->Admin_model->remove_subject($data['student_id'], $data['subject_id']);
        echo json_encode($result);
    }

    // ========== SUBJECTS ==========
    public function subjects() {
        $filters = [];
        if (isset($_GET['grade_level']) && $_GET['grade_level'] !== '') {
            $filters['grade_level'] = $_GET['grade_level'];
        }
        if (isset($_GET['semester']) && $_GET['semester'] !== '') {
            $filters['semester'] = $_GET['semester'];
        }
        $data['subjects'] = $this->Admin_model->getAllSubjects($filters);
        $data['teachers'] = $this->Admin_model->getAllTeachers(); // for dropdown
        $this->call->view('admin/subjects', $data);
    }

public function add_subject() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->Admin_model->add_subject($_POST);
        redirect('admin/subjects');
    }
    
    // Show form
    $data['page'] = 'Add Subject';
    
    // Get all teachers
    $teachers = $this->Admin_model->getAllTeachers() ?: [];
    
    // Normalize teacher IDs for consistency
    foreach ($teachers as $idx => $t) {
        if (!isset($t['id'])) {
            $teachers[$idx]['id'] = $t['teacher_id'] ?? $t['student_id'] ?? null;
        }
    }
    
    $data['teachers'] = $teachers;
    $this->call->view('admin/add_subject', $data);
}
public function edit_subject($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $this->Admin_model->updateSubject($id, $_POST);
        if ($result) {
            $this->session->set_flashdata('success', 'Subject updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to update subject');
        }
        redirect('admin/subjects');
        return;
    }

    // Show edit form
    $data['page'] = 'Edit Subject';
    
    // Get subject data
    $subject = $this->db->table('subjects')
                       ->where('id', $id)
                       ->get();
    
    // Check if subject exists
    if (!$subject) {
        $this->session->set_flashdata('error', 'Subject not found');
        redirect('admin/subjects');
        return;
    }
    
    // Get current teacher assignment if any
    $assignment = $this->db->table('class_assignments')
                         ->where('subject_id', $id)
                         ->get();
    
    if ($assignment) {
        $subject['teacher_id'] = $assignment['teacher_id'];
    }
    
    $data['subject'] = $subject;
    
    // Get all teachers
    $teachers = $this->Admin_model->getAllTeachers() ?: [];
    
    // Normalize teacher IDs for the view
    foreach ($teachers as $idx => $t) {
        // Ensure each teacher has an 'id' field for consistency
        if (!isset($t['id'])) {
            $teachers[$idx]['id'] = $t['teacher_id'] ?? $t['student_id'] ?? null;
        }
    }
    
    $data['teachers'] = $teachers;
    $this->call->view('admin/edit_subject', $data);
}

    public function delete_subject($id) {
        $this->Admin_model->deleteSubject($id);
        redirect('admin/subjects');
    }
    
    /**
     * Review pending grade submissions
     */
    public function review_grades()
    {
        $this->call->model('Auth_model');
        
        $pending_grades = $this->Auth_model->get_pending_grade_submissions();
        
        // Group grades by section
        $grouped_grades = [];
        foreach ($pending_grades as $grade) {
            $section_key = $grade['section_id'] ?: 0;
            $grade_level = $grade['grade_level'] ?: 'N/A';
            $section_name = $grade['section_name'] ?: 'No Section';
            $display_name = ($section_key > 0) ? "Grade {$grade_level} - {$section_name}" : 'No Section';
            
            if (!isset($grouped_grades[$section_key])) {
                $grouped_grades[$section_key] = [
                    'section_id' => $section_key,
                    'section_name' => $display_name,
                    'grade_level' => $grade_level,
                    'grades' => []
                ];
            }
            
            $grouped_grades[$section_key]['grades'][] = $grade;
        }
        
        $data['page'] = 'Review Grades';
        $data['grouped_grades'] = $grouped_grades;
        $data['total_pending'] = count($pending_grades);
        
        $this->call->view('admin/review_grades', $data);
    }
    
    /**
     * Approve a grade submission
     */
    public function approve_grade()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $this->call->model('Auth_model');
        $this->call->helper('Mail_helper');
        
        $grade_id = intval($this->io->post('grade_id'));
        
        if (!$grade_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid grade ID']);
            return;
        }
        
        // Get grade details before approval for email notification
        $grade_info = $this->db->table('grades g')
            ->join('students st', 'g.student_id = st.id')
            ->join('subjects sub', 'g.subject_id = sub.id')
            ->join('teachers t', 'g.teacher_id = t.id')
            ->select('g.id, st.first_name as student_first, st.last_name as student_last, 
                      sub.subject_name, sub.subject_code, 
                      t.first_name as teacher_first, t.last_name as teacher_last, t.email as teacher_email,
                      g.prelim, g.midterm, g.finals, g.final_grade, g.remarks')
            ->where('g.id', $grade_id)
            ->get();
        
        $result = $this->Auth_model->approve_grade($grade_id);
        
        if ($result !== false && $result > 0) {
            // Log the approval
            $admin_id = $this->session->userdata('user_id');
            $this->Auth_model->log_grade_update($admin_id, 0, 0, 0, "Approved grade ID: {$grade_id}");
            
            // Send approval email to teacher
            if ($grade_info && !empty($grade_info['teacher_email'])) {
                $teacher_name = $grade_info['teacher_first'] . ' ' . $grade_info['teacher_last'];
                $student_name = $grade_info['student_first'] . ' ' . $grade_info['student_last'];
                $subject_info = $grade_info['subject_name'] . ' (' . $grade_info['subject_code'] . ')';
                
                $email_subject = 'Grade Submission Approved';
                $email_message = "<!DOCTYPE html>
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
        .info-box { background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .info-box .label { font-weight: bold; color: #333; }
        .info-box .value { color: #555; }
        .success-badge { background: #10b981; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>✅ Grade Submission Approved</h1>
            <p>Grading Management System</p>
        </div>
        <div class='content'>
            <h2>Dear {$teacher_name},</h2>
            <p>Your grade submission has been <span class='success-badge'>APPROVED</span> by the administrator.</p>
            
            <div class='info-box'>
                <p><span class='label'>Subject:</span> <span class='value'>{$subject_info}</span></p>
                <p><span class='label'>Student:</span> <span class='value'>{$student_name}</span></p>
                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;'>
                <p><span class='label'>Prelim:</span> <span class='value'>{$grade_info['prelim']}</span></p>
                <p><span class='label'>Midterm:</span> <span class='value'>{$grade_info['midterm']}</span></p>
                <p><span class='label'>Finals:</span> <span class='value'>{$grade_info['finals']}</span></p>
                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;'>
                <p><span class='label'>Final Grade:</span> <span class='value'><strong>{$grade_info['final_grade']}</strong></span></p>
                <p><span class='label'>Remarks:</span> <span class='value'>{$grade_info['remarks']}</span></p>
            </div>
            
            <p>✓ The grades are now visible to the student.</p>
            <p>Thank you for your dedication to accurate grading.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
                
                // Send email
                $mail_result = mail_helper($teacher_name, $grade_info['teacher_email'], $email_subject, $email_message);
                
                if ($mail_result !== true) {
                    // Log email error but don't fail the approval
                    error_log("Failed to send approval email to {$grade_info['teacher_email']}: " . $mail_result);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Grade approved successfully and is now visible to student. Email notification sent.']);
        } elseif ($result === 0) {
            echo json_encode(['success' => false, 'message' => 'Grade not found or not in Submitted status']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve grade']);
        }
    }
    
    /**
     * Reject a grade submission
     */
    public function reject_grade()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $this->call->model('Auth_model');
        $this->call->helper('Mail_helper');
        
        $grade_id = intval($this->io->post('grade_id'));
        $reason = trim($this->io->post('reason'));
        
        if (!$grade_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid grade ID']);
            return;
        }
        
        // Get grade details before rejection for email notification
        $grade_info = $this->db->table('grades g')
            ->join('students st', 'g.student_id = st.id')
            ->join('subjects sub', 'g.subject_id = sub.id')
            ->join('teachers t', 'g.teacher_id = t.id')
            ->select('g.id, st.first_name as student_first, st.last_name as student_last, 
                      sub.subject_name, sub.subject_code, 
                      t.first_name as teacher_first, t.last_name as teacher_last, t.email as teacher_email,
                      g.prelim, g.midterm, g.finals, g.final_grade, g.remarks')
            ->where('g.id', $grade_id)
            ->get();
        
        $result = $this->Auth_model->reject_grade($grade_id, $reason);
        
        if ($result !== false && $result > 0) {
            // Log the rejection
            $admin_id = $this->session->userdata('user_id');
            $this->Auth_model->log_grade_update($admin_id, 0, 0, 0, "Rejected grade ID: {$grade_id}. Reason: {$reason}");
            
            // Send rejection email to teacher
            if ($grade_info && !empty($grade_info['teacher_email'])) {
                $teacher_name = $grade_info['teacher_first'] . ' ' . $grade_info['teacher_last'];
                $student_name = $grade_info['student_first'] . ' ' . $grade_info['student_last'];
                $subject_info = $grade_info['subject_name'] . ' (' . $grade_info['subject_code'] . ')';
                
                $email_subject = 'Grade Submission Rejected - Action Required';
                $rejection_reason = !empty($reason) ? $reason : 'No specific reason provided.';
                $email_message = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .content h2 { color: #333; margin-top: 0; }
        .content p { color: #555; line-height: 1.6; }
        .info-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .info-box .label { font-weight: bold; color: #333; }
        .info-box .value { color: #555; }
        .reason-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .steps-box { background: #f8f9fa; border-radius: 4px; padding: 20px; margin: 20px 0; }
        .steps-box ol { margin: 10px 0; padding-left: 20px; }
        .steps-box li { color: #555; margin: 8px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⚠️ Grade Submission Rejected</h1>
            <p>Action Required</p>
        </div>
        <div class='content'>
            <h2>Dear {$teacher_name},</h2>
            <p>Your grade submission has been <strong>REJECTED</strong> by the administrator and requires revision.</p>
            
            <div class='info-box'>
                <p><span class='label'>Subject:</span> <span class='value'>{$subject_info}</span></p>
                <p><span class='label'>Student:</span> <span class='value'>{$student_name}</span></p>
                <hr style='border: none; border-top: 1px solid #fee2e2; margin: 15px 0;'>
                <p><span class='label'>Prelim:</span> <span class='value'>{$grade_info['prelim']}</span></p>
                <p><span class='label'>Midterm:</span> <span class='value'>{$grade_info['midterm']}</span></p>
                <p><span class='label'>Finals:</span> <span class='value'>{$grade_info['finals']}</span></p>
                <hr style='border: none; border-top: 1px solid #fee2e2; margin: 15px 0;'>
                <p><span class='label'>Final Grade:</span> <span class='value'><strong>{$grade_info['final_grade']}</strong></span></p>
                <p><span class='label'>Remarks:</span> <span class='value'>{$grade_info['remarks']}</span></p>
            </div>
            
            <div class='reason-box'>
                <strong>📝 Reason for Rejection:</strong>
                <p style='margin: 10px 0 0;'>{$rejection_reason}</p>
            </div>
            
            <h3 style='color: #333;'>Action Required:</h3>
            <p>Please review and revise the grades, then resubmit for approval.</p>
            
            <div class='steps-box'>
                <strong>To Revise:</strong>
                <ol>
                    <li>Log in to the grading system</li>
                    <li>Navigate to your subjects</li>
                    <li>Edit the grades for {$student_name}</li>
                    <li>Click 'Submit for Review' again</li>
                </ol>
            </div>
            
            <p>Thank you for your cooperation.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the Grading Management System.</p>
            <p>&copy; " . date('Y') . " All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
                
                // Send email
                $mail_result = mail_helper($teacher_name, $grade_info['teacher_email'], $email_subject, $email_message);
                
                if ($mail_result !== true) {
                    // Log email error but don't fail the rejection
                    error_log("Failed to send rejection email to {$grade_info['teacher_email']}: " . $mail_result);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Grade rejected and returned to teacher for revision. Email notification sent.']);
        } elseif ($result === 0) {
            echo json_encode(['success' => false, 'message' => 'Grade not found or not in Submitted status']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject grade']);
        }
    }

    // ========== SECTION MANAGEMENT ==========
    
    public function sections()
    {
        $this->call->model('Sections_model');
        $data['page'] = 'Sections Management';
        $data['sections'] = $this->Sections_model->get_all_sections();
        $data['grade_levels'] = $this->Sections_model->get_grade_levels();
        $data['school_years'] = $this->Sections_model->get_school_years();
        $data['teachers'] = $this->Sections_model->get_available_teachers();
        $this->call->view('admin/sections', $data);
    }

    public function add_section()
    {
        $this->call->model('Sections_model');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'section_name' => trim($this->io->post('section_name')),
                'grade_level' => trim($this->io->post('grade_level')),
                'school_year' => trim($this->io->post('school_year')),
                'semester' => trim($this->io->post('semester')),
                'adviser_id' => $this->io->post('adviser_id'),
                'max_capacity' => $this->io->post('max_capacity') ?: 40
            ];
            
            $result = $this->Sections_model->create_section($data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Section created successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to create section.');
            }
            
            redirect('admin/sections');
        } else {
            $data['page'] = 'Add Section';
            $data['grade_levels'] = $this->Sections_model->get_grade_levels();
            $data['school_years'] = $this->Sections_model->get_school_years();
            $data['teachers'] = $this->Sections_model->get_available_teachers();
            $this->call->view('admin/add_section', $data);
        }
    }

    public function edit_section($id)
    {
        $this->call->model('Sections_model');
        
        $section = $this->Sections_model->get_section($id);
        if (!$section) {
            $this->session->set_flashdata('error', 'Section not found.');
            redirect('admin/sections');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'section_name' => trim($this->io->post('section_name')),
                'grade_level' => trim($this->io->post('grade_level')),
                'school_year' => trim($this->io->post('school_year')),
                'semester' => trim($this->io->post('semester')),
                'adviser_id' => $this->io->post('adviser_id'),
                'max_capacity' => $this->io->post('max_capacity') ?: 40
            ];
            
            $result = $this->Sections_model->update_section($id, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Section updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update section.');
            }
            
            redirect('admin/sections');
        } else {
            $data['page'] = 'Edit Section';
            $data['section'] = $section;
            $data['grade_levels'] = $this->Sections_model->get_grade_levels();
            $data['school_years'] = $this->Sections_model->get_school_years();
            $data['teachers'] = $this->Sections_model->get_available_teachers();
            $this->call->view('admin/edit_section', $data);
        }
    }

    public function delete_section($id)
    {
        $this->call->model('Sections_model');
        $result = $this->Sections_model->delete_section($id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('admin/sections');
    }

    public function view_section($id)
    {
        $this->call->model('Sections_model');
        $section = $this->Sections_model->get_section($id);
        
        if (!$section) {
            $this->session->set_flashdata('error', 'Section not found.');
            redirect('admin/sections');
            return;
        }
        
        $data['page'] = 'Section Details';
        $data['section'] = $section;
        $data['unassigned_students'] = $this->Sections_model->get_unassigned_students($section['grade_level']);
        $this->call->view('admin/view_section', $data);
    }

    public function assign_student_to_section()
    {
        header('Content-Type: application/json');
        
        $this->call->model('Sections_model');
        
        $student_id = $this->io->post('student_id');
        $section_id = $this->io->post('section_id');
        
        $result = $this->Sections_model->assign_student_to_section($student_id, $section_id);
        
        // Auto-enroll student in subject bundle based on section's grade level
        if ($result['success']) {
            $this->call->model('SubjectBundle_model');
            $auto_result = $this->SubjectBundle_model->auto_enroll_section_students($section_id);
            
            // Always include auto-enrollment info in result
            $result['auto_enrollment'] = $auto_result;
            
            if ($auto_result['success'] && $auto_result['total_enrolled'] > 0) {
                $result['message'] .= ' ' . $auto_result['message'];
            } else if (!$auto_result['success']) {
                $result['warning'] = $auto_result['message'];
                if (isset($auto_result['debug'])) {
                    $result['debug'] = $auto_result['debug'];
                }
            }
        }
        
        echo json_encode($result);
    }

    public function remove_student_from_section()
    {
        header('Content-Type: application/json');
        
        $this->call->model('Sections_model');
        
        $student_id = $this->io->post('student_id');
        
        $result = $this->Sections_model->remove_student_from_section($student_id);
        
        echo json_encode($result);
    }

    public function bulk_assign_section()
    {
        header('Content-Type: application/json');
        
        $this->call->model('Sections_model');
        
        $student_ids = $this->io->post('student_ids'); // array
        $section_id = $this->io->post('section_id');
        
        $result = $this->Sections_model->bulk_assign_students($student_ids, $section_id);
        
        // Auto-enroll all assigned students in subject bundle
        if ($result['success']) {
            $this->call->model('SubjectBundle_model');
            $auto_result = $this->SubjectBundle_model->auto_enroll_section_students($section_id);
            
            // Always include auto-enrollment info in result
            $result['auto_enrollment'] = $auto_result;
            
            if ($auto_result['success'] && $auto_result['total_enrolled'] > 0) {
                $result['message'] .= ' ' . $auto_result['message'];
            } else if (!$auto_result['success']) {
                $result['warning'] = $auto_result['message'];
                if (isset($auto_result['debug'])) {
                    $result['debug'] = $auto_result['debug'];
                }
            }
        }
        
        echo json_encode($result);
    }

    // ========== SUBJECT BUNDLES ==========
    
    public function subject_bundles()
    {
        $this->call->model('SubjectBundle_model');
        $this->call->model('Sections_model');
        
        $data['page'] = 'Subject Bundles';
        $data['bundles'] = $this->SubjectBundle_model->get_all_bundles();
        $data['grade_levels'] = $this->Sections_model->get_grade_levels();
        $data['school_years'] = $this->Sections_model->get_school_years();
        
        $this->call->view('admin/subject_bundles', $data);
    }

    public function add_bundle()
    {
        $this->call->model('SubjectBundle_model');
        $this->call->model('Sections_model');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bundle_name' => trim($this->io->post('bundle_name')),
                'grade_level' => trim($this->io->post('grade_level')),
                'semester' => $this->io->post('semester'),
                'school_year' => trim($this->io->post('school_year')),
                'description' => trim($this->io->post('description'))
            ];
            
            $result = $this->SubjectBundle_model->create_bundle($data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Subject bundle created successfully!');
                redirect('admin/view_bundle/' . $result);
            } else {
                $this->session->set_flashdata('error', 'Failed to create bundle.');
                redirect('admin/subject_bundles');
            }
        } else {
            $data['page'] = 'Add Subject Bundle';
            $data['grade_levels'] = $this->Sections_model->get_grade_levels();
            $data['school_years'] = $this->Sections_model->get_school_years();
            $this->call->view('admin/add_bundle', $data);
        }
    }

    public function edit_bundle($id)
    {
        $this->call->model('SubjectBundle_model');
        $this->call->model('Sections_model');
        
        $bundle = $this->SubjectBundle_model->get_bundle($id);
        if (!$bundle) {
            $this->session->set_flashdata('error', 'Bundle not found.');
            redirect('admin/subject_bundles');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bundle_name' => trim($this->io->post('bundle_name')),
                'grade_level' => trim($this->io->post('grade_level')),
                'semester' => $this->io->post('semester'),
                'school_year' => trim($this->io->post('school_year')),
                'description' => trim($this->io->post('description'))
            ];
            
            $result = $this->SubjectBundle_model->update_bundle($id, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Bundle updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update bundle.');
            }
            
            redirect('admin/view_bundle/' . $id);
        } else {
            $data['page'] = 'Edit Bundle';
            $data['bundle'] = $bundle;
            $data['grade_levels'] = $this->Sections_model->get_grade_levels();
            $data['school_years'] = $this->Sections_model->get_school_years();
            $this->call->view('admin/edit_bundle', $data);
        }
    }

    public function delete_bundle($id)
    {
        $this->call->model('SubjectBundle_model');
        $result = $this->SubjectBundle_model->delete_bundle($id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('admin/subject_bundles');
    }

    public function view_bundle($id)
    {
        $this->call->model('SubjectBundle_model');
        
        $bundle = $this->SubjectBundle_model->get_bundle($id);
        if (!$bundle) {
            $this->session->set_flashdata('error', 'Bundle not found.');
            redirect('admin/subject_bundles');
            return;
        }
        
        $data['page'] = 'Bundle Details';
        $data['bundle'] = $bundle;
        $data['available_subjects'] = $this->SubjectBundle_model->get_available_subjects($bundle['grade_level'], $bundle['semester'], $id);
        
        $this->call->view('admin/view_bundle', $data);
    }

    public function add_subject_to_bundle()
    {
        $this->call->model('SubjectBundle_model');
        
        $bundle_id = $this->io->post('bundle_id');
        $subject_id = $this->io->post('subject_id');
        
        $result = $this->SubjectBundle_model->add_subject_to_bundle($bundle_id, $subject_id);
        
        echo json_encode($result);
    }

    public function remove_subject_from_bundle()
    {
        $this->call->model('SubjectBundle_model');
        
        $item_id = $this->io->post('item_id');
        
        $result = $this->SubjectBundle_model->remove_subject_from_bundle($item_id);
        
        echo json_encode($result);
    }

    // ========== MESSAGING SYSTEM ==========

    public function messages()
    {
        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        $data['page'] = 'Messages';
        $data['threads'] = $this->Notification_model->get_threads($auth_id);
        $data['messageable_users'] = $this->Notification_model->get_messageable_users($auth_id, 'admin');
        $data['unread_count'] = $this->Notification_model->get_unread_thread_count($auth_id);
        
        $this->call->view('admin/messages', $data);
    }

    public function view_thread($thread_id)
    {
        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        // Check access
        if (!$this->Notification_model->has_thread_access($thread_id, $auth_id)) {
            $this->session->set_flashdata('error', 'Unauthorized access to this conversation.');
            redirect('admin/messages');
            return;
        }
        
        $data['page'] = 'Conversation';
        $data['thread'] = $this->Notification_model->get_thread($thread_id);
        $data['messages'] = $this->Notification_model->get_thread_messages($thread_id, $auth_id);
        
        $this->call->view('admin/view_thread', $data);
    }

    public function send_message()
    {
        header('Content-Type: application/json');
        
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
        $this->call->model('Notification_model');
        
        $auth_id = $this->session->userdata('user_id');
        
        $result = $this->Notification_model->delete_thread($thread_id, $auth_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Conversation deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete conversation');
        }
        
        redirect('admin/messages');
    }

}
