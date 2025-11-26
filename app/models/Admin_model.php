<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Admin_model extends Model
{
    /**
     * Get all teachers from both new teachers table and legacy system
     */
public function getAllTeachers() {
    $all_teachers = [];

    // Get teachers from the normalized teachers table only
    $teachers = $this->db->table('teachers t')
                       ->select('t.id, t.first_name, t.middle_name, t.last_name, 
                                t.email, t.specialization, t.contact_number, 
                                t.created_at, t.updated_at')
                       ->order_by('t.id', 'DESC')
                       ->get_all();

    if ($teachers) {
        foreach ($teachers as $teacher) {
            $teacher['teacher_id'] = $teacher['id'];
            $teacher['is_legacy'] = false;

            // Get subjects from class_assignments first
            $subjects = $this->db->table('class_assignments ca')
                               ->join('subjects s', 'ca.subject_id = s.id')
                               ->select('s.id, s.subject_name, s.subject_code')
                               ->where('ca.teacher_id', $teacher['id'])
                               ->order_by('s.subject_name', 'ASC')
                               ->get_all();

            // If no class_assignments, check subjects.teacher_id directly
            if (empty($subjects)) {
                $subjects = $this->db->table('subjects')
                                   ->select('id, subject_name, subject_code')
                                   ->where('teacher_id', $teacher['id'])
                                   ->order_by('subject_name', 'ASC')
                                   ->get_all();
            }

            $teacher['subjects'] = $subjects ?: [];
            $teacher['subject_count'] = count($subjects ?: []);

            $all_teachers[] = $teacher;
        }
    }

    return $all_teachers;
}
    /**
     * Get a single teacher by ID
     */
    public function getTeacher($id) {
        // Try to get from teachers table first
        $teacher = $this->db->table('teachers')->where('id', $id)->get();
        
        if ($teacher) {
            $teacher['is_legacy'] = false;
            return $teacher;
        }
        
        // If not found, try to get from legacy system
        $legacy_teacher = $this->db->table('students s')
                                   ->join('auth a', 's.id = a.student_id')
                                   ->select('s.*, a.id as auth_id')
                                   ->where('s.id', $id)
                                   ->where('a.role', 'teacher')
                                   ->where_null('s.deleted_at')
                                   ->get();
        
        if ($legacy_teacher) {
            $legacy_teacher['is_legacy'] = true;
        }
        
        return $legacy_teacher;
    }

    /**
     * Add a new teacher
     */
    public function addTeacher($data) {
        try {
            $this->db->transaction();
            
            // Insert into teachers table
            $teacher_data = [
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $teacher_id = $this->db->table('teachers')->insert($teacher_data);
            
            if (!$teacher_id) {
                throw new Exception("Failed to create teacher record");
            }
            
            // NOTE: Previously we created a corresponding student record so auth.student_id
            // pointed to that student. To avoid saving teachers into the students table
            // we create the auth account without creating a student row and set
            // student_id to NULL. This requires the `auth.student_id` column to be
            // nullable in the database. If your DB still has student_id NOT NULL,
            // this insert will fail and you'll need to run the ALTER TABLE command
            // provided in the README / notes below.

            // Create auth account (no student row)
            $auth_data = [
                'student_id' => null,
                // link auth to the newly created teacher via teacher_id so teacher functions can resolve
                'teacher_id' => $teacher_id,
                'username' => $data['username'] ?? strtolower( ($data['first_name'] . $data['last_name']) ?? '' ),
                'password' => password_hash($data['password'] ?? 'teacher123', PASSWORD_DEFAULT),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Try to create auth account without a student row first (preferred)
            $auth_id = false;
            try {
                $auth_id = $this->db->table('auth')->insert($auth_data);
            } catch (Exception $e) {
                // swallow for fallback below
                $auth_id = false;
            }

            if (!$auth_id) {
                // Before creating a students fallback row, check if the DB allows
                // auth.student_id to be NULL. If it does NOT allow NULL, we will NOT
                // auto-create a students row here because that was causing teachers
                // to be inserted into the students table implicitly. Instead we
                // raise an explicit error so you can choose how to migrate the
                // schema (preferred) or add a deliberate fallback.
                // Use the DB wrapper's raw() method to run small schema probes
                $dbNameStmt = $this->db->raw("SELECT DATABASE() AS dbname");
                $dbName = $dbNameStmt ? $dbNameStmt->fetch() : null;
                $schema = $dbName ? ($dbName['dbname'] ?? null) : null;

                $is_nullable = null;
                if ($schema) {
                    $colStmt = $this->db->raw(
                        "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'auth' AND COLUMN_NAME = 'student_id'",
                        [$schema]
                    );
                    $col = $colStmt ? $colStmt->fetch() : null;
                    if ($col && isset($col['IS_NULLABLE'])) {
                        $is_nullable = strtoupper($col['IS_NULLABLE']) === 'YES';
                    }
                }

                if ($is_nullable === false) {
                    // DB requires student_id NOT NULL — do NOT auto-create student record.
                    throw new Exception("Database requires auth.student_id NOT NULL. To avoid creating a students row for every teacher, run: ALTER TABLE auth MODIFY student_id INT UNSIGNED DEFAULT NULL;");
                }

                // If we either couldn't detect the schema or it is nullable, then
                // attempt the fallback creation (existing behavior). If detection
                // failed (is_nullable === null) we conservatively avoid creating
                // unexpected students unless you're OK with it; for now we'll
                // attempt to create the student only if the column is nullable.
                if ($is_nullable === true) {
                    // Check if a student with the same email already exists
                    $existing_student = null;
                    if (!empty($data['email'])) {
                        $existing_student = $this->db->table('students')
                                                      ->where('email', $data['email'])
                                                      ->get();
                    }

                    if ($existing_student) {
                        $student_id = $existing_student['id'];
                    } else {
                        $student_data = [
                            'first_name' => $data['first_name'] ?? null,
                            'middle_name' => $data['middle_name'] ?? null,
                            'last_name' => $data['last_name'] ?? null,
                            'email' => $data['email'] ?? null,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $student_id = $this->db->table('students')->insert($student_data);
                        if (!$student_id) {
                            throw new Exception("Failed to create student record for auth fallback");
                        }
                    }

                    // Attach student_id and try inserting auth again
                    $auth_data['student_id'] = $student_id;
                    $auth_id = $this->db->table('auth')->insert($auth_data);
                    if (!$auth_id) {
                        throw new Exception("Failed to create auth account after creating student");
                    }
                } else {
                    // If we couldn't detect nullability, fail safe to avoid creating student rows
                    throw new Exception("Unable to determine auth.student_id nullability; aborting to avoid creating a students record for a teacher.");
                }
            }
            
            $this->db->commit();
            return $teacher_id;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Add Teacher Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update teacher information
     */
    public function updateTeacher($id, $data) {
        try {
            $this->db->transaction();
            
            $teacher_data = [
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->table('teachers')
                              ->where('id', $id)
                              ->update($teacher_data);
            
            if ($result === false) {
                throw new Exception("Failed to update teacher record");
            }
            
            // Update corresponding student record if it exists
            $teacher = $this->db->table('teachers')->where('id', $id)->get();
            
            if ($teacher && $teacher['email']) {
                $student = $this->db->table('students')
                                   ->where('email', $teacher['email'])
                                   ->get();
                
                if ($student) {
                    $student_data = [
                        'first_name' => $data['first_name'],
                        'middle_name' => $data['middle_name'] ?? null,
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->db->table('students')
                            ->where('id', $student['id'])
                            ->update($student_data);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Update Teacher Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a teacher
     */
    public function deleteTeacher($id) {
        try {
            $this->db->transaction();
            
            // Get teacher email for cleanup
            $teacher = $this->db->table('teachers')->where('id', $id)->get();
            
            // Delete class assignments first
            $this->db->table('class_assignments')
                     ->where('teacher_id', $id)
                     ->delete();
            
            // Delete teacher record
            $result = $this->db->table('teachers')
                              ->where('id', $id)
                              ->delete();
            
            // Optional: Delete corresponding student and auth records
            if ($teacher && $teacher['email']) {
                $student = $this->db->table('students')
                                   ->where('email', $teacher['email'])
                                   ->get();
                
                if ($student) {
                    // This will cascade delete auth record due to FK constraint
                    $this->db->table('students')
                            ->where('id', $student['id'])
                            ->delete();
                }
            }
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Delete Teacher Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subjects assigned to a teacher
     */
public function get_subjects_by_teacher($teacher_id)
{
    if (!$teacher_id) return [];

    // First try class_assignments (proper way)
    $subjects = $this->db->table('class_assignments ca')
                       ->join('subjects s', 'ca.subject_id = s.id')
                       ->select('s.id, s.subject_name, s.subject_code, s.description,
                               ca.section, ca.school_year, ca.semester')
                       ->where('ca.teacher_id', $teacher_id)
                       ->order_by('s.subject_name', 'ASC')
                       ->get_all();

    // If no results, fall back to subjects.teacher_id (legacy/direct assignment)
    if (empty($subjects)) {
        $subjects = $this->db->table('subjects')
                           ->select('id, subject_name, subject_code, description,
                                   grade_level, semester, 
                                   NULL as section, NULL as school_year')
                           ->where('teacher_id', $teacher_id)
                           ->order_by('subject_name', 'ASC')
                           ->get_all();
    }

    return $subjects ?: [];
}



    // ======== STUDENTS ========
    public function getAllStudents() {
        // Only get students with auth role='student' (exclude pending users with role='user')
        $students = $this->db->table('students s')
                       ->join('auth a', 's.id = a.student_id')
                       ->select('s.*')
                       ->where('a.role', 'student')
                       ->where_null('s.deleted_at')
                       ->order_by('s.id', 'DESC')
                       ->get_all();
        
        return $students ?: [];
    }

    public function get_students($per_page = null, $page = null)
    {
        // Build base query - only get students with auth role='student' (exclude pending users with role='user')
        $query = $this->db->table('students s')
                         ->join('auth a', 's.id = a.student_id')
                         ->select('s.*')
                         ->where('a.role', 'student')
                         ->where_null('s.deleted_at')
                         ->order_by('s.id', 'DESC');
        
        // Get total count for pagination
        $total = $this->db->table('students s')
                         ->join('auth a', 's.id = a.student_id')
                         ->where('a.role', 'student')
                         ->where_null('s.deleted_at')
                         ->select('COUNT(DISTINCT s.id) as total')
                         ->get();
        $total = $total ? (int)$total['total'] : 0;

        // Apply pagination if parameters provided
        if ($per_page && $page) {
            $offset = ($page - 1) * $per_page;
            $query->limit($per_page, $offset);
        }

        // Fetch paginated students
        $students = $query->get_all() ?: [];
        
        if (empty($students)) {
            return [
                'data' => [],
                'total' => 0,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => 0
            ];
        }

        // Collect student IDs
        $student_ids = array_column($students, 'id');

        // No need to fetch auth roles again since we already filtered by role='student' in the JOIN
        // Just prepare the filtered array directly
        $filtered = [];
        foreach ($students as $st) {
            $sid = $st['id'];
            $filtered[$sid] = $st;
            $filtered[$sid]['subjects'] = [];
        }

        if (empty($filtered)) {
            return [
                'data' => [],
                'total' => 0,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => 0
            ];
        }

        // Batch fetch enrolled subjects for all returned students
        $enrollments = $this->db->table('student_subjects ss')
                               ->join('subjects s', 'ss.subject_id = s.id')
                               ->select('ss.student_id, s.id AS id, s.subject_name AS name, s.subject_code AS code')
                               ->in('ss.student_id', array_keys($filtered))
                               ->get_all() ?: [];

        // Attach subjects to each student
        foreach ($enrollments as $en) {
            $sid = $en['student_id'];
            if (isset($filtered[$sid])) {
                $filtered[$sid]['subjects'][] = [
                    'id' => $en['id'],
                    'name' => $en['name'],
                    'code' => $en['code']
                ];
            }
        }

        // Keep the descending order but return with pagination metadata
        usort($filtered, function($a, $b) { return $b['id'] <=> $a['id']; });
        $filtered = array_values($filtered);

        return [
            'data' => $filtered,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => $total ? ceil($total / $per_page) : 1
        ];
    }

    public function addStudent($data) {
        try {
            $this->db->transaction();

            // Insert into students table
            $student_data = [
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'address' => $data['address'] ?? null,
                'grade_level' => $data['grade_level'] ?? null,
                'section' => $data['section'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $student_id = $this->db->table('students')->insert($student_data);

            if (!$student_id) {
                throw new Exception("Failed to create student record");
            }

            // Create auth account
            $auth_data = [
                'username' => $data['username'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'student',
                'student_id' => $student_id,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $auth_id = $this->db->table('auth')->insert($auth_data);

            if (!$auth_id) {
                throw new Exception("Failed to create auth account");
            }

            $this->db->commit();
            return $student_id;
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Add Student Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStudent($id, $data) {
        try {
            $this->db->transaction();

            // Update student information
            $student_data = [
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'address' => $data['address'] ?? null,
                'grade_level' => $data['grade_level'] ?? null,
                'section' => $data['section'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $student_updated = $this->db->table('students')
                                      ->where('id', $id)
                                      ->update($student_data);

            if ($student_updated === false) {
                throw new Exception("Failed to update student record");
            }

            // Update auth account if provided
            if (isset($data['username'])) {
                if (!empty($data['password'])) {
                    $auth_data = [
                        'username' => $data['username'],
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $auth_data = [
                        'username' => $data['username'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                $this->db->table('auth')
                        ->where('student_id', $id)
                        ->update($auth_data);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Update Student Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteStudent($id) {
        // Soft delete by setting deleted_at
        return $this->db->table('students')
                       ->where('id', $id)
                       ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    // ======== SUBJECTS ========
    public function getAllSubjects($filters = []) {
        $query = $this->db->table('subjects s')
            ->select('s.*, s.subject_name, s.subject_code');
        if (!empty($filters['grade_level'])) {
            $query = $query->where('s.grade_level', $filters['grade_level']);
        }
        if (!empty($filters['semester'])) {
            $query = $query->where('s.semester', $filters['semester']);
        }
        return $query->order_by('s.id', 'DESC')->get_all();
    }

    public function get_available_subjects($student_id)
    {
        // Get subjects that the student is not already enrolled in
        $enrolled = $this->db->table('student_subjects')
                             ->select('subject_id')
                             ->where('student_id', $student_id)
                             ->get_all() ?: [];

        $enrolled_ids = [];
        foreach ($enrolled as $row) {
            if (isset($row['subject_id'])) {
                $enrolled_ids[] = $row['subject_id'];
            }
        }

        $query = $this->db->table('subjects')
                         ->select('id, subject_name as name, subject_code as code');

        if (!empty($enrolled_ids)) {
            $query = $query->not_in('id', $enrolled_ids);
        }

        return $query->order_by('subject_name', 'ASC')->get_all();
    }

    public function assign_subject($student_id, $subject_id)
    {
        // Check if already enrolled
        $existing = $this->db->table('student_subjects')
            ->where('student_id', $student_id)
            ->where('subject_id', $subject_id)
            ->get();

        if ($existing) {
            return ['success' => false, 'message' => 'Student is already enrolled in this subject'];
        }

        // Insert the enrollment
        $result = $this->db->table('student_subjects')->insert([
            'student_id' => $student_id,
            'subject_id' => $subject_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => (bool)$result,
            'message' => $result ? 'Subject assigned successfully' : 'Failed to assign subject'
        ];
    }

    public function remove_subject($student_id, $subject_id)
    {
        $result = $this->db->table('student_subjects')
            ->where('student_id', $student_id)
            ->where('subject_id', $subject_id)
            ->delete();

        return [
            'success' => (bool)$result,
            'message' => $result ? 'Subject removed successfully' : 'Failed to remove subject'
        ];
    }

    public function get_subjects()
    {
        return $this->db->table('subjects s')
                        ->select('s.*')
                        ->get_all();
    }


public function add_subject($data)
{
    try {
        $this->db->transaction();
        
        // Insert subject with teacher_id
        $subject_data = [
            'subject_code' => $data['subject_code'],
            'subject_name' => $data['subject_name'],
            'description' => $data['description'] ?? null,
            'grade_level' => $data['grade_level'] ?? null,
            'semester' => $data['semester'] ?? '1st',
            'teacher_id' => !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $subject_id = $this->db->table('subjects')->insert($subject_data);
        
        // Also create class assignment
        if (!empty($data['teacher_id'])) {
            $teacher_exists = $this->db->table('teachers')
                                     ->where('id', $data['teacher_id'])
                                     ->get();
            
            if ($teacher_exists) {
                $assignment = [
                    'teacher_id' => (int)$data['teacher_id'],
                    'subject_id' => (int)$subject_id,
                    'section' => $data['section'] ?? null,
                    'school_year' => $data['school_year'] ?? date('Y'),
                    'semester' => $data['semester'] ?? '1st',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->table('class_assignments')->insert($assignment);
            }
        }
        
        $this->db->commit();
        return $subject_id;
    } catch (Exception $e) {
        $this->db->roll_back();
        error_log("Add Subject Error: " . $e->getMessage());
        return false;
    }
}

public function updateSubject($id, $data)
{
    try {
        $this->db->transaction();
        
        // Update subject info INCLUDING teacher_id in subjects table
        $subject_data = [
            'subject_code' => $data['subject_code'],
            'subject_name' => $data['subject_name'],
            'description' => $data['description'] ?? null,
            'grade_level' => $data['grade_level'] ?? null,
            'semester' => $data['semester'] ?? '1st',
            'teacher_id' => !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->table('subjects')
                          ->where('id', $id)
                          ->update($subject_data);
        
        if ($result === false) {
            throw new Exception('Failed to update subject');
        }
        
        // ALSO update class_assignments for proper structure
        // Remove existing assignments
        $this->db->table('class_assignments')
                 ->where('subject_id', $id)
                 ->delete();
        
        // If a teacher is selected, create a new assignment
        if (!empty($data['teacher_id'])) {
            $teacher_exists = $this->db->table('teachers')
                                     ->where('id', $data['teacher_id'])
                                     ->get();
            
            if ($teacher_exists) {
                $assignment = [
                    'teacher_id' => (int)$data['teacher_id'],
                    'subject_id' => (int)$id,
                    'section' => $data['section'] ?? null,
                    'school_year' => $data['school_year'] ?? date('Y'),
                    'semester' => $data['semester'] ?? '1st',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->table('class_assignments')->insert($assignment);
            }
        }
        
        $this->db->commit();
        return true;
        
    } catch (Exception $e) {
        $this->db->roll_back();
        error_log("Update Subject Error: " . $e->getMessage());
        return false;
    }
}

    public function deleteSubject($id)
    {
        try {
            $this->db->transaction();
            
            // Delete class assignments first
            $this->db->table('class_assignments')
                     ->where('subject_id', $id)
                     ->delete();
            
            // Then delete the subject
            $this->db->table('subjects')
                     ->where('id', $id)
                     ->delete();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->roll_back();
            error_log("Delete Subject Error: " . $e->getMessage());
            return false;
        }
    }

    /** Class assignments */
    public function get_class_assignments_by_teacher($teacher_id, $subject_id = null) 
    {
        $query = $this->db->table('class_assignments ca')
                         ->join('subjects s', 'ca.subject_id = s.id')
                         ->select('ca.*, s.subject_name, s.subject_code')
                         ->where('ca.teacher_id', $teacher_id);

        if ($subject_id) {
            $query->where('ca.subject_id', $subject_id);
        }

        return $query->order_by('ca.school_year DESC, ca.semester DESC')
                    ->get_all() ?: [];
    }

    /** Reports */
    public function get_reports()
    {
        return $this->db->table('grades g')
                        ->join('students s', 'g.student_id = s.id')
                        ->join('subjects sub', 'g.subject_id = sub.id')
                        ->select('s.first_name, s.last_name, sub.subject_name, 
                                 g.prelim, g.midterm, g.finals, g.final_grade, 
                                 g.remarks, g.semester, g.school_year')
                        ->where_null('s.deleted_at')
                        ->order_by('s.last_name', 'ASC')
                        ->get_all();
    }

    /** Announcements */
    public function get_announcements()
    {
        return $this->db->table('announcements a')
                       ->join('auth u', 'a.author_id = u.id')
                       ->join('students s', 'u.student_id = s.id')
                       ->select('a.*, s.first_name, s.last_name')
                       ->order_by('a.created_at', 'DESC')
                       ->get_all();
    }

    public function add_announcement($data)
    {
        $announcement_data = [
            'author_id' => $data['author_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'role_target' => $data['role_target'] ?? 'all',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('announcements')->insert($announcement_data);
    }

    /** Audit Logs */
    public function get_audit_logs($limit = null, $offset = 0)
    {
        $sql = "SELECT al.*, s.first_name, s.last_name, a.role, a.username 
                FROM audit_logs al
                LEFT JOIN auth a ON al.user_id = a.id
                LEFT JOIN students s ON a.student_id = s.id
                ORDER BY al.timestamp DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $result = $this->db->raw($sql);
        return $result ? $result->fetchAll() : [];
    }
    
    public function get_audit_logs_count()
    {
        $result = $this->db->table('audit_logs')->select('COUNT(*) as total')->get();
        return $result ? intval($result['total']) : 0;
    }
    
    public function log_action($user_id, $action, $table_name, $record_id = null)
    {
        $log_data = [
            'user_id' => $user_id,
            'action' => $action,
            'table_name' => $table_name,
            'record_id' => $record_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->table('audit_logs')->insert($log_data);
    }
}