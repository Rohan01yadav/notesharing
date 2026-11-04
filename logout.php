<?php
/**
 * User Logout Action
 * Student Notes Sharing Platform
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();

// Re-start a clean session just to show the flash message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('info', 'You have been logged out securely.');
redirect('/login.php');
