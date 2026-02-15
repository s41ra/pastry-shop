<?php
/**
 * Migration Script: Add delivery_address column to orders table
 */

require_once __DIR__ . '/../includes/config.php';

// Check if column already exists
$check_query = "SHOW COLUMNS FROM orders LIKE 'delivery_address'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) == 0) {
    // Column doesn't exist, add it
    $alter_query = "ALTER TABLE orders ADD COLUMN delivery_address TEXT AFTER customer_name";

    if (mysqli_query($conn, $alter_query)) {
        echo "SUCCESS: delivery_address column added to orders table.\n";
    } else {
        echo "ERROR: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "INFO: delivery_address column already exists.\n";
}

mysqli_close($conn);
?>