<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_populate_student_subjects
{
    private $db;

    public function __construct()
    {
        // Use the project's Database wrapper
        $this->db = new Database;
    }

    public function up()
    {
        // This migration will create student_subjects rows for subjects that have a grade_level
        // by enrolling all non-deleted students with matching grade_level.
        // It is conservative: it only inserts when a matching student_subjects row does not already exist.

        $now = date('Y-m-d H:i:s');
        try {
            $subjects = $this->db->table('subjects')->select('id, grade_level')->get_all() ?: [];

            foreach ($subjects as $s) {
                if (empty($s['grade_level'])) continue;

                // find students in this grade_level
                $students = $this->db->table('students')
                    ->select('id')
                    ->where('grade_level', $s['grade_level'])
                    ->where_null('deleted_at')
                    ->get_all() ?: [];

                foreach ($students as $st) {
                    // skip if enrollment already exists
                    $exists = $this->db->table('student_subjects')
                        ->where('student_id', $st['id'])
                        ->where('subject_id', $s['id'])
                        ->get();

                    if ($exists) continue;

                    $payload = [
                        'student_id' => $st['id'],
                        'subject_id' => $s['id'],
                        'created_at' => $now,
                        'updated_at' => null
                    ];

                    $this->db->table('student_subjects')->insert($payload);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log('Migration populate_student_subjects failed: ' . $e->getMessage());
            return false;
        }
    }

    public function down()
    {
        // No-op: this migration is non-destructive and intended as a one-off population step.
        // If you want to roll back, manually delete rows you don't want.
        return true;
    }
}
