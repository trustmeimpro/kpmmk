<?php
include 'includes_admin/auth_check.php';

// Include database connection
require_once '../includes/database/connection.php';

// Initialize variables for messages
$error_message = "";
$success_message = "";

// Process delete request
if (isset($_POST['delete_kasus'])) {
    $kasus_id = intval($_POST['kasus_id']);
    
    // Start a transaction to ensure data consistency
    $conn->begin_transaction();
    
    try {
        // First, check if qty column exists in kasus_barang
        $column_check = $conn->query("SHOW COLUMNS FROM kasus_barang LIKE 'qty'");
        
        // Restore stock for the items in this kasus
        if ($column_check->num_rows > 0) {
            // The qty column exists, use it
            $restore_stock_query = "UPDATE barang b 
                                   JOIN kasus_barang kb ON b.id = kb.barang_id 
                                   SET b.jumlah = b.jumlah + kb.qty 
                                   WHERE kb.kasus_id = $kasus_id";
        } else {
            // The qty column doesn't exist, assume 1 item per record
            $restore_stock_query = "UPDATE barang b 
                                   JOIN kasus_barang kb ON b.id = kb.barang_id 
                                   SET b.jumlah = b.jumlah + 1 
                                   WHERE kb.kasus_id = $kasus_id";
        }
        $conn->query($restore_stock_query);
        
        // Delete records from kasus_barang table for this kasus
        $conn->query("DELETE FROM kasus_barang WHERE kasus_id = $kasus_id");
        
        // Delete the kasus record
        $conn->query("DELETE FROM kasus WHERE id = $kasus_id");
        
        // Commit the transaction
        $conn->commit();
        
        $success_message = "Peminjaman #$kasus_id berhasil dihapus.";
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        $error_message = "Gagal menghapus peminjaman: " . $e->getMessage();
    }
}

