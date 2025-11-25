<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_create_student_subjects {
    
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    public function up() {
        $this->db->raw("CREATE TABLE IF NOT EXISTS `student_subjects` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `student_id` int(11) NOT NULL,
            `subject_id` int(11) NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_enrollment` (`student_id`, `subject_id`),
            KEY `fk_student_subjects_student` (`student_id`),
            KEY `fk_student_subjects_subject` (`subject_id`),
            CONSTRAINT `fk_student_subjects_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_student_subjects_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
    
    public function down() {
        $this->db->raw("DROP TABLE IF EXISTS `student_subjects`;");
    }
}