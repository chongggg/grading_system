<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

// Authentication routes
$router->match('auth/login', 'Auth::login', ['GET','POST']);
$router->match('auth/register', 'Auth::register', ['GET','POST']);
$router->get('auth/logout', 'Auth::logout');
$router->match('auth/profile', 'Auth::profile', ['GET','POST']);
$router->post('auth/upload_image', 'Auth::upload_image');
$router->post('auth/change_password', 'Auth::change_password');

// 2FA routes
$router->match('auth/verify_2fa', 'Auth::verify_2fa', ['GET','POST']);
$router->post('auth/resend_2fa', 'Auth::resend_2fa');

// Main routes
$router->get('/', 'Landing::index');

// Landing page routes (public)
$router->get('landing', 'Landing::index');
$router->get('landing/waiting', 'Landing::waiting');

$router->get('students', 'Students::index');
$router->match('students/create', 'Students::create', ['GET','POST']);
$router->match('students/edit/{id}', 'Students::edit', ['GET','POST']);
$router->get('students/delete/{id}', 'Students::delete');
$router->get('students/deleted', 'Students::deleted');
$router->get('students/restore/{id}', 'Students::restore');
$router->get('students/permanent_delete/{id}', 'Students::permanent_delete');
$router->get('students/index/{page}', 'Students::index');

// Admin base routes
$router->get('admin', 'Auth::admin_dashboard');
$router->get('admin/subjects', 'Auth::admin_subjects');

// Admin student management
$router->get('admin/students', 'Admin::students');
$router->match('admin/add_student', 'Admin::add_student', ['GET','POST']);
$router->match('admin/edit_student/{id}', 'Admin::edit_student', ['GET','POST']);
$router->get('admin/delete_student/{id}', 'Admin::delete_student');
$router->post('admin/assign_subject', 'Admin::assign_subject');
$router->post('admin/remove_subject', 'Admin::remove_subject');
$router->get('admin/get_available_subjects/{student_id}', 'Admin::get_available_subjects');

// Admin teacher management
$router->get('admin/teachers', 'Admin::teachers');
$router->match('admin/add_teacher', 'Admin::add_teacher', ['GET','POST']);
$router->match('admin/edit_teacher/{id}', 'Admin::edit_teacher', ['GET','POST']);
$router->get('admin/delete_teacher/{id}', 'Admin::delete_teacher');
// Sync legacy auth teacher into teachers table
$router->get('admin/sync_teacher/{id}', 'Admin::sync_teacher');

// expose alias without admin/ for some links
$router->get('teachers', 'Admin::teachers');

// Admin subject management routes (handled by Auth controller)
$router->match('admin/add_subject', 'Auth::create_subject', ['GET','POST']);
$router->match('admin/edit_subject/{id}', 'Auth::update_subject', ['GET','POST']);
$router->get('admin/delete_subject/{id}', 'Auth::delete_subject');
$router->match('admin/assign_teacher/{subject_id}', 'Auth::assign_teacher', ['GET','POST']);

// Teacher routes
$router->get('teacher', 'Auth::teacher_dashboard');
$router->get('teacher/dashboard', 'Auth::teacher_dashboard');
$router->get('teacher/subjects', 'Auth::teacher_subjects');
$router->get('teacher/subjects/{subject_id}/students', 'Auth::teacher_class_list');
$router->get('teacher/class_list/{subject_id}', 'Auth::teacher_class_list'); // Alias for backward compatibility
$router->get('teacher/subjects/{subject_id}/grades', 'Auth::teacher_grades');
$router->post('teacher/update_grade', 'Auth::update_grade'); // match jQuery AJAX URL
$router->post('teacher/submit_grades', 'Auth::submit_grades'); // NEW: Submit grades for review

// Excel Import Routes
$router->get('teacher/import_grades/{id}', 'Auth::import_grades');
$router->get('teacher/download_template/{id}', 'Auth::download_template');
$router->post('teacher/upload_excel', 'Auth::upload_excel');
$router->post('teacher/execute_import', 'Auth::execute_import');

// Messaging Routes (Admin)
$router->get('admin/messages', 'Admin::messages');
$router->get('admin/view_thread/{id}', 'Admin::view_thread');
$router->post('admin/send_message', 'Admin::send_message');
$router->get('admin/delete_thread/{id}', 'Admin::delete_thread');

