<?php
// Include database connection
require_once 'connection.php';

// Function to get all items from the database
function getAllItems() {
    global $conn;
    
    $sql = "SELECT * FROM barang ORDER BY nama_barang";
    $result = $conn->query($sql);
    
    $items = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

// Function to get an item by ID
function getItemById($id) {
    global $conn;
    
    $sql = "SELECT * FROM barang WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?> 