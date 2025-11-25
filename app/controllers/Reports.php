<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Reports extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Reports_model', 'reports');
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

    public function index()
    {
        $data['page_title'] = 'Reports';
        
        // Get filter parameters with default empty strings
        $filters = [
            'date_from' => $this->io->get('date_from') ?: '',
            'date_to' => $this->io->get('date_to') ?: '',
            'subject_id' => $this->io->get('subject_id') ?: '',
            'teacher_id' => $this->io->get('teacher_id') ?: '',
            'section' => $this->io->get('section') ?: ''
        ];
        
        // Create filtered version for queries (remove empty values)
        $query_filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        // Get report data
        $data['summary'] = $this->reports->get_summary();
        $data['grade_statistics'] = $this->reports->get_grade_statistics($query_filters);
        $data['submission_status'] = $this->reports->get_submission_status($query_filters);
        $data['teacher_performance'] = $this->reports->get_teacher_performance($query_filters);
        
        // Get filter options
        $data['subjects'] = $this->reports->get_all_subjects();
        $data['teachers'] = $this->reports->get_all_teachers();
        $data['sections'] = $this->reports->get_all_sections();
        $data['filters'] = $filters; // Pass full array with all keys
        
        $this->call->view('admin/reports', $data);
    }

    public function export_csv()
    {
        // Get filter parameters with default empty strings
        $filters = [
            'date_from' => $this->io->get('date_from') ?: '',
            'date_to' => $this->io->get('date_to') ?: '',
            'subject_id' => $this->io->get('subject_id') ?: '',
            'teacher_id' => $this->io->get('teacher_id') ?: '',
            'section' => $this->io->get('section') ?: ''
        ];
        
        // Remove null/empty filters for query
        $query_filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        $data = $this->reports->get_grade_statistics($query_filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="grades_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['Subject', 'Teacher', 'Students', 'Avg Grade', 'Submission Status']);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['subject_name'],
                $row['teacher_name'],
                $row['total_students'],
                number_format($row['avg_grade'], 2),
                $row['submission_percentage'] . '%'
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function export_pdf()
    {
        // Note: This requires a PDF library like TCPDF or FPDF
        // For now, we'll provide a simple implementation
        $this->session->set_flashdata('info', 'PDF export feature coming soon. Please use CSV export.');
        redirect('reports');
    }
}
