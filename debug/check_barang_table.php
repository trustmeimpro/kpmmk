<?php
// Include database connection
require_once 'includes/database/connection.php';

// Check barang table structure
$result = $conn->query("DESCRIBE barang");
if ($result) {
    echo "<h2>Barang Table Structure</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] === NULL ? 'NULL' : $row['Default']) . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Show sample data from barang table
$result = $conn->query("SELECT * FROM barang LIMIT 5");
if ($result) {
    echo "<h2>Sample Data from Barang Table</h2>";
    echo "<table border='1'>";
    echo "<tr>";
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}
?>