// Process status update request
if (isset($_POST['update_status'])) {
    $kasus_id = intval($_POST['kasus_id']);
    $status_acc = $_POST['status_acc'];
    $kondisi = $_POST['kondisi'];
    
    // Start a transaction to ensure data consistency
    $conn->begin_transaction();
    
    try {
        // First, check the current status to avoid unnecessary updates
        $check_stmt = $conn->prepare("SELECT status_acc, kondisi FROM kasus WHERE id = ?");
        $check_stmt->bind_param("i", $kasus_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $current = $result->fetch_assoc();
        $check_stmt->close();
        
        // Update the status and condition using prepared statement
        $update_stmt = $conn->prepare("UPDATE kasus SET status_acc = ?, kondisi = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $status_acc, $kondisi, $kasus_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // If the condition is set to 'selesai' (completed), return the items to inventory
        if ($kondisi === 'selesai') {
            // Check if qty column exists in kasus_barang
            $column_check = $conn->query("SHOW COLUMNS FROM kasus_barang LIKE 'qty'");
            
            if ($column_check->num_rows > 0) {
                // The qty column exists, use it
                $restore_stock_query = "UPDATE barang b 
                                      JOIN kasus_barang kb ON b.id = kb.barang_id 
                                      SET b.jumlah = b.jumlah + kb.qty 
                                      WHERE kb.kasus_id = $kasus_id";
            } else {
                // The qty column doesn't exist, assume 1 item per record
                $restore_stock_query = "UPDATE barang b 
                                      JOIN kasus_barang kb ON b.id = kb.barang_id 
                                      SET b.jumlah = b.jumlah + 1 
                                      WHERE kb.kasus_id = $kasus_id";
            }
            $conn->query($restore_stock_query);
            
            // Check if returned column exists in kasus_barang table
            $returned_column_check = $conn->query("SHOW COLUMNS FROM kasus_barang LIKE 'returned'");
            
            if ($returned_column_check->num_rows > 0) {
                // The returned column exists, update it
                $conn->query("UPDATE kasus_barang SET returned = 1 WHERE kasus_id = $kasus_id");
            } else {
                // The returned column doesn't exist, we'll just update the stock without marking as returned
                // Optionally, we could add the column here if needed
                // $conn->query("ALTER TABLE kasus_barang ADD COLUMN returned TINYINT(1) DEFAULT 0");
                // $conn->query("UPDATE kasus_barang SET returned = 1 WHERE kasus_id = $kasus_id");
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        $success_message = "Status peminjaman #$kasus_id berhasil diperbarui.";
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        $error_message = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Get all kasus data
$query = "SELECT k.*, ng.nama_guru 
          FROM kasus k 
          LEFT JOIN nip_guru ng ON k.nip = ng.nip 
          ORDER BY k.tanggal DESC, k.waktu_mulai DESC";
$result = $conn->query($query);
$kasus_list = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get barang for this kasus
        $kasus_id = $row['id'];
        $barang_list = [];
        
        // Check if kasus_barang table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'kasus_barang'");
        if ($table_check->num_rows > 0) {
            // Table exists, proceed with query
            // First check if qty column exists
            $column_check = $conn->query("SHOW COLUMNS FROM kasus_barang LIKE 'qty'");
            
            if ($column_check->num_rows > 0) {
                // The qty column exists, include it in the query
                $barang_query = "SELECT b.nama_barang, kb.qty 
                                 FROM kasus_barang kb 
                                 JOIN barang b ON kb.barang_id = b.id 
                                 WHERE kb.kasus_id = $kasus_id";
            } else {
                // The qty column doesn't exist, use default query
                $barang_query = "SELECT b.nama_barang, 1 as qty 
                                 FROM kasus_barang kb 
                                 JOIN barang b ON kb.barang_id = b.id 
                                 WHERE kb.kasus_id = $kasus_id";
            }
            
            $barang_result = $conn->query($barang_query);
            
            if ($barang_result && $barang_result->num_rows > 0) {
                while ($barang_row = $barang_result->fetch_assoc()) {
                    // Add quantity to the item name
                    $qty = intval($barang_row['qty']);
                    $barang_list[] = $barang_row['nama_barang'] . ' x' . $qty;
                }
            }
        }
        
        $row['barang'] = $barang_list;
        $kasus_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Admin</title>
    <link rel="stylesheet" href="css/dashboard_admin.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="css/history_admin.css">
</head>
<body>
    <?php include 'includes_admin/navbar_admin.php'; ?>
    
    <div class="history-container">
        <h1 class="history-title">Riwayat Peminjaman</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($kasus_list)): ?>
            <div class="no-kasus">Belum ada riwayat peminjaman.</div>
        <?php else: ?>
            <div class="kasus-grid">
                <?php foreach ($kasus_list as $kasus): ?>
                    <div class="kasus-card">
                        <div class="kasus-header">
                            <h3 class="kasus-title">Peminjaman #<?php echo $kasus['id']; ?></h3>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?');">
                                <input type="hidden" name="kasus_id" value="<?php echo $kasus['id']; ?>">
                                <button type="submit" name="delete_kasus" class="delete-btn" style="padding: 4px 8px; font-size: 12px;">Hapus</button>
                            </form>
                        </div>
                        <div class="kasus-content">
                            <div class="kasus-info">
                                <p><strong>Peminjam:</strong> <?php echo $kasus['nama_guru'] ?? 'Unknown'; ?></p>
                                <p><strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($kasus['tanggal'])); ?></p>
                                <p><strong>Waktu:</strong> <?php echo date('H:i', strtotime($kasus['waktu_mulai'])); ?> - <?php echo date('H:i', strtotime($kasus['waktu_selesai'])); ?></p>
                            </div>
                            
                            <div class="kasus-barang">
                                <h4>Barang yang Dipinjam:</h4>
                                <?php if (empty($kasus['barang'])): ?>
                                    <p>Tidak ada barang</p>
                                <?php else: ?>
                                    <ul class="barang-list">
                                        <?php foreach ($kasus['barang'] as $barang): ?>
                                            <li><?php echo $barang; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            
                            <div class="status-labels">
                                <?php 
                                $acc_class = 'status-acc';
                                if ($kasus['status_acc'] === 'disetujui') {
                                    $acc_class .= ' approved';
                                } else if ($kasus['status_acc'] === 'ditolak') {
                                    $acc_class .= ' rejected';
                                }
                                
                                $kondisi_class = 'status-kondisi';
                                if ($kasus['kondisi'] === 'selesai') {
                                    $kondisi_class .= ' completed';
                                }
                                ?>
                                <div class="status-label <?php echo $acc_class; ?>">
                                    <?php echo ucfirst($kasus['status_acc']); ?>
                                </div>
                                <div class="status-label <?php echo $kondisi_class; ?>">
                                    <?php echo ucfirst($kasus['kondisi']); ?>
                                </div>
                            </div>
                            
                            <!-- Form to update status and condition -->
                            <form method="POST" class="status-form">
                                <input type="hidden" name="kasus_id" value="<?php echo $kasus['id']; ?>">
                                
                                <div class="form-group">
                                    <label for="status_acc_<?php echo $kasus['id']; ?>">Status Persetujuan:</label>
                                    <select name="status_acc" id="status_acc_<?php echo $kasus['id']; ?>">
                                        <option value="menunggu" <?php echo ($kasus['status_acc'] === 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="disetujui" <?php echo ($kasus['status_acc'] === 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                        <option value="ditolak" <?php echo ($kasus['status_acc'] === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="kondisi_<?php echo $kasus['id']; ?>">Kondisi:</label>
                                    <select name="kondisi" id="kondisi_<?php echo $kasus['id']; ?>">
                                        <option value="belum selesai" <?php echo ($kasus['kondisi'] === 'belum selesai') ? 'selected' : ''; ?>>Belum Selesai</option>
                                        <option value="selesai" <?php echo ($kasus['kondisi'] === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="update_status" class="update-btn">Update Status</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Add any JavaScript needed for the admin history page
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success messages after 5 seconds
            setTimeout(function() {
                const successMessages = document.querySelectorAll('.success-message');
                successMessages.forEach(function(message) {
                    message.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>