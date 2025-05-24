<?php
// Database connection parameters for root access (without specific database)
$host = "localhost";
$username = "root";
$password = "";

// Create connection without specifying database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS peminjamanBarangLab";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db("peminjamanBarangLab");

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100),
    jumlah INT
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Check if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM barang");
$row = $result->fetch_assoc();

// If table is empty, insert sample data
if ($row['count'] == 0) {
    $items = [
        ['Labu erlenmeyer gede', 21],
        ['Labu erlenmeyer kecil', 9],
        ['Gelas kimia kecil', 18],
        ['Gelas kimia sedang', 16],
        ['Gelas kimia besar', 11],
        ['Plate tetes', 6],
        ['Gelas ukur besar', 13],
        ['Gelas ukur sedang', 2],
        ['Gelas ukur kecil', 13],
        ['Tabung reaksi', 187],
        ['Rak tabung reaksi', 6],
        ['Penjepit tabung reaksi', 5],
        ['Pipet tetes', 80],
        ['Tabung spiritus', 6],
        ['Corong gelas kecil', 6],
        ['Corong gelas gede', 4],
        ['Cawan petri', 15],
        ['Kaki tiga', 3]
    ];
    
    // Prepare statement for inserting data
    $stmt = $conn->prepare("INSERT INTO barang (nama_barang, jumlah) VALUES (?, ?)");
    
    // Insert each item
    foreach ($items as $item) {
        $stmt->bind_param("si", $item[0], $item[1]);
        if (!$stmt->execute()) {
            die("Error inserting data: " . $stmt->error);
        }
    }
    
    echo "Database initialized with sample data.<br>";
} else {
    echo "Database already contains data.<br>";
}

// Close connection
$conn->close();

echo "Database setup complete. <a href='../../index.php'>Go to Dashboard</a>";
?> 