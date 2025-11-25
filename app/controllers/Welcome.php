<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('session');
        $this->call->helper('url');
    }

    public function index()
    {
        // Check if user is logged in
        if ($this->session->userdata('logged_in')) {
            // User is logged in, redirect based on role
            $role = $this->session->userdata('role');
            switch ($role) {
                case 'admin':
                    redirect('admin/');
                    break;
                case 'teacher':
                    redirect('teacher/subjects');
                    break;
                case 'student':
                    redirect('student/dashboard');
                    break;
                default:
                    redirect('auth/profile');
            }
        } else {
            // User is not logged in, redirect to login
            redirect('auth/login');
        }
    }
}