<?php
// Include database connection
require_once 'includes/database/connection.php';

// Check kasus table structure
$result = $conn->query("DESCRIBE kasus");
if ($result) {
    echo "<h2>Kasus Table Structure</h2>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error: " . $conn->error;
}

// Check if kasus_barang table exists
$result = $conn->query("SHOW TABLES LIKE 'kasus_barang'");
if ($result->num_rows > 0) {
    // Table exists, check its structure
    $result = $conn->query("DESCRIBE kasus_barang");
    if ($result) {
        echo "<h2>Kasus_Barang Table Structure</h2>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "<h2>kasus_barang table does not exist</h2>";
}
?>
