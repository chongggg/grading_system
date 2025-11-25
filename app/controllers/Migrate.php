<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migrate extends Controller {
    
    public function __construct() {
        parent::__construct();
        // Only allow in development
        if (ENVIRONMENT !== 'development') {
            exit('Migrations are only available in development environment');
        }
    }
    
    public function index() {
        require_once(APP_DIR . 'migrations/002_create_student_subjects.php');
        
        $migration = new Migration_create_student_subjects();
        
        try {
            $migration->up();
            echo "Migration successful: student_subjects table created.";
        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage();
        }
    }
    
    public function rollback() {
        require_once(APP_DIR . 'migrations/002_create_student_subjects.php');
        
        $migration = new Migration_create_student_subjects();
        
        try {
            $migration->down();
            echo "Rollback successful: student_subjects table dropped.";
        } catch (Exception $e) {
            echo "Rollback failed: " . $e->getMessage();
        }
    }
}