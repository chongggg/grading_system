<?php
// Prevent any output before headers
ob_start();

$host = "ql12.freesqldatabase.com";
$user = "ysql12808059";
$pass = "yYDYBj8QiH5";
$db   = "sql12808059";

// SendGrid API Key for email sending (get from https://sendgrid.com)
// Replace 'YOUR_SENDGRID_API_KEY_HERE' with your actual API key
$GLOBALS['sendgrid_api_key'] = getenv('SENDGRID_API_KEY') ?: 'YOUR_SENDGRID_API_KEY_HERE';