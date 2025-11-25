<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Student extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Student_model');
        $this->call->model('Grade_model');
        $this->call->model('Notification_model');
        $this->call->library('session');
        $this->call->helper('url');
        
        // Check if logged in and is student
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'student') {
            redirect('auth/login');
        }
    }

    /** Dashboard */
    public function index()
    {
        redirect('student/dashboard');
    }

    /** Dashboard */
    public function dashboard()
    {
        $student_id = $this->session->userdata('student_id');
        $auth_id = $this->session->userdata('user_id');
        
        $data['page'] = 'Dashboard';
        
        // Get student info
        $data['student'] = $this->Student_model->get_student($student_id);
        
        // Get statistics
        $data['total_subjects'] = $this->Grade_model->count_enrolled_subjects($student_id);
        $data['reviewed_grades'] = $this->Grade_model->count_reviewed_grades($student_id);
        $data['pending_grades'] = $data['total_subjects'] - $data['reviewed_grades'];
        
        // Get recent announcements for students
        $sql = "SELECT * FROM announcements WHERE role_target IN ('all', 'student') ORDER BY created_at DESC LIMIT 5";
        $result = $this->db->raw($sql);
        $data['announcements'] = $result ? $result->fetchAll() : [];
        
        // Get unread notification count
        $data['unread_count'] = $this->Notification_model->get_unread_count($auth_id);
        
        $this->call->view('student/dashboard', $data);
    }

    /** My Subjects - View enrolled subjects/sections */
    public function subjects()
    {
        $student_id = $this->session->userdata('student_id');
        $auth_id = $this->session->userdata('user_id');
        
        $data['page'] = 'My Subjects';
        
        // Get enrolled subjects from student_subjects table
        $sql = "SELECT ss.id, ss.subject_id, ss.created_at, 
                       s.subject_code, s.subject_name, s.description, s.grade_level, s.semester,
                       t.first_name as teacher_first_name, t.last_name as teacher_last_name
                FROM student_subjects ss
                JOIN subjects s ON ss.subject_id = s.id
                LEFT JOIN teachers t ON s.teacher_id = t.id
                WHERE ss.student_id = ?
                ORDER BY s.subject_code";
        
        $result = $this->db->raw($sql, [$student_id]);
        $data['subjects'] = $result ? $result->fetchAll() : [];
        
        // Get unread notification count
        $data['unread_count'] = $this->Notification_model->get_unread_count($auth_id);
        
        $this->call->view('student/subjects', $data);
    }

    /** Profile - View and Update */
    public function profile()
    {
        $student_id = $this->session->userdata('student_id');
        $auth_id = $this->session->userdata('user_id');
        
        if ($_POST) {
            // Students can update most fields EXCEPT grade_level and section (admin-only)
            $allowed_fields = [
                'first_name', 
                'middle_name', 
                'last_name', 
                'email', 
                'gender', 
                'birthdate', 
                'address'
            ];
            
            $update_data = [];
            $errors = [];
            
            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $value = trim($_POST[$field]);
                    
                    // Validation
                    if (in_array($field, ['first_name', 'last_name']) && empty($value)) {
                        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                        continue;
                    }
                    
                    if ($field === 'email') {
                        if (empty($value)) {
                            $errors[] = 'Email is required';
                            continue;
                        }
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = 'Invalid email format';
                            continue;
                        }
                        // Check if email exists for other students
                        if ($this->Student_model->email_exists($value, $student_id)) {
                            $errors[] = 'Email already exists';
                            continue;
                        }
                    }
                    
                    if ($field === 'birthdate' && !empty($value)) {
                        // Validate date format
                        $date = \DateTime::createFromFormat('Y-m-d', $value);
                        if (!$date || $date->format('Y-m-d') !== $value) {
                            $errors[] = 'Invalid birthdate format';
                            continue;
                        }
                    }
                    
                    $update_data[$field] = $value;
                }
            }
            
            if (empty($errors)) {
                $update_data['updated_at'] = date('Y-m-d H:i:s');
                
                if ($this->Student_model->update_student($student_id, $update_data)) {
                    // Update session if name or email changed
                    if (isset($update_data['first_name'])) {
                        $this->session->set_userdata('first_name', $update_data['first_name']);
                    }
                    if (isset($update_data['last_name'])) {
                        $this->session->set_userdata('last_name', $update_data['last_name']);
                    }
                    if (isset($update_data['email'])) {
                        $this->session->set_userdata('email', $update_data['email']);
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            }
            exit;
        }
        
        $data['page'] = 'My Profile';
        $data['student'] = $this->Student_model->get_student($student_id);
        $data['unread_count'] = $this->Notification_model->get_unread_count($auth_id);
        
        $this->call->view('student/profile', $data);
    }

    /** View Grades - Only Reviewed Status */
    public function grades()
    {
        $student_id = $this->session->userdata('student_id');
        $auth_id = $this->session->userdata('user_id');
        
        $data['page'] = 'My Grades';
        
        // Get only reviewed grades with subject and teacher details
        $data['grades'] = $this->Grade_model->get_reviewed_grades($student_id);
        
        // Get unread notification count
        $data['unread_count'] = $this->Notification_model->get_unread_count($auth_id);
        
        $this->call->view('student/grades', $data);
    }

    /** Download PDF Grade Report */
    public function download_pdf()
    {
        // Clean output buffer to prevent TCPDF errors
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        $student_id = $this->session->userdata('student_id');
        
        // Get comprehensive grade report data
        $report_data = $this->Grade_model->get_grade_report_data($student_id);
        
        if (!$report_data || !isset($report_data['student'])) {
            $this->session->set_flashdata('error', 'No grade data available for PDF generation.');
            redirect('student/grades');
            return;
        }
        
        // Load TCPDF library - Use absolute path
        $vendor_path = dirname(dirname(__FILE__)) . '/vendor/autoload.php';
        if (!file_exists($vendor_path)) {
            $this->session->set_flashdata('error', 'PDF library not found. Please run composer install.');
            redirect('student/grades');
            return;
        }
        require_once $vendor_path;
        
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Grading Management System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Grade Report');
        $pdf->SetSubject('Student Grade Report');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Build HTML content
        $student = $report_data['student'];
        $grades = $report_data['grades'];
        
        $html = '
        <style>
            h1 { text-align: center; color: #1e3a8a; font-size: 18px; margin-bottom: 5px; }
            h2 { text-align: center; color: #3b82f6; font-size: 14px; margin-bottom: 20px; }
            .info-table { width: 100%; margin-bottom: 20px; }
            .info-table td { padding: 5px; font-size: 11px; }
            .info-label { font-weight: bold; width: 30%; }
            .grades-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .grades-table th { background-color: #1e40af; color: white; padding: 8px; text-align: center; font-size: 10px; border: 1px solid #ddd; }
            .grades-table td { padding: 6px; text-align: center; font-size: 9px; border: 1px solid #ddd; }
            .grades-table tr:nth-child(even) { background-color: #f9fafb; }
            .passed { color: #059669; font-weight: bold; }
            .failed { color: #dc2626; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #6b7280; }
        </style>
        
        <h1>GRADING MANAGEMENT SYSTEM</h1>
        <h2>Official Grade Report</h2>
        
        <table class="info-table" cellpadding="5">
            <tr>
                <td class="info-label">Student Name:</td>
                <td>' . htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) . '</td>
                <td class="info-label">Student ID:</td>
                <td>' . htmlspecialchars($student_id) . '</td>
            </tr>
            <tr>
                <td class="info-label">Grade Level:</td>
                <td>' . htmlspecialchars($student['grade_level'] ?? 'N/A') . '</td>
                <td class="info-label">Section:</td>
                <td>' . htmlspecialchars($student['section'] ?? 'N/A') . '</td>
            </tr>
        </table>
        
        <h3 style="margin-top: 20px; margin-bottom: 10px; font-size: 12px; color: #1e3a8a;">Academic Performance</h3>
        
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Final Grade</th>
                    <th>Remarks</th>
                    <th>School Year</th>
                </tr>
            </thead>
            <tbody>';
        
        if (!empty($grades)) {
            foreach ($grades as $grade) {
                $remarks_class = '';
                if ($grade['remarks'] === 'Passed') {
                    $remarks_class = 'passed';
                } elseif ($grade['remarks'] === 'Failed') {
                    $remarks_class = 'failed';
                }
                
                $html .= '<tr>
                    <td>' . htmlspecialchars($grade['subject_code']) . '</td>
                    <td><strong>' . number_format($grade['final_grade'], 2) . '</strong></td>
                    <td class="' . $remarks_class . '">' . htmlspecialchars($grade['remarks']) . '</td>
                    <td>' . htmlspecialchars($grade['school_year']) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">No reviewed grades available</td></tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Generated on ' . date('F d, Y h:i A') . '</p>
            <p>This is an official document from the Grading Management System</p>
        </div>';
        
        // Output HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Clean any remaining output buffers before sending PDF
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Close and output PDF document
        $filename = 'Grade_Report_' . $student['last_name'] . '_' . date('Ymd') . '.pdf';
        $pdf->Output($filename, 'D'); // D = force download
        exit;
    }

    /** Get Notifications */
    public function notifications()
    {
        $auth_id = $this->session->userdata('user_id');
        
        // Get notifications for this student
        $notifications = $this->Notification_model->get_notifications($auth_id, 50);
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        exit;
    }

    /** Mark Notification as Read */
    public function mark_notification_read($notification_id = null)
    {
        if (!$notification_id) {
            echo json_encode(['success' => false, 'message' => 'Notification ID required']);
            exit;
        }
        
        $auth_id = $this->session->userdata('user_id');
        
        // Verify notification belongs to this user
        $notification = $this->db->table('notifications')
            ->where('id', '=', $notification_id)
            ->where('recipient_id', '=', $auth_id)
            ->get();
        
        if (!$notification) {
            echo json_encode(['success' => false, 'message' => 'Notification not found']);
            exit;
        }
        
        $result = $this->Notification_model->mark_as_read($notification_id);
        
        if ($result) {
            $unread_count = $this->Notification_model->get_unread_count($auth_id);
            echo json_encode(['success' => true, 'message' => 'Notification marked as read', 'unread_count' => $unread_count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
        }
        exit;
    }

    /** Mark All Notifications as Read */
    public function mark_all_read()
    {
        $auth_id = $this->session->userdata('user_id');
        
        $result = $this->Notification_model->mark_all_read($auth_id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read', 'unread_count' => 0]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notifications']);
        }
        exit;
    }

    // ========== MESSAGING SYSTEM ==========

    public function messages()
    {
        $auth_id = $this->session->userdata('user_id');
        
        $this->call->model('Notification_model');
        
        $data = [
            'page' => 'Messages',
            'threads' => $this->Notification_model->get_threads($auth_id),
            'messageable_users' => $this->Notification_model->get_messageable_users($auth_id, 'student'),
            'unread_count' => $this->Notification_model->get_unread_thread_count($auth_id)
        ];
        
        $this->call->view('student/messages', $data);
    }

    public function view_thread($thread_id)
    {
        $auth_id = $this->session->userdata('user_id');
        
        $this->call->model('Notification_model');
        
        $thread = $this->Notification_model->get_thread($thread_id, $auth_id);
        $messages = $this->Notification_model->get_thread_messages($thread_id, $auth_id);
        
        if (!$thread || !$messages) {
            $this->call->view('student/view_thread', [
                'page' => 'Conversation',
                'thread' => null,
                'messages' => null
            ]);
            return;
        }
        
        // Mark messages as read
        $this->Notification_model->mark_thread_as_read($thread_id, $auth_id);
        
        $data = [
            'page' => 'Conversation',
            'thread' => $thread,
            'messages' => $messages
        ];
        
        $this->call->view('student/view_thread', $data);
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
        
        redirect('student/messages');
    }

    /** AI Chatbot - Using Free Gemini API */
    public function chatbot()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $question = trim($this->io->post('question'));
        
        if (empty($question)) {
            echo json_encode(['success' => false, 'message' => 'Question is required']);
            exit;
        }

        // System context for the AI
        $context = "You are a helpful assistant for a Student Grading Management System. " .
                   "Answer questions about viewing grades, downloading reports, updating profiles, " .
                   "contacting teachers, and general system usage. Keep responses concise and helpful. " .
                   "Use emojis appropriately.";

        // Call Gemini API (free tier)
        $answer = $this->callGeminiAPI($context, $question);

        if ($answer) {
            echo json_encode([
                'success' => true, 
                'answer' => $answer,
                'mode' => 'AI (Gemini)'
            ]);
        } else {
            // Fallback to rule-based responses
            $answer = $this->getRuleBasedAnswer($question);
            echo json_encode([
                'success' => true, 
                'answer' => $answer,
                'mode' => 'Rule-Based'
            ]);
        }
        exit;
    }

    /** Call Google Gemini API */
    private function callGeminiAPI($context, $question)
    {
        // Get API key from environment or config
        $api_key = getenv('GEMINI_API_KEY') ?: 'AIzaSyDhn2AuK8SKoEtZbA2D4DTYnxjwYiZBuXw';
        
        // If no valid API key, use fallback
        if (empty($api_key) || strlen($api_key) < 30) {
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $context . "\n\nUser question: " . $question]
                    ]
                ]
            ]
        ];

        // Check if curl is available
        if (!function_exists('curl_init')) {
            return null; // Curl not available, use fallback
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local dev
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log error for debugging (optional)
        if ($error) {
            error_log("Gemini API Error: " . $error);
            return null;
        }

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        return null;
    }

    /** Fallback rule-based chatbot */
    private function getRuleBasedAnswer($question)
    {
        $q = strtolower($question);
        
        // Grading system explanation
        if (strpos($q, 'grading') !== false || strpos($q, 'how') !== false && strpos($q, 'work') !== false) {
            return '📚 <strong>How Grading Works:</strong><br><br>' .
                   '<strong>Grade Components:</strong><br>' .
                   '• <strong>Prelim</strong> - First quarter grade<br>' .
                   '• <strong>Midterm</strong> - Second quarter grade<br>' .
                   '• <strong>Finals</strong> - Third quarter grade<br><br>' .
                   '<strong>Final Grade Calculation:</strong><br>' .
                   'Final Grade = (Prelim + Midterm + Finals) ÷ 3<br><br>' .
                   '<strong>Remarks:</strong><br>' .
                   '• <strong>Passed</strong> - Final grade ≥ 75<br>' .
                   '• <strong>Failed</strong> - Final grade < 75<br>' .
                   '• <strong>Incomplete</strong> - Missing grades<br><br>' .
                   '<strong>Grade Status:</strong><br>' .
                   '• <strong>Draft</strong> - Teacher is still entering grades<br>' .
                   '• <strong>Submitted</strong> - Pending admin review<br>' .
                   '• <strong>Reviewed</strong> - Final and visible to students<br><br>' .
                   '📊 You can only view grades with "Reviewed" status in your "My Grades" page!';
        }
        // Calculation question
        else if (strpos($q, 'calculate') !== false || strpos($q, 'computation') !== false || strpos($q, 'formula') !== false) {
            return '🧮 <strong>Grade Calculation Formula:</strong><br><br>' .
                   'Final Grade = (Prelim + Midterm + Finals) ÷ 3<br><br>' .
                   '<strong>Example:</strong><br>' .
                   'Prelim: 85<br>' .
                   'Midterm: 90<br>' .
                   'Finals: 88<br>' .
                   'Final Grade = (85 + 90 + 88) ÷ 3 = <strong>87.67</strong><br><br>' .
                   'The system automatically calculates your final grade when all three components are entered by your teacher!';
        }
        // Passing grade
        else if (strpos($q, 'pass') !== false || strpos($q, 'passing') !== false) {
            return '✅ <strong>Passing Grade:</strong><br><br>' .
                   'You need a <strong>final grade of 75 or higher</strong> to pass a subject.<br><br>' .
                   'Final Grade ≥ 75 = <strong>Passed ✅</strong><br>' .
                   'Final Grade < 75 = <strong>Failed ❌</strong><br><br>' .
                   'Check your "My Grades" page to see your current standing in all subjects!';
        }
        // View grades
        else if (strpos($q, 'grade') !== false && strpos($q, 'view') !== false) {
            return '📊 To view your grades, click on "My Grades" in the navigation menu. You\'ll see all your reviewed grades with prelim, midterm, finals, and final grade.';
        }
        // View subjects
        else if (strpos($q, 'subject') !== false) {
            return '📚 To view your enrolled subjects, click on "My Subjects" in the navigation. You\'ll see all your subjects with teacher information, grade level, and semester details.';
        }
        // Contact teacher
        else if (strpos($q, 'contact') !== false && strpos($q, 'teacher') !== false) {
            return '✉️ You can find your teacher\'s information in the "My Subjects" section. Each subject card shows the teacher\'s name.';
        }
        // Download PDF
        else if (strpos($q, 'download') !== false || strpos($q, 'pdf') !== false || strpos($q, 'report') !== false) {
            return '📄 To download your grade report as PDF, go to "My Grades" and click the "Download PDF Report" button at the top right of the page. The PDF includes all your reviewed grades organized by school year.';
        }
        // Update profile
        else if (strpos($q, 'profile') !== false && strpos($q, 'update') !== false) {
            return '✏️ To update your profile, click on "My Profile" in the menu. You can edit your personal information like name, email, birthdate, and address. Note: Grade level and section can only be changed by administrators.';
        }
        // Password
        else if (strpos($q, 'password') !== false) {
            return '🔑 To change your password, go to your profile page and look for the "Change Password" section. You\'ll need to enter your current password and new password twice for confirmation.';
        }
        // Notifications
        else if (strpos($q, 'notification') !== false) {
            return '🔔 Click the bell icon in the top right corner to view your notifications. You\'ll receive notifications when teachers update your grades. You can mark them as read individually or all at once.';
        }
        // Status explanation
        else if (strpos($q, 'status') !== false || strpos($q, 'draft') !== false || strpos($q, 'submitted') !== false || strpos($q, 'reviewed') !== false) {
            return '📋 <strong>Grade Status Explained:</strong><br><br>' .
                   '• <strong>Draft</strong> - Teacher is still working on grades. Not visible to students.<br><br>' .
                   '• <strong>Submitted</strong> - Teacher has submitted grades for admin review. Still not visible to students.<br><br>' .
                   '• <strong>Reviewed</strong> - Admin has approved the grades. NOW visible to students in "My Grades"!<br><br>' .
                   'You can only see grades that have been <strong>reviewed and approved</strong> by administrators.';
        }
        // Default response
        else {
            return '🤔 I\'m here to help! Here are some questions I can answer:<br><br>' .
                   '📚 <strong>About Grading:</strong><br>' .
                   '• "How does grading work?"<br>' .
                   '• "How is final grade calculated?"<br>' .
                   '• "What is the passing grade?"<br>' .
                   '• "What do grade statuses mean?"<br><br>' .
                   '📊 <strong>Common Tasks:</strong><br>' .
                   '• "How do I view my grades?"<br>' .
                   '• "How do I view my subjects?"<br>' .
                   '• "How do I download my grade report?"<br>' .
                   '• "How do I update my profile?"<br>' .
                   '• "How do I contact my teacher?"<br><br>' .
                   'Just ask me anything!';
        }
    }
}