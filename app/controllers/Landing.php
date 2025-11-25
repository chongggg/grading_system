<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Landing extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Landing_model', 'landing');
        $this->call->library('session');
        $this->call->helper('url');
    }

    public function index()
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->session->userdata('logged_in')) {
            $role = $this->session->userdata('role');
            switch ($role) {
                case 'admin':
                    redirect('admin');
                    break;
                case 'teacher':
                    redirect('teacher/dashboard');
                    break;
                case 'student':
                    redirect('student/dashboard');
                    break;
                default:
                    // User role - waiting for approval
                    redirect('landing/waiting');
                    break;
            }
            return;
        }

        $data['page_title'] = 'Welcome';
        $data['announcements'] = $this->landing->getPublicAnnouncements(6);
        $data['stats'] = $this->landing->getSystemStats();
        
        $this->call->view('landing/index', $data);
    }

    public function waiting()
    {
        // Check if user is logged in with 'user' role
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            return;
        }

        $role = $this->session->userdata('role');
        
        // If approved, redirect to dashboard
        if ($role === 'student') {
            redirect('student/dashboard');
            return;
        }
        
        // If not user role, redirect appropriately
        if ($role !== 'user') {
            redirect('landing');
            return;
        }

        $data['page_title'] = 'Waiting for Approval';
        $data['user_name'] = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');
        
        $this->call->view('landing/waiting', $data);
    }
}
