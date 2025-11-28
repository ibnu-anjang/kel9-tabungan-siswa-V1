<?php

// Enhanced Connection Backend by Ibnu A. A. M
// Security-focused database connection with proper error handling

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_team9_tabungan');

// Security settings
ini_set('display_errors', 0); // Hide errors in production
error_reporting(E_ALL); // Log all errors

// Create connection with error handling
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    // Log error instead of displaying
    error_log("Database Connection Failed: " . mysqli_connect_error());

    // User-friendly error message
    die("Maaf, terjadi masalah koneksi ke database. Silakan hubungi administrator.");
}

// Set charset untuk security
mysqli_set_charset($conn, "utf8mb4");

// Helper function untuk secure query
function executeQuery($conn, $query, $params = [], $types = "") {
    try {
        if (empty($params)) {
            // Simple query untuk SELECT tanpa parameter
            $result = mysqli_query($conn, $query);
            if (!$result) {
                throw new Exception("Query failed: " . mysqli_error($conn));
            }
            return $result;
        } else {
            // Prepared statement untuk security
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }

            if (!empty($types) && !empty($params)) {
                // Debug: Log parameter binding
                error_log("Binding parameters: " . $types . " - " . print_r($params, true));

                if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
                    throw new Exception("Bind failed: " . mysqli_stmt_error($stmt));
                }
            }

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }

            return $stmt;
        }
    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        throw $e; // Re-throw untuk handling di level atas
    }
}

// Start session untuk flash messages dengan header check
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Flash message helper functions
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function hasFlashMessage($type) {
    return isset($_SESSION['flash'][$type]);
}

?>