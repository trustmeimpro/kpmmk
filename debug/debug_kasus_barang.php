<?php
// Include database connection
require_once 'includes/database/connection.php';

// Check if kasus_barang table exists
$result = $conn->query("SHOW TABLES LIKE 'kasus_barang'");
if ($result->num_rows == 0) {
    echo "<h2>The kasus_barang table does not exist.</h2>";
    
    // Create the kasus_barang table
    $create_table_sql = "CREATE TABLE kasus_barang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kasus_id INT NOT NULL,
        barang_id INT NOT NULL,
        FOREIGN KEY (kasus_id) REFERENCES kasus(id),
        FOREIGN KEY (barang_id) REFERENCES barang(id)
    )";
    
    if ($conn->query($create_table_sql)) {
        echo "<p>Successfully created kasus_barang table.</p>";
    } else {
        echo "<p>Failed to create kasus_barang table: " . $conn->error . "</p>";
    }
} else {
    echo "<h2>The kasus_barang table exists.</h2>";
    
    // Check if there are any records in the table
    $result = $conn->query("SELECT * FROM kasus_barang");
    if ($result->num_rows > 0) {
        echo "<p>There are " . $result->num_rows . " records in the kasus_barang table.</p>";
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Kasus ID</th><th>Barang ID</th><th>Barang Name</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            // Get barang name
            $barang_query = "SELECT nama_barang FROM barang WHERE id = " . $row['barang_id'];
            $barang_result = $conn->query($barang_query);
            $barang_name = ($barang_result && $barang_result->num_rows > 0) ? 
                           $barang_result->fetch_assoc()['nama_barang'] : 'Unknown';
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['kasus_id'] . "</td>";
            echo "<td>" . $row['barang_id'] . "</td>";
            echo "<td>" . $barang_name . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>There are no records in the kasus_barang table.</p>";
    }
}

// Now let's check the kasus table
echo "<h2>Kasus Table Records</h2>";
$result = $conn->query("SELECT * FROM kasus");
if ($result->num_rows > 0) {
    echo "<p>There are " . $result->num_rows . " records in the kasus table.</p>";
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>NIP</th><th>Tanggal</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Status ACC</th><th>Kondisi</th><th>Kasus Barang</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nip'] . "</td>";
        echo "<td>" . $row['tanggal'] . "</td>";
        echo "<td>" . $row['waktu_mulai'] . "</td>";
        echo "<td>" . $row['waktu_selesai'] . "</td>";
        echo "<td>" . $row['status_acc'] . "</td>";
        echo "<td>" . $row['kondisi'] . "</td>";
        echo "<td>" . $row['kasus_barang'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>There are no records in the kasus table.</p>";
}
?>
