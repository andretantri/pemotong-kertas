<?php
/**
 * Authentication & Session Management
 */

// Require database config first for constants
require_once __DIR__ . '/database.php';

// Set session name BEFORE session_start()
if (defined('SESSION_NAME') && session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
}

// Set session lifetime BEFORE session_start()
if (defined('SESSION_LIFETIME') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login - redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Get base path dynamically
        $basePath = dirname($_SERVER['PHP_SELF']);
        if ($basePath === '/') {
            $basePath = '';
        }
        header('Location: ' . $basePath . '/login.php');
        exit();
    }
}

/**
 * Login user
 */
function loginUser($userId, $username) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

