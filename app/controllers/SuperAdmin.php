<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class SuperAdmin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('Student_model', 'student');
        $this->call->model('Auth_model', 'auth');
        $this->call->library('pagination');
        $this->call->library('session');
        $this->call->library('upload');
        $this->call->helper('url');
        
        $this->pagination->set_theme('custom');
        $this->pagination->set_custom_classes([
            'nav'    => 'flex justify-center mt-6',
            'ul'     => 'inline-flex items-center space-x-1',
            'li'     => 'inline',
            'a'      => 'px-3 py-1 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-blue-500 hover:text-white transition',
            'active' => 'px-7 py-1 rounded-md border border-blue-500 bg-blue text-black font-bold'
        ]);
    }

    /**
     * Check if user is logged in
     */
    private function check_auth()
    {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Please login to access this page.');
            redirect('auth/login');
        }
    }

    /**
     * Check if user is admin
     */
    private function check_admin()
    {
        $this->check_auth();
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Access denied. Super Admin privileges required.');
            redirect('');
        }
    }

    /**
     * Super Admin Dashboard - View all users
     */
    public function index($page = 1)
    {
        $this->check_admin();
        
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $allowed_per_page = [10, 25, 50, 100];
        if (!in_array($per_page, $allowed_per_page)) {
            $per_page = 10;
        }

        // Handle search (from query string)
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Get ALL users from all tables (students, teachers, admins)
        $all_users = $this->get_all_system_users($search);
        
        // Pagination manually
        $total_rows = count($all_users);
        $offset = ($page - 1) * $per_page;
        $paginated_users = array_slice($all_users, $offset, $per_page);
        
        // Init pagination
        $pagination_data = $this->pagination->initialize(
            $total_rows,
            $per_page,
            $page,
            'superadmin/index',
            5
        );
        
        $data['users'] = $paginated_users;
        $data['total_records'] = $total_rows;
        $data['pagination_data'] = $pagination_data;
        $data['pagination_links'] = $this->pagination->paginate();
        $data['search'] = $search;
        $data['per_page'] = $per_page;
        $data['is_admin'] = true;

        $this->call->view('superadmin/index', $data);
    }

    /**
     * Get all users from all tables (students, teachers, admins)
     */
    private function get_all_system_users($search = '')
    {
        $all_users = [];
        
        // Get all auth records with their associated data
        $query = "SELECT 
                    a.id as auth_id,
                    a.username,
                    a.role,
                    a.profile_image,
                    a.created_at,
                    a.student_id,
                    a.teacher_id,
                    COALESCE(s.id, t.id) as user_id,
                    COALESCE(s.first_name, t.first_name) as first_name,
                    COALESCE(s.last_name, t.last_name) as last_name,
                    COALESCE(s.email, t.email) as email,
                    s.deleted_at as student_deleted_at
                FROM auth a
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE 1=1";
        
        // Add search filter
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $query .= " AND (
                a.username LIKE '%{$search}%' OR
                COALESCE(s.first_name, t.first_name) LIKE '%{$search}%' OR
                COALESCE(s.last_name, t.last_name) LIKE '%{$search}%' OR
                COALESCE(s.email, t.email) LIKE '%{$search}%' OR
                a.role LIKE '%{$search}%'
            )";
        }
        
        $query .= " ORDER BY a.created_at DESC";
        
        $result = $this->db->raw($query);
        
        if ($result) {
            foreach ($result as $row) {
                $all_users[] = [
                    'id' => $row['user_id'] ?? $row['auth_id'],
                    'auth_id' => $row['auth_id'],
                    'student_id' => $row['student_id'],
                    'teacher_id' => $row['teacher_id'],
                    'username' => $row['username'],
                    'first_name' => $row['first_name'] ?? 'N/A',
                    'last_name' => $row['last_name'] ?? '',
                    'email' => $row['email'] ?? 'N/A',
                    'role' => $row['role'],
                    'profile_image' => $row['profile_image'],
                    'created_at' => $row['created_at'],
                    'is_deleted' => !empty($row['student_deleted_at'])
                ];
            }
        }
        
        return $all_users;
    }

    /**
     * Edit user (student, teacher, or admin)
     */
    public function edit($auth_id)
    {
        $this->check_admin();
        
        if ($_POST) {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';

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

            if (empty($errors)) {
                try {
                    // Get user data
                    $user = $this->get_user_by_auth_id($auth_id);
                    
                    if (!$user) {
                        $this->session->set_flashdata('error', 'User not found');
                        redirect('superadmin');
                    }

                    // Update auth table
                    $auth_data = [
                        'username' => $username,
                        'role' => $role,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if (!empty($password)) {
                        $auth_data['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }

                    $this->db->table('auth')->where('id', $auth_id)->update($auth_data);

                    // Update student or teacher table
                    $user_data = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($user['student_id']) {
                        $this->db->table('students')->where('id', $user['student_id'])->update($user_data);
                    } elseif ($user['teacher_id']) {
                        // For teachers, also update contact_number and specialization if provided
                        if (isset($_POST['contact_number'])) {
                            $user_data['contact_number'] = $_POST['contact_number'];
                        }
                        if (isset($_POST['specialization'])) {
                            $user_data['specialization'] = $_POST['specialization'];
                        }
                        $this->db->table('teachers')->where('id', $user['teacher_id'])->update($user_data);
                    }

                    $this->session->set_flashdata('success', 'User updated successfully!');
                    redirect('superadmin');
                } catch (Exception $e) {
                    $this->session->set_flashdata('error', 'Failed to update user: ' . $e->getMessage());
                }
            } else {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            }
        }
        
        $data['user'] = $this->get_user_by_auth_id($auth_id);
        
        if (!$data['user']) {
            $this->session->set_flashdata('error', 'User not found');
            redirect('superadmin');
        }
        
        $this->call->view('superadmin/edit', $data);
    }

    /**
     * Delete user (soft delete for students)
     */
    public function delete($auth_id)
    {
        $this->check_admin();
        
        try {
            $user = $this->get_user_by_auth_id($auth_id);
            
            if (!$user) {
                $this->session->set_flashdata('error', 'User not found');
                redirect('superadmin');
            }

            // Soft delete students
            if ($user['student_id']) {
                $this->db->table('students')
                    ->where('id', $user['student_id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                $this->session->set_flashdata('success', 'Student archived successfully!');
            } 
            // For teachers and admins, you might want to handle differently
            else {
                $this->session->set_flashdata('warning', 'Only students can be soft deleted. To remove teachers/admins, contact system administrator.');
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Failed to delete user: ' . $e->getMessage());
        }
        
        redirect('superadmin');
    }

    /**
     * Restore deleted student
     */
    public function restore($auth_id)
    {
        $this->check_admin();
        
        try {
            $user = $this->get_user_by_auth_id($auth_id);
            
            if (!$user) {
                $this->session->set_flashdata('error', 'User not found');
                redirect('superadmin');
            }

            if ($user['student_id']) {
                $this->db->table('students')
                    ->where('id', $user['student_id'])
                    ->update(['deleted_at' => NULL]);
                $this->session->set_flashdata('success', 'Student restored successfully!');
            } else {
                $this->session->set_flashdata('error', 'Only students can be restored.');
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Failed to restore user: ' . $e->getMessage());
        }
        
        redirect('superadmin');
    }

    /**
     * Permanently delete user
     */
    public function permanent_delete($auth_id)
    {
        $this->check_admin();
        
        try {
            $user = $this->get_user_by_auth_id($auth_id);
            
            if (!$user) {
                $this->session->set_flashdata('error', 'User not found');
                redirect('superadmin');
            }

            $this->db->transaction();

            // Delete from student or teacher table first
            if ($user['student_id']) {
                $this->db->table('students')->where('id', $user['student_id'])->delete();
            } elseif ($user['teacher_id']) {
                $this->db->table('teachers')->where('id', $user['teacher_id'])->delete();
            }

            // Delete from auth table
            $this->db->table('auth')->where('id', $auth_id)->delete();

            $this->db->commit();
            $this->session->set_flashdata('success', 'User permanently deleted!');
        } catch (Exception $e) {
            $this->db->roll_back();
            $this->session->set_flashdata('error', 'Failed to permanently delete user: ' . $e->getMessage());
        }
        
        redirect('superadmin');
    }

    /**
     * Get user by auth ID
     */
    private function get_user_by_auth_id($auth_id)
    {
        $query = "SELECT 
                    a.id as auth_id,
                    a.username,
                    a.role,
                    a.profile_image,
                    a.student_id,
                    a.teacher_id,
                    COALESCE(s.id, t.id) as user_id,
                    COALESCE(s.first_name, t.first_name) as first_name,
                    COALESCE(s.last_name, t.last_name) as last_name,
                    COALESCE(s.email, t.email) as email,
                    t.contact_number,
                    t.specialization,
                    s.deleted_at as student_deleted_at
                FROM auth a
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN teachers t ON a.teacher_id = t.id
                WHERE a.id = ?";
        
        $result = $this->db->raw($query, [$auth_id]);
        
        if ($result && count($result) > 0) {
            $row = $result[0];
            return [
                'auth_id' => $row['auth_id'],
                'student_id' => $row['student_id'],
                'teacher_id' => $row['teacher_id'],
                'user_id' => $row['user_id'] ?? $row['auth_id'],
                'username' => $row['username'],
                'first_name' => $row['first_name'] ?? '',
                'last_name' => $row['last_name'] ?? '',
                'email' => $row['email'] ?? '',
                'role' => $row['role'],
                'profile_image' => $row['profile_image'],
                'contact_number' => $row['contact_number'] ?? '',
                'specialization' => $row['specialization'] ?? '',
                'is_deleted' => !empty($row['student_deleted_at'])
            ];
        }
        
        return null;
    }
}

