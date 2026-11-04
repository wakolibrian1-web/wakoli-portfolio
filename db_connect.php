<?php
/**
 * db_connect.php
 * Database connection configuration for XAMPP (MySQL via MySQLi)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP default username
define('DB_PASS', '');            // XAMPP default password (empty)
define('DB_NAME', 'wakoli_portfolio');

/**
 * Creates and returns a MySQLi connection.
 * Terminates with an error message if the connection fails.
 */
function get_db_connection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // Log the real error server-side, show a safe message to the user
        error_log('Database connection failed: ' . $conn->connect_error);
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'A server error occurred. Please try again later.'
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
