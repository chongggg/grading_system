<?php
// Database connection
$host = 'localhost';
$dbname = 'grading_ms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL for creating student_subjects table
    $sql = "CREATE TABLE IF NOT EXISTS `student_subjects` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    // Execute the query
    $pdo->exec($sql);
    echo "Table student_subjects created successfully!";

} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}