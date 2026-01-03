<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
class Database {
    private static $host = "localhost";
    private static $username = "adminuser";
    private static $password = "Monkey2868!!";
    private static $dbname = "booking_sys";

    public static function connect() {
        $conn = new mysqli(self::$host, self::$username, self::$password, self::$dbname);

        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}
?>