<?php

class Migration_add_student_subjects_table
{
    private $dbforge;
    private $db;

    public function __construct()
    {
        $this->dbforge = load_class('DBForge', 'database');
        $this->db = load_class('Database', 'database');
    }

    public function up()
    {
        // Create student_subjects table
        $fields = array(
            'id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'student_id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE
            ),
            'subject_id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            )
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('student_id');
        $this->dbforge->add_key('subject_id');
        $this->dbforge->create_table('student_subjects', TRUE);

        // Add foreign key constraints
    $this->db->raw('ALTER TABLE student_subjects 
             ADD CONSTRAINT fk_student_subjects_student 
             FOREIGN KEY (student_id) 
             REFERENCES students(id) 
             ON DELETE CASCADE');

    $this->db->raw('ALTER TABLE student_subjects 
             ADD CONSTRAINT fk_student_subjects_subject 
             FOREIGN KEY (subject_id) 
             REFERENCES subjects(id) 
             ON DELETE CASCADE');

    // Add unique constraint to prevent duplicate enrollments
    $this->db->raw('ALTER TABLE student_subjects 
             ADD CONSTRAINT uq_student_subject 
             UNIQUE (student_id, subject_id)');
    }

    public function down()
    {
        // Drop foreign key constraints first
    $this->db->raw('ALTER TABLE student_subjects DROP FOREIGN KEY fk_student_subjects_student');
    $this->db->raw('ALTER TABLE student_subjects DROP FOREIGN KEY fk_student_subjects_subject');

        // Drop the table
        $this->dbforge->drop_table('student_subjects', TRUE);
    }
}