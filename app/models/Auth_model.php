<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Auth_model extends Model
{
    protected $table = "auth";
    protected $primary_key = "id";
    protected $soft_delete = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Register a new user
     */
    public function register($student_data, $auth_data)
    {
        try {
            $this->db->transaction();
            
            // Insert student record
            $student_id = $this->db->table('students')->insert($student_data);
            
            if (!$student_id) {
                throw new Exception('Failed to create student record');
            }
            
            // Insert auth record
            $auth_data['student_id'] = $student_id;
            $auth_result = $this->db->table($this->table)->insert($auth_data);
            
            if (!$auth_result) {
                throw new Exception('Failed to create auth record');
            }
            
            $this->db->commit();
            return $student_id;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            return false;
        }
    }

    /**
     * Login user
     */
    public function login($username, $password)
    {
        // First try teachers and admins (users without student_id)
        $sql = "SELECT * FROM {$this->table} WHERE username = ? AND student_id IS NULL";
        $result = $this->db->raw($sql, [$username]);
        $staff = $result ? $result->fetch() : null;

        if ($staff && password_verify($password, $staff['password'])) {
            return $staff;
        }

        // If not found in staff, try students
        $student = $this->db->table($this->table)
            ->join('students', 'auth.student_id = students.id')
            ->where('auth.username', $username)
            ->where('students.deleted_at', null)
            ->get();
            
        if ($student && password_verify($password, $student['password'])) {
            return $student;
        }
        
        return false;
    }

    /**
     * Get user by student ID
     */
    public function get_user_by_student_id($student_id)
    {
        return $this->db->table($this->table)
            ->join('students', 'auth.student_id = students.id')
            ->where('auth.student_id', $student_id)
            ->where('students.deleted_at', null)
            ->get();
    }

    /**
     * Get auth with student details by student ID
     */
    public function get_auth_with_student_by_id($student_id)
    {
        // Join students and teachers to cover both account types
        return $this->db->table($this->table . ' a')
                        ->left_join('students s', 'a.student_id = s.id')
                        ->left_join('teachers t', 'a.teacher_id = t.id')
                        ->where('a.student_id', $student_id)
                        ->select('a.id as auth_id, a.student_id, a.username, a.password, a.role, a.profile_image, a.created_at as auth_created_at, a.updated_at as auth_updated_at, COALESCE(t.id, s.id) as id, COALESCE(t.first_name, s.first_name) as first_name, COALESCE(t.last_name, s.last_name) as last_name, COALESCE(t.email, s.email) as email, COALESCE(t.profile_image, s.profile_image) as student_profile_image')
                        ->get();
    }

    /**
     * Get auth record by student ID
     */
    public function get_auth_by_student_id($student_id)
    {
        return $this->db->table($this->table)
                        ->where('student_id', $student_id)
                        ->get();
    }

    /**
     * Get auth with student details
     */
    public function get_auth_with_student($username)
    {
        // Join both students and teachers so we can resolve name/email regardless of whether
        // the auth account points to a student row or a teacher row (via teacher_id)
        return $this->db->table($this->table . ' a')
                        ->left_join('students s', 'a.student_id = s.id')
                        ->left_join('teachers t', 'a.teacher_id = t.id')
                        ->where('a.username', $username)
                        ->select('a.*, COALESCE(t.first_name, s.first_name) as first_name, COALESCE(t.last_name, s.last_name) as last_name, COALESCE(t.email, s.email) as email, COALESCE(t.profile_image, s.profile_image) as student_profile_image')
                        ->get();
    }

    /**
     * Update user profile
     */
    public function update_profile($student_id, $student_data, $auth_data = null)
    {
        try {
            $this->db->transaction();
            
            // Update student record
            $student_result = $this->db->table('students')
                ->where('id', $student_id)
                ->update($student_data);
            
            if (!$student_result) {
                throw new Exception('Failed to update student record');
            }
            
            // Update auth record if provided
            if ($auth_data) {
                $auth_result = $this->db->table($this->table)
                    ->where('student_id', $student_id)
                    ->update($auth_data);
                
                if (!$auth_result) {
                    throw new Exception('Failed to update auth record');
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            return false;
        }
    }

    /**
     * Check if username exists
     */
    public function username_exists($username, $exclude_id = null)
    {
        $query = $this->db->table($this->table)->where('username', $username);
        
        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }
        
        return $query->get() ? true : false;
    }

    /**
     * Get auth record by auth ID
     */
    public function get_auth_by_id($auth_id)
    {
        return $this->db->table($this->table)
                        ->where('id', $auth_id)
                        ->get();
    }

    /**
     * Get teacher profile by auth ID
     */
    public function get_teacher_profile($auth_id)
    {
        return $this->db->table($this->table . ' a')
                        ->join('teachers t', 'a.teacher_id = t.id')
                        ->where('a.id', $auth_id)
                        ->select('a.id as auth_id, a.teacher_id, a.username, a.role, a.profile_image as auth_profile_image, a.created_at as auth_created_at, t.id as id, t.first_name, t.last_name, t.email, t.contact_number, t.specialization, t.profile_image, t.created_at, t.updated_at')
                        ->get();
    }

    /**
     * Update teacher profile
     */
    public function update_teacher_profile($teacher_id, $teacher_data, $auth_data = null, $auth_id = null)
    {
        try {
            $this->db->transaction();
            
            // Update teacher record
            $teacher_result = $this->db->table('teachers')
                ->where('id', $teacher_id)
                ->update($teacher_data);
            
            if (!$teacher_result) {
                throw new Exception('Failed to update teacher record');
            }
            
            // Update auth record if provided
            if ($auth_data && $auth_id) {
                $auth_result = $this->db->table($this->table)
                    ->where('id', $auth_id)
                    ->update($auth_data);
                
                if (!$auth_result) {
                    throw new Exception('Failed to update auth record');
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            return false;
        }
    }

    /**
     * Check if teacher email exists
     */
    public function teacher_email_exists($email, $exclude_teacher_id = null)
    {
        $query = $this->db->table('teachers')->where('email', $email);
        
        if ($exclude_teacher_id) {
            $query->where('id', '!=', $exclude_teacher_id);
        }
        
        return $query->get() ? true : false;
    }

    /**
     * Get all users with student info (for admin)
     */
    public function get_all_users()
    {
        return $this->db->table($this->table)
            ->join('students', 'auth.student_id = students.id')
            ->where('students.deleted_at', null)
            ->order_by('students.created_at', 'DESC')
            ->get_all();
    }

    /**
     * Delete user (soft delete)
     */
    public function delete_user($student_id)
    {
        try {
            $this->db->transaction();
            
            // Soft delete student (auth table doesn't have deleted_at column)
            $student_result = $this->db->table('students')
                ->where('id', $student_id)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);
            
            if (!$student_result) {
                throw new Exception('Failed to delete student record');
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            return false;
        }
    }

    /**
     * Restore user (un-soft delete)
     */
    public function restore_user($student_id)
    {
        try {
            $this->db->transaction();
            
            // Use the Student model's restore method
            $student_result = $this->db->table('students')
                ->where('id', $student_id)
                ->update(['deleted_at' => NULL]);
            
            if (!$student_result) {
                throw new Exception('Failed to restore student record');
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log('Restore user error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Force delete user (permanent delete)
     */
    public function force_delete_user($student_id)
    {
        try {
            $this->db->transaction();
            
            // Delete auth record first (due to foreign key constraint)
            $auth_result = $this->db->table($this->table)
                ->where('student_id', $student_id)
                ->delete();
            
            // Delete student record
            $student_result = $this->db->table('students')
                ->where('id', $student_id)
                ->delete();
            
            if (!$student_result) {
                throw new Exception('Failed to permanently delete student record');
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log('Force delete user error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update password
     */
    public function update_password($student_id, $new_password)
    {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        return $this->db->table($this->table)
            ->where('student_id', $student_id)
            ->update(['password' => $hashed_password]);
    }

    /**
     * Dashboard Statistics
     */
    public function count_students()
    {
        $sql = "SELECT COUNT(*) as total FROM students WHERE deleted_at IS NULL";
        $result = $this->db->raw($sql);
        if ($result) {
            $row = $result->fetch();
            return $row ? (int)$row['total'] : 0;
        }
        return 0;
    }

    public function count_teachers()
    {
        $result = $this->db->table('teachers')->get_all();
        return count($result);
    }

    public function count_subjects()
    {
        $result = $this->db->table('subjects')->get_all();
        return count($result);
    }

    public function get_recent_logs($limit = 10)
    {
        return $this->db->table('audit_logs al')
            ->join('auth a', 'al.user_id = a.id')
            ->join('students s', 'a.student_id = s.id')
            ->select('al.*, CONCAT(s.first_name, " ", s.last_name) as user_name')
            ->order_by('al.timestamp', 'DESC')
            ->limit($limit)
            ->get_all();
    }

    public function get_recent_announcements($limit = 5)
    {
        return $this->db->table('announcements an')
            ->join('auth a', 'an.author_id = a.id')
            ->join('students s', 'a.student_id = s.id')
            ->select('an.*, CONCAT(s.first_name, " ", s.last_name) as author_name')
            ->order_by('an.created_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }

    public function get_recent_grades($limit = 5)
    {
        return $this->db->table('grades g')
            ->join('students s', 'g.student_id = s.id')
            ->join('subjects sub', 'g.subject_id = sub.id')
            ->select('g.*, CONCAT(s.first_name, " ", s.last_name) as student_name, sub.subject_name')
            ->order_by('g.updated_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }

    /**
     * Subjects helper methods
     */
    public function get_all_subjects($filters = [])
    {
        $query = $this->db->table('subjects');
        if (!empty($filters['grade_level'])) {
            $query = $query->where('grade_level', $filters['grade_level']);
        }
        if (!empty($filters['semester'])) {
            $query = $query->where('semester', $filters['semester']);
        }
        return $query->get_all();
    }

    public function get_subject($id)
    {
        return $this->db->table('subjects')->where('id', $id)->get();
    }

    public function get_subject_by_code($code)
    {
        return $this->db->table('subjects')->where('subject_code', $code)->get();
    }

    /**
     * Returns true if the subjects table has a teacher_id column.
     */
    protected function subjects_has_teacher_column()
    {
        // Try a lightweight select of the column; if the column doesn't exist the DB layer should throw
        try {
            $this->db->table('subjects')->select('teacher_id')->limit(1)->get();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function create_subject($data)
    {
        $data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');

        // If subjects table doesn't have teacher_id column (older schema), drop it from payload
        if (isset($data['teacher_id']) && !$this->subjects_has_teacher_column()) {
            unset($data['teacher_id']);
        }

        return $this->db->table('subjects')->insert($data);
    }

    public function update_subject($id, $data)
    {
        $data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');

        // If subjects table doesn't have teacher_id column (older schema), drop it from payload
        if (isset($data['teacher_id']) && !$this->subjects_has_teacher_column()) {
            unset($data['teacher_id']);
        }

        return $this->db->table('subjects')->where('id', $id)->update($data);
    }

    public function delete_subject($id)
    {
        return $this->db->table('subjects')->where('id', $id)->delete();
    }

    /**
     * Class assignments: assign a teacher to a subject (upsert behavior)
     */
    public function get_class_assignment($subject_id, $school_year = null, $semester = null, $section = null)
    {
        $q = $this->db->table('class_assignments')->where('subject_id', $subject_id);
        if ($school_year) $q->where('school_year', $school_year);
        if ($semester) $q->where('semester', $semester);
        if ($section) $q->where('section', $section);
        return $q->get();
    }

    public function upsert_class_assignment($subject_id, $teacher_id, $section = null, $school_year = null, $semester = '1st')
    {
        // Try to find existing assignment for same subject + year + semester + section
        $existing = $this->get_class_assignment($subject_id, $school_year, $semester, $section);
        $payload = [
            'teacher_id' => $teacher_id,
            'subject_id' => $subject_id,
            'section' => $section,
            'school_year' => $school_year,
            'semester' => $semester,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            // update
            return $this->db->table('class_assignments')->where('id', $existing['id'])->update([
                'teacher_id' => $teacher_id,
                'section' => $section,
                'school_year' => $school_year,
                'semester' => $semester,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->db->table('class_assignments')->insert($payload);
    }

    /**
     * Count distinct subjects assigned to a teacher
     */
    public function count_teacher_subjects($teacher_id)
    {
        $subjects = [];

        // class_assignments
        $rows = $this->db->table('class_assignments')->where('teacher_id', $teacher_id)->get_all();
        if ($rows) {
            foreach ($rows as $r) {
                if (!empty($r['subject_id'])) $subjects[] = $r['subject_id'];
            }
        }

        // subjects.teacher_id (normalized or legacy auth id)
        $s = $this->db->table('subjects')->where('teacher_id', $teacher_id)->get_all();
        if ($s) {
            foreach ($s as $row) {
                if (!empty($row['id'])) $subjects[] = $row['id'];
            }
        }

        $subjects = array_filter(array_unique($subjects));
        return count($subjects);
    }

    /**
     * Count distinct students under a teacher (based on grades table)
     */
    public function count_teacher_students($teacher_id)
    {
        $rows = $this->db->table('grades')
            ->where('teacher_id', $teacher_id)
            ->get_all();

        if (!$rows) return 0;

        $students = array_map(function ($r) {
            return isset($r['student_id']) ? $r['student_id'] : null;
        }, $rows);

        return count(array_unique($students));
    }

    /**
     * Count pending grades (status = 'Draft') for a teacher
     */
    public function count_pending_grades($teacher_id)
    {
        $rows = $this->db->table('grades')
            ->where('teacher_id', $teacher_id)
            ->where('status', 'Draft')
            ->get_all();

        return $rows ? count($rows) : 0;
    }

    /**
     * Get recent teacher activities (simple feed from grades updates)
     */
    public function get_teacher_activities($teacher_id, $limit = 10)
    {
        return $this->db->table('grades g')
            ->join('students s', 'g.student_id = s.id')
            ->join('subjects sub', 'g.subject_id = sub.id')
            ->select('g.updated_at as date, sub.subject_name as subject_name, CONCAT(s.first_name, " ", s.last_name, " grade updated") as description')
            ->where('g.teacher_id', $teacher_id)
            ->order_by('g.updated_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }

    /**
     * Get teacher subjects with enrolled students and their grades
     * Returns an array of subject objects where each subject has ->students (array)
     */
    public function get_teacher_subjects_with_students($teacher_id)
    {
        // Resolve potential teachers.id from auth user id
        $resolved_teacher_id = $this->get_teacher_id_from_auth($teacher_id) ?: $teacher_id;

        // Get all subjects for this teacher directly from subjects table
        $sql = "SELECT DISTINCT s.*, 
                       sections.id as section_id,
                       sections.section_name,
                       sections.grade_level as section_grade
                FROM subjects s
                LEFT JOIN students st ON st.grade_level = s.grade_level AND (st.deleted_at IS NULL OR st.deleted_at = '')
                LEFT JOIN sections ON sections.id = st.section_id
                WHERE s.teacher_id = ?
                AND sections.id IS NOT NULL
                ORDER BY s.subject_code, sections.section_name";
        
        $stmt = $this->db->raw($sql, [$resolved_teacher_id]);
        $subjects = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if (empty($subjects)) return [];

        $result = [];
        foreach ($subjects as $sub) {
            $section_id = $sub['section_id'];
            $section_name = $sub['section_name'];
            
            // Get students enrolled in this subject AND assigned to this specific section
            $sql_students = "SELECT DISTINCT st.id, st.first_name, st.last_name, st.section_id
                           FROM student_subjects ss
                           INNER JOIN students st ON ss.student_id = st.id
                           WHERE ss.subject_id = ?
                           AND st.section_id = ?
                           AND (st.deleted_at IS NULL OR st.deleted_at = '')
                           ORDER BY st.last_name, st.first_name";
            
            $stmt_students = $this->db->raw($sql_students, [$sub['id'], $section_id]);
            $enrolled = $stmt_students ? $stmt_students->fetchAll(PDO::FETCH_ASSOC) : [];

            // Fetch grades for students in this section
            $sql_grades = "SELECT st.id, st.first_name, st.last_name, 
                                  g.prelim, g.midterm, g.finals, g.final_grade, g.teacher_id
                           FROM grades g
                           INNER JOIN students st ON g.student_id = st.id
                           WHERE g.subject_id = ?
                           AND st.section_id = ?
                           AND (st.deleted_at IS NULL OR st.deleted_at = '')";
            
            $stmt_grades = $this->db->raw($sql_grades, [$sub['id'], $section_id]);
            $allGrades = $stmt_grades ? $stmt_grades->fetchAll(PDO::FETCH_ASSOC) : [];

            // Index grades by student id for quick lookup
            $gradesIndex = [];
            foreach ($allGrades as $gr) {
                if (empty($gr['teacher_id']) || $gr['teacher_id'] == $resolved_teacher_id || $gr['teacher_id'] == $teacher_id) {
                    $gradesIndex[$gr['id']] = $gr;
                }
            }

            $student_list = [];

            // Add enrolled students first (will include those without grades)
            if (!empty($enrolled)) {
                foreach ($enrolled as $st) {
                    $g = isset($gradesIndex[$st['id']]) ? $gradesIndex[$st['id']] : null;
                    $grades = [];
                    $grades[1] = $g && isset($g['prelim']) ? (float)$g['prelim'] : null;
                    $grades[2] = $g && isset($g['midterm']) ? (float)$g['midterm'] : null;
                    $grades[3] = $g && isset($g['finals']) ? (float)$g['finals'] : null;
                    $grades[4] = $g && isset($g['finals']) ? (float)$g['finals'] : null;

                    $student_list[] = (object)[
                        'id' => $st['id'],
                        'first_name' => $st['first_name'],
                        'last_name' => $st['last_name'],
                        'grades' => $grades,
                        'final_grade' => $g && isset($g['final_grade']) ? (float)$g['final_grade'] : null
                    ];
                    // mark consumed
                    if (isset($gradesIndex[$st['id']])) unset($gradesIndex[$st['id']]);
                }
            }

            // Add remaining students that have grades but were not in enrollments
            if (!empty($gradesIndex)) {
                foreach ($gradesIndex as $gr) {
                    $grades = [];
                    $grades[1] = isset($gr['prelim']) ? (float)$gr['prelim'] : null;
                    $grades[2] = isset($gr['midterm']) ? (float)$gr['midterm'] : null;
                    $grades[3] = isset($gr['finals']) ? (float)$gr['finals'] : null;
                    $grades[4] = isset($gr['finals']) ? (float)$gr['finals'] : null;

                    $student_list[] = (object)[
                        'id' => $gr['id'],
                        'first_name' => $gr['first_name'],
                        'last_name' => $gr['last_name'],
                        'grades' => $grades,
                        'final_grade' => isset($gr['final_grade']) ? (float)$gr['final_grade'] : null
                    ];
                }
            }

            // Create a unique entry for this subject-section combination
            $subObj = (object)[
                'id' => $sub['id'],
                'name' => $sub['subject_name'] ?? $sub['name'] ?? 'Unknown',
                'code' => $sub['subject_code'] ?? $sub['code'] ?? null,
                'description' => $sub['description'] ?? null,
                'section_id' => $section_id,
                'section_name' => $section_name,
                'display_name' => ($sub['subject_code'] ?? $sub['code'] ?? 'Subject') . ' - ' . $section_name,
                'students' => $student_list,
                'grade_status_summary' => $this->get_subject_grade_status_summary_by_section($sub['id'], $resolved_teacher_id, $section_id)
            ];

            $result[] = $subObj;
        }

        return $result;
    }

    /**
     * Get grade status summary for a subject
     * Returns counts of Draft, Submitted, and Reviewed grades
     */
    public function get_subject_grade_status_summary($subject_id, $teacher_id)
    {
        $summary = [
            'Draft' => 0,
            'Submitted' => 0,
            'Reviewed' => 0,
            'total' => 0
        ];

        $grades = $this->db->table('grades')
            ->select('status')
            ->where('subject_id', $subject_id)
            ->where('teacher_id', $teacher_id)
            ->get_all();

        if ($grades) {
            foreach ($grades as $grade) {
                $status = $grade['status'] ?? 'Draft';
                if (isset($summary[$status])) {
                    $summary[$status]++;
                }
                $summary['total']++;
            }
        }

        return $summary;
    }

    /**
     * Get grade status summary for a subject filtered by section
     * Returns counts of Draft, Submitted, and Reviewed grades
     */
    public function get_subject_grade_status_summary_by_section($subject_id, $teacher_id, $section_id)
    {
        $summary = [
            'Draft' => 0,
            'Submitted' => 0,
            'Reviewed' => 0,
            'total' => 0
        ];

        $sql = "SELECT g.status 
                FROM grades g
                INNER JOIN students s ON g.student_id = s.id
                WHERE g.subject_id = ?
                AND g.teacher_id = ?
                AND s.section_id = ?
                AND (s.deleted_at IS NULL OR s.deleted_at = '')";
        
        $stmt = $this->db->raw($sql, [$subject_id, $teacher_id, $section_id]);
        $grades = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if ($grades) {
            foreach ($grades as $grade) {
                $status = $grade['status'] ?? 'Draft';
                if (isset($summary[$status])) {
                    $summary[$status]++;
                }
                $summary['total']++;
            }
        }

        return $summary;
    }

    /**
     * Get a single subject (assigned to teacher) with students and their grades
     */
    public function get_teacher_subject_with_students($teacher_id, $subject_id, $section_id = null)
    {
        // Try resolve teacher_id to teachers.id
        $resolved_teacher_id = $this->get_teacher_id_from_auth($teacher_id) ?: $teacher_id;

        // Try assignment by resolved id first (class_assignments)
        $sub = $this->db->table('class_assignments ca')
            ->join('subjects s', 'ca.subject_id = s.id')
            ->select('s.*, ca.id as assignment_id, ca.section, ca.school_year, ca.semester')
            ->where('ca.teacher_id', $resolved_teacher_id)
            ->where('s.id', $subject_id)
            ->get();

        // Fallback: maybe class_assignments stored auth id directly
        if (!$sub) {
            $sub = $this->db->table('class_assignments ca')
                ->join('subjects s', 'ca.subject_id = s.id')
                ->select('s.*, ca.id as assignment_id, ca.section, ca.school_year, ca.semester')
                ->where('ca.teacher_id', $teacher_id)
                ->where('s.id', $subject_id)
                ->get();
        }

        // Still not found? Try subjects.teacher_id column
        if (!$sub) {
            $sub = $this->db->table('subjects s')
                ->select('s.*, NULL as assignment_id, NULL as section, NULL as school_year, NULL as semester')
                ->where('s.id', $subject_id)
                ->where('s.teacher_id', $resolved_teacher_id)
                ->get();

            if (!$sub && $resolved_teacher_id != $teacher_id) {
                $sub = $this->db->table('subjects s')
                    ->select('s.*, NULL as assignment_id, NULL as section, NULL as school_year, NULL as semester')
                    ->where('s.id', $subject_id)
                    ->where('s.teacher_id', $teacher_id)
                    ->get();
            }
        }

        if (!$sub) return null;

        // Build student list: start with enrollments from student_subjects
        $enrolled = [];
        try {
            $query = $this->db->table('student_subjects ss')
                ->join('students st', 'ss.student_id = st.id')
                ->where('ss.subject_id', $sub['id']);
            
            // Apply section filter if provided
            if ($section_id !== null && $section_id !== '' && $section_id !== 'all') {
                $query->where('st.section_id', $section_id);
            }
            
            $enrolled = $query->select('st.id, st.first_name, st.last_name, st.email, st.section_id')
                ->get_all() ?: [];
            
            // Filter out deleted students in PHP
            if ($enrolled) {
                $enrolled = array_filter($enrolled, function($st) {
                    return empty($st['deleted_at']);
                });
            }
        } catch (Exception $e) {
            // student_subjects table might not exist
            $enrolled = [];
        }

        // Fetch all grades for this subject, then filter by teacher id(s)
        $gradesQuery = $this->db->table('grades g')
            ->join('students st', 'g.student_id = st.id')
            ->select('st.id, st.first_name, st.last_name, st.section_id, g.prelim, g.midterm, g.finals, g.final_grade, g.teacher_id, g.remarks, g.status')
            ->where('g.subject_id', $sub['id']);
        
        // Apply section filter to grades query as well
        if ($section_id !== null && $section_id !== '' && $section_id !== 'all') {
            $gradesQuery->where('st.section_id', $section_id);
        }
        
        $allGrades = $gradesQuery->get_all() ?: [];

        // Index grades by student id
        $gradesIndex = [];
        if ($allGrades) {
            foreach ($allGrades as $gr) {
                // include grade rows where teacher_id matches or is empty (support imports)
                if (!empty($gr['teacher_id']) && $gr['teacher_id'] != $resolved_teacher_id && $gr['teacher_id'] != $teacher_id) continue;
                $gradesIndex[$gr['id']] = $gr;
            }
        }

        $student_list = [];

        // Add enrolled students first (from student_subjects)
        if (!empty($enrolled)) {
            foreach ($enrolled as $st) {
                $g = isset($gradesIndex[$st['id']]) ? $gradesIndex[$st['id']] : null;
                $grades = [];
                $grades[1] = $g && isset($g['prelim']) ? (float)$g['prelim'] : null;
                $grades[2] = $g && isset($g['midterm']) ? (float)$g['midterm'] : null;
                $grades[3] = $g && isset($g['finals']) ? (float)$g['finals'] : null;
                $grades[4] = $g && isset($g['finals']) ? (float)$g['finals'] : null;

                $student_list[] = (object)[
                    'id' => $st['id'],
                    'first_name' => $st['first_name'],
                    'last_name' => $st['last_name'],
                    'grades' => $grades,
                    'final_grade' => $g && isset($g['final_grade']) ? (float)$g['final_grade'] : null,
                    'remarks' => $g && isset($g['remarks']) ? $g['remarks'] : 'Incomplete',
                    'status' => $g && isset($g['status']) ? $g['status'] : 'Draft'
                ];
                // Mark as consumed
                if (isset($gradesIndex[$st['id']])) unset($gradesIndex[$st['id']]);
            }
        }

        // Add remaining students that have grades but were not in enrollments
        if (!empty($gradesIndex)) {
            foreach ($gradesIndex as $gr) {
                $grades = [];
                $grades[1] = isset($gr['prelim']) ? (float)$gr['prelim'] : null;
                $grades[2] = isset($gr['midterm']) ? (float)$gr['midterm'] : null;
                $grades[3] = isset($gr['finals']) ? (float)$gr['finals'] : null;
                $grades[4] = isset($gr['finals']) ? (float)$gr['finals'] : null;

                $student_list[] = (object)[
                    'id' => $gr['id'],
                    'first_name' => $gr['first_name'],
                    'last_name' => $gr['last_name'],
                    'grades' => $grades,
                    'final_grade' => isset($gr['final_grade']) ? (float)$gr['final_grade'] : null,
                    'remarks' => isset($gr['remarks']) ? $gr['remarks'] : 'Incomplete',
                    'status' => isset($gr['status']) ? $gr['status'] : 'Draft'
                ];
            }
        }

        // If still no students and subject has grade_level, fall back to listing students in that grade level
        if (empty($student_list)) {
            try {
                if (!empty($sub['grade_level'])) {
                    $sql = "SELECT * FROM students WHERE grade_level = ? AND (deleted_at IS NULL OR deleted_at = '')";
                    $params = [$sub['grade_level']];
                    
                    // Apply section filter to fallback query as well
                    if ($section_id !== null && $section_id !== '' && $section_id !== 'all') {
                        $sql .= " AND section_id = ?";
                        $params[] = $section_id;
                    }
                    
                    $result = $this->db->raw($sql, $params);
                    $fallback_students = $result ? $result->fetchAll() : [];

                    foreach ($fallback_students as $fs) {
                        $student_list[] = (object)[
                            'id' => $fs['id'],
                            'first_name' => $fs['first_name'],
                            'last_name' => $fs['last_name'],
                            'grades' => [1 => null, 2 => null, 3 => null, 4 => null],
                            'final_grade' => null,
                            'remarks' => 'Incomplete',
                            'status' => 'Draft'
                        ];
                    }
                }
            } catch (Exception $e) {
                // ignore fallback errors
            }
        }

        return (object)[
            'id' => $sub['id'],
            'name' => $sub['subject_name'] ?? $sub['subject_name'],
            'code' => $sub['subject_code'] ?? null,
            'description' => $sub['description'] ?? null,
            'students' => $student_list
        ];
    }

    /**
     * Get sections that have students enrolled in this subject
     */
    public function get_sections_for_subject($subject_id)
    {
        try {
            $sections = $this->db->raw(
                "SELECT DISTINCT s.id, s.section_name, s.grade_level, 
                    COUNT(st.id) as student_count
                FROM sections s
                INNER JOIN students st ON st.section_id = s.id
                INNER JOIN student_subjects ss ON ss.student_id = st.id
                WHERE ss.subject_id = ? AND (st.deleted_at IS NULL OR st.deleted_at = '')
                GROUP BY s.id, s.section_name, s.grade_level
                ORDER BY s.grade_level, s.section_name",
                [$subject_id]
            );
            
            return $sections ? $sections->fetchAll() : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Verify that the given teacher (auth user id or teachers.id) is assigned to the subject
     */
    public function verify_teacher_subject($auth_user_id, $subject_id)
    {
        // First try mapping auth user -> teachers.id
        $teacher_id = $this->get_teacher_id_from_auth($auth_user_id);
        if ($teacher_id) {
            $assigned = $this->db->table('class_assignments')->where('teacher_id', $teacher_id)->where('subject_id', $subject_id)->get();
            if ($assigned) return true;
        }

        // Fallback: in some setups teacher_id might directly store auth id in class_assignments
        $direct = $this->db->table('class_assignments')->where('teacher_id', $auth_user_id)->where('subject_id', $subject_id)->get();
        if ($direct) return true;

        // Also support assignment via subjects.teacher_id
        if ($teacher_id) {
            $s = $this->db->table('subjects')->where('id', $subject_id)->where('teacher_id', $teacher_id)->get();
            if ($s) return true;
        }

        // Fallback: maybe subjects.teacher_id stores auth id directly
        $s2 = $this->db->table('subjects')->where('id', $subject_id)->where('teacher_id', $auth_user_id)->get();
        if ($s2) return true;

        return false;
    }

    /**
     * Resolve teachers.id from auth user id. Uses email mapping between auth->students.email and teachers.email
     */
    public function get_teacher_id_from_auth($auth_user_id)
    {
        // First check if auth has a teacher_id column populated
        $row = $this->db->table('auth')->select('teacher_id')->where('id', $auth_user_id)->get();
        if ($row && !empty($row['teacher_id'])) return $row['teacher_id'];
        // Fallbacks (safe, non-destructive heuristics):
        // 1) try linked student email (most reliable)
        // 2) if auth.username looks like an email, try matching that to teachers.email
        // 3) try exact name match against teachers.first_name + teachers.last_name

        $authRow = $this->db->table('auth a')
            ->left_join('students s', 'a.student_id = s.id')
            ->select('s.email, s.first_name, s.last_name, a.username')
            ->where('a.id', $auth_user_id)
            ->get();

        // 1) Linked student email
        if ($authRow && !empty($authRow['email'])) {
            $teacher = $this->db->table('teachers')->where('email', $authRow['email'])->get();
            if ($teacher) return $teacher['id'];
        }

        // 2) auth.username may be an email (some systems use email as username)
        $username = $authRow['username'] ?? null;
        if (!$username) {
            $a = $this->db->table('auth')->select('username')->where('id', $auth_user_id)->get();
            $username = $a['username'] ?? null;
        }

        if ($username && strpos($username, '@') !== false) {
            $teacher = $this->db->table('teachers')->where('email', $username)->get();
            if ($teacher) return $teacher['id'];
        }

        // 3) Try exact name match (first + last)
        $first = $authRow['first_name'] ?? null;
        $last = $authRow['last_name'] ?? null;
        if ($first || $last) {
            $q = $this->db->table('teachers');
            if ($first) $q->where('first_name', $first);
            if ($last) $q->where('last_name', $last);
            $t = $q->get();
            if ($t) return $t['id'];

            // try full name concatenation (DB-side)
            $full = trim(($first ?? '') . ' ' . ($last ?? ''));
            if ($full) {
                $t2 = $this->db->table('teachers')->where('CONCAT(first_name, " ", last_name)', $full)->get();
                if ($t2) return $t2['id'];
            }
        }

        return null;
    }

    /**
     * Update or insert student grade for a given period
     */
    public function update_student_grade($student_id, $subject_id, $period, $grade)
    {
        // Determine teacher id (resolve to teachers.id for FK compliance)
        $auth_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $teacher_ref = $this->get_teacher_id_from_auth($auth_user_id) ?: $auth_user_id;

        // Find existing grade row
        $existing = $this->db->table('grades')->where('student_id', $student_id)->where('subject_id', $subject_id)->get();

        $field = null;
        switch ($period) {
            case 1: $field = 'prelim'; break;
            case 2: $field = 'midterm'; break;
            case 3: 
            case 4: $field = 'finals'; break; // map 3/4 to finals (DB has 3 terms)
            default: return false;
        }

        if ($existing) {
            $payload = [ $field => $grade, 'updated_at' => date('Y-m-d H:i:s') ];
            
            // Merge with existing data to calculate final grade
            $updatedGrade = array_merge($existing, $payload);
            $prelim = floatval($updatedGrade['prelim'] ?? 0);
            $midterm = floatval($updatedGrade['midterm'] ?? 0);
            $finals = floatval($updatedGrade['finals'] ?? 0);
            
            // Calculate final grade if all three periods have grades
            if ($prelim > 0 && $midterm > 0 && $finals > 0) {
                $payload['final_grade'] = round(($prelim + $midterm + $finals) / 3, 2);
            }
            
            // Update remarks
            $remarks = $this->determine_remarks($prelim, $midterm, $finals);
            if ($remarks) {
                $payload['remarks'] = $remarks;
            }
            
            return $this->db->table('grades')->where('id', $existing['id'])->update($payload);
        }

        // Insert new grade row
        $payload = [
            'student_id' => $student_id,
            'teacher_id' => $teacher_ref ?: 0,
            'subject_id' => $subject_id,
            'school_year' => date('Y') . '-' . (date('Y') + 1),
            'semester' => '1st',
            'status' => 'Draft', // Explicitly set status to Draft
            $field => $grade,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Set initial remarks based on the entered grade
        $remarks = $this->determine_remarks(
            $field === 'prelim' ? $grade : 0, 
            $field === 'midterm' ? $grade : 0, 
            $field === 'finals' ? $grade : 0
        );
        if ($remarks) {
            $payload['remarks'] = $remarks;
        }

        return $this->db->table('grades')->insert($payload);
    }
    
    /**
     * Determine remarks based on final grade
     * >= 75: Passed
     * < 75: Failed (if all grades entered) or Incomplete (if some grades missing)
     */
    private function determine_remarks($prelim, $midterm, $finals)
    {
        $prelim = floatval($prelim);
        $midterm = floatval($midterm);
        $finals = floatval($finals);
        
        // Check if all grades are entered
        $allEntered = ($prelim > 0 || $prelim === 0.00) && 
                      ($midterm > 0 || $midterm === 0.00) && 
                      ($finals > 0 || $finals === 0.00);
        
        // Calculate final grade (average of 3 terms)
        $finalGrade = ($prelim + $midterm + $finals) / 3;
        
        if ($finalGrade >= 75) {
            return 'Passed';
        } elseif ($allEntered && $prelim > 0 && $midterm > 0 && $finals > 0) {
            // All grades entered and below 75
            return 'Failed';
        } else {
            // Some grades missing
            return 'Incomplete';
        }
    }

    /**
     * Log grade update into audit_logs
     */
    public function log_grade_update($auth_user_id, $student_id, $subject_id, $period, $grade)
    {
        // Check if this is a submission log (grade parameter contains text)
        if (is_string($grade) && (strpos($grade, 'Submitted') !== false || strpos($grade, 'Approved') !== false || strpos($grade, 'Rejected') !== false)) {
            $action = $grade; // Use the text directly (e.g., "Submitted grades for review for section 3")
        } else {
            // Regular grade update
            $periods = [1 => 'Prelim', 2 => 'Midterm', 3 => 'Finals'];
            $period_name = isset($periods[$period]) ? $periods[$period] : 'Period ' . $period;
            $action = "Updated {$period_name} grade for student ID {$student_id} in subject ID {$subject_id}: {$grade}";
        }
        
        $payload = [
            'user_id' => $auth_user_id,
            'action' => $action,
            'table_name' => 'grades',
            'record_id' => null,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('audit_logs')->insert($payload);
    }

    /**
     * Create a notification for a recipient (recipient_id is auth.id)
     * Non-destructive: simply inserts a row into notifications table.
     */
    public function create_notification($recipient_auth_id, $sender_auth_id, $message)
    {
        if (empty($recipient_auth_id) || empty($message)) return false;

        $payload = [
            'recipient_id' => $recipient_auth_id,
            'sender_id' => $sender_auth_id ?: 0,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('notifications')->insert($payload);
    }
    
    /**
     * Get student grade record for a specific student and subject
     */
    public function get_student_grade($student_id, $subject_id)
    {
        return $this->db->table('grades')
            ->where('student_id', $student_id)
            ->where('subject_id', $subject_id)
            ->get();
    }

    /**
     * Calculate final grade for a student in a subject (returns numeric or null)
     * Also updates remarks based on the final grade
     */
    public function calculate_final_grade($student_id, $subject_id)
    {
        // Get the grade row
        $row = $this->db->table('grades')->where('student_id', $student_id)->where('subject_id', $subject_id)->get();
        if (!$row) return null;

        // Calculate final grade based on current grades
        $prelim = isset($row['prelim']) ? (float)$row['prelim'] : 0;
        $mid = isset($row['midterm']) ? (float)$row['midterm'] : 0;
        $finals = isset($row['finals']) ? (float)$row['finals'] : 0;
        
        // Calculate final grade if all three periods have grades
        $finalGrade = null;
        if ($prelim > 0 && $mid > 0 && $finals > 0) {
            $finalGrade = round(($prelim + $mid + $finals) / 3, 2);
        }
        
        // Update remarks and final_grade in database
        $remarks = $this->determine_remarks($prelim, $mid, $finals);
        $updatePayload = [];
        if ($remarks) {
            $updatePayload['remarks'] = $remarks;
        }
        if ($finalGrade !== null) {
            $updatePayload['final_grade'] = $finalGrade;
        }
        
        if (!empty($updatePayload) && isset($row['id'])) {
            $this->db->table('grades')->where('id', $row['id'])->update($updatePayload);
        }

        return $finalGrade;
    }
    
    /**
     * Submit grades for a subject (change status from Draft to Submitted)
     */
    public function submit_grades_for_subject($subject_id, $teacher_id, $section_id = null)
    {
        // Build query to get Draft grades for this subject and teacher
        $query = $this->db->table('grades')
            ->where('subject_id', $subject_id)
            ->where('teacher_id', $teacher_id)
            ->where('status', 'Draft');
        
        // Apply section filter if provided
        if (!empty($section_id) && $section_id !== 'all') {
            // Join with students table to filter by section
            $draft_grades = $this->db->raw(
                "SELECT g.* FROM grades g 
                 INNER JOIN students s ON g.student_id = s.id 
                 WHERE g.subject_id = ? AND g.teacher_id = ? AND g.status = 'Draft' AND s.section_id = ?",
                [$subject_id, $teacher_id, intval($section_id)]
            );
            
            if ($draft_grades) {
                $draft_grades = $draft_grades->fetchAll();
            }
        } else {
            $draft_grades = $query->get_all();
        }
        
        if (empty($draft_grades)) {
            return 0; // No draft grades to submit
        }
        
        // Get IDs of grades to update
        $grade_ids = array_column($draft_grades, 'id');
        
        // Update all draft grades to Submitted (pending admin review)
        if (!empty($grade_ids)) {
            // Use raw SQL with IN clause since where_in doesn't exist
            $ids_string = implode(',', array_map('intval', $grade_ids));
            $updated_at = date('Y-m-d H:i:s');
            
            $sql = "UPDATE grades SET status = 'Submitted', updated_at = ? WHERE id IN ({$ids_string})";
            $result = $this->db->raw($sql, [$updated_at]);
            
            return count($grade_ids); // Return count of grades updated
        }
        
        return 0;
    }
    
    /**
     * Get all pending grade submissions for admin review (status = Submitted)
     */
    public function get_pending_grade_submissions()
    {
        // Use raw SQL to properly handle LEFT JOIN with section filtering
        $sql = "SELECT g.id as grade_id, g.student_id, g.subject_id, g.teacher_id, 
                g.prelim, g.midterm, g.finals, g.final_grade, g.remarks, g.status, 
                g.school_year, g.semester, g.updated_at, 
                s.first_name as student_first_name, s.last_name as student_last_name, 
                s.section_id, sec.section_name, sec.grade_level, 
                sub.subject_name, sub.subject_code, 
                t.first_name as teacher_first_name, t.last_name as teacher_last_name
                FROM grades g
                INNER JOIN students s ON g.student_id = s.id
                INNER JOIN subjects sub ON g.subject_id = sub.id
                INNER JOIN teachers t ON g.teacher_id = t.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                WHERE g.status = ?
                ORDER BY sec.grade_level ASC, sec.section_name ASC, sub.subject_name ASC, s.last_name ASC";
        
        $result = $this->db->raw($sql, ['Submitted']);
        
        return $result ? $result->fetchAll() : [];
    }
    
    /**
     * Approve a grade submission (change status to Reviewed/Submitted)
     */
    public function approve_grade($grade_id)
    {
        // First verify the grade exists and is in Submitted status
        $grade = $this->db->table('grades')->where('id', $grade_id)->get();
        
        if (!$grade) {
            return false; // Grade not found
        }
        
        if ($grade['status'] !== 'Submitted') {
            return false; // Grade is not in Submitted status
        }
        
        return $this->db->table('grades')
            ->where('id', $grade_id)
            ->update([
                'status' => 'Reviewed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
    
    /**
     * Reject a grade submission (return to Draft status)
     */
    public function reject_grade($grade_id, $reason = null)
    {
        // First verify the grade exists and is in Submitted status
        $grade = $this->db->table('grades')->where('id', $grade_id)->get();
        
        if (!$grade) {
            return false; // Grade not found
        }
        
        if ($grade['status'] !== 'Submitted') {
            return false; // Grade is not in Submitted status
        }
        
        $payload = [
            'status' => 'Draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Log rejection reason if provided
        if ($reason) {
            $this->db->table('audit_logs')->insert([
                'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0,
                'action' => "Rejected grade: {$reason}",
                'table_name' => 'grades',
                'record_id' => $grade_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $this->db->table('grades')
            ->where('id', $grade_id)
            ->update($payload);
    }
    
    /**
     * Get grades that are visible to students (only Reviewed/Submitted status)
     */
    public function get_student_grades($student_id)
    {
        return $this->db->table('grades g')
            ->join('subjects sub', 'g.subject_id = sub.id')
            ->join('teachers t', 'g.teacher_id = t.id')
            ->select('g.id, g.prelim, g.midterm, g.finals, g.final_grade, g.remarks, g.school_year, g.semester, sub.subject_name, sub.subject_code, CONCAT(t.first_name, " ", t.last_name) as teacher_name')
            ->where('g.student_id', $student_id)
            ->where('g.status', 'Reviewed')
            ->order_by('g.school_year', 'DESC')
            ->order_by('g.semester', 'DESC')
            ->get_all() ?: [];
    }
}
