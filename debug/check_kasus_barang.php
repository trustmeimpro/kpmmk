<?php
// Include database connection
require_once 'includes/database/connection.php';

// Check table structure
echo "<h2>Table Structure for kasus_barang</h2>";
$result = $conn->query("DESCRIBE kasus_barang");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . ($value ?? "NULL") . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check sample data
echo "<h2>Sample Data from kasus_barang</h2>";
$result = $conn->query("SELECT * FROM kasus_barang LIMIT 5");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        // Get field names
        $fields = $result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Get data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . ($value ?? "NULL") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data found in the table.";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