// Messaging Routes (Teacher)
$router->get('teacher/messages', 'Auth::messages');
$router->get('teacher/view_thread/{id}', 'Auth::view_thread');
$router->post('teacher/send_message', 'Auth::send_message');
$router->get('teacher/delete_thread/{id}', 'Auth::delete_thread');
$router->get('teacher/grades', 'Auth::teacher_subjects'); // alias to subjects view for nav link

// Admin grade review routes
$router->get('admin/review_grades', 'Admin::review_grades'); // NEW: Review pending grades
$router->post('admin/approve_grade', 'Admin::approve_grade'); // NEW: Approve grade
$router->post('admin/reject_grade', 'Admin::reject_grade'); // NEW: Reject grade

// Admin section management routes
$router->get('admin/sections', 'Admin::sections');
$router->match('admin/add_section', 'Admin::add_section', ['GET','POST']);
$router->match('admin/edit_section/{id}', 'Admin::edit_section', ['GET','POST']);
$router->get('admin/delete_section/{id}', 'Admin::delete_section');
$router->get('admin/view_section/{id}', 'Admin::view_section');
$router->post('admin/assign_student_to_section', 'Admin::assign_student_to_section');
$router->post('admin/remove_student_from_section', 'Admin::remove_student_from_section');
$router->post('admin/bulk_assign_section', 'Admin::bulk_assign_section');

// Admin subject bundles management
$router->get('admin/subject_bundles', 'Admin::subject_bundles');
$router->match('admin/add_bundle', 'Admin::add_bundle', ['GET','POST']);
$router->match('admin/edit_bundle/{id}', 'Admin::edit_bundle', ['GET','POST']);
$router->get('admin/delete_bundle/{id}', 'Admin::delete_bundle');
$router->get('admin/view_bundle/{id}', 'Admin::view_bundle');
$router->post('admin/add_subject_to_bundle', 'Admin::add_subject_to_bundle');
$router->post('admin/remove_subject_from_bundle', 'Admin::remove_subject_from_bundle');

// Reports Module routes
$router->get('reports', 'Reports::index');
$router->get('reports/index/{page}', 'Reports::index');
$router->get('reports/export_csv', 'Reports::export_csv');
$router->get('reports/export_pdf', 'Reports::export_pdf');

// Announcements Module routes
$router->get('announcements', 'Announcements::index');
$router->get('announcements/index/{page}', 'Announcements::index');
$router->match('announcements/create', 'Announcements::create', ['GET','POST']);
$router->match('announcements/edit/{id}', 'Announcements::edit', ['GET','POST']);
$router->get('announcements/delete/{id}', 'Announcements::delete');
$router->get('announcements/view/{id}', 'Announcements::view');

// Audit Logs Module routes
$router->get('auditlogs', 'AuditLogs::index');
$router->get('auditlogs/index/{page}', 'AuditLogs::index');
$router->post('auditlogs/clear_old_logs', 'AuditLogs::clear_old_logs');

// Admin audit logs with pagination
$router->get('admin/audit_logs', 'Admin::audit_logs');
$router->get('admin/audit_logs/{page}', 'Admin::audit_logs');

// Pending Accounts routes (admin only)
$router->get('pendingaccounts', 'PendingAccounts::index');
$router->get('pendingaccounts/approve/{id}', 'PendingAccounts::approve');
$router->get('pendingaccounts/reject/{id}', 'PendingAccounts::reject');

// Student Portal routes
$router->get('student', 'Student::index');
$router->get('student/dashboard', 'Student::dashboard');
$router->match('student/profile', 'Student::profile', ['GET','POST']);
$router->get('student/subjects', 'Student::subjects');
$router->get('student/grades', 'Student::grades');
$router->get('student/download_pdf', 'Student::download_pdf');
$router->get('student/notifications', 'Student::notifications');
$router->post('student/mark_notification_read/{id}', 'Student::mark_notification_read');
$router->post('student/mark_all_read', 'Student::mark_all_read');
$router->post('student/chatbot', 'Student::chatbot');

// Messaging Routes (Student)
$router->get('student/messages', 'Student::messages');
$router->get('student/view_thread/{id}', 'Student::view_thread');
$router->post('student/send_message', 'Student::send_message');
$router->get('student/delete_thread/{id}', 'Student::delete_thread');

// Migration routes (only available in development)
$router->get('migrate', 'Migrate::index');
$router->get('migrate/rollback', 'Migrate::rollback');

