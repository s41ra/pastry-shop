<?php
/**
 * Database Configuration with Auto-Setup
 * Automatically creates the database and tables if they don't exist
 */

// Database configuration
if (!defined('DB_HOST'))
    define('DB_HOST', 'localhost');
if (!defined('DB_USER'))
    define('DB_USER', 'root');
if (!defined('DB_PASS'))
    define('DB_PASS', '');
if (!defined('DB_NAME'))
    define('DB_NAME', 'pastry_shop');

// 1. Try to connect to MySQL
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$conn) {
    die("Database Connection Error: Please ensure your MySQL server is running. Error: " . mysqli_connect_error());
}

// 2. Check if database exists and has required tables
$db_selected = @mysqli_select_db($conn, DB_NAME);
$needs_setup = false;

if (!$db_selected) {
    $needs_setup = true;
} else {
    // Check if a core table exists (e.g., 'pastries')
    $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'pastries'");
    if (!$check_table || mysqli_num_rows($check_table) == 0) {
        $needs_setup = true;
    }
}

// 3. Run setup if needed
if ($needs_setup) {
    // Close initial connection to avoid conflicts during setup
    mysqli_close($conn);

    // Include setup script
    $setup_file = __DIR__ . '/../database/setup.php';
    if (file_exists($setup_file)) {
        $setup_status = include($setup_file);

        if ($setup_status !== true) {
            die("Error: Failed to automatically set up the database. Detail: " . ($setup_status === false ? "Unknown error" : $setup_status));
        }
    } else {
        die("Error: Database setup file not found at " . $setup_file);
    }

    // Reconnect after successful setup
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("Connection failed after setup: " . mysqli_connect_error());
    }
}

// Final charset setting
mysqli_set_charset($conn, "utf8mb4");
?>