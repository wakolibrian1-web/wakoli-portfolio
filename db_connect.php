<?php
/**
 * db_connect.php
 * Database connection configuration for WAMP/XAMPP (MySQL via MySQLi).
 * Throws a RuntimeException on failure so callers can catch it gracefully.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // WAMP/XAMPP default username
define('DB_PASS', '');       // WAMP/XAMPP default password (empty)
define('DB_NAME', 'wakoli_portfolio');

/**
 * Creates and returns a MySQLi connection.
 * Throws RuntimeException if the connection fails — callers can catch this
 * and fall back to defaults instead of dying with a raw error.
 *
 * @throws RuntimeException
 */
function get_db_connection(): mysqli {
    // Suppress the default PHP warning so we control the error output
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // Log the real error server-side only — never expose it to the browser
        error_log('Database connection failed: ' . $conn->connect_error);
        throw new RuntimeException('Database connection failed.');
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
