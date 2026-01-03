<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/crud_functions.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

class Auth {
    public static function start() {
        if(session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function login($email, $password) {
        $user = User::getByEmail($email);
        if (!$user) return ['success' => false, 'error' => 'Invalid credentials'];
        if (password_verify($password, $user['password_hash'])) {
            self::start();
            $_SESSION['userid'] = $user['userid'];
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    public static function logout() {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function requireLogin() {
        self::start();
        if (empty($_SESSION['userid'])) {
            header('Location: booking_login.php');
            exit;
        }
    }

    public static function currentUserId() {
        self::start();
        return $_SESSION['userid'] ?? null;
    }
}
