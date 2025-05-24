<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/history.css">
    <style>
        .history-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .history-title {
            margin-bottom: 20px;
            color: #333;
        }
        
        .kasus-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .kasus-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        .kasus-header {
            background-color: #f5f5f5;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .kasus-title {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .kasus-content {
            padding: 15px;
        }
        
        .kasus-info {
            margin-bottom: 10px;
        }
        
        .kasus-info p {
            margin: 5px 0;
            color: #555;
        }
        
        .kasus-info strong {
            color: #333;
        }
        
        .kasus-barang {
            margin-top: 15px;
        }
        
        .kasus-barang h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
        }
        
        .barang-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .barang-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .barang-list li:last-child {
            border-bottom: none;
        }
        
        .status-labels {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .status-label {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
        
        .status-acc {
            background-color: #2196F3; /* Blue */
        }
        
        .status-acc.approved {
            background-color: #4CAF50; /* Green */
        }
        
        .status-acc.rejected {
            background-color: #F44336; /* Red */
        }
        
        .status-kondisi {
            background-color: #FF9800; /* Orange */
        }
        
        .status-kondisi.completed {
            background-color: #4CAF50; /* Green */
        }
        
        .no-kasus {
            text-align: center;
            padding: 30px;
            color: #777;
            font-size: 18px;
        }
        
        .clear-button {
            background-color: #F44336;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .clear-button:hover {
            background-color: #D32F2F;
        }
    </style>
</head>
<body>
    <?php 
    // Include database connection
    require_once 'includes/database/connection.php';
    
    // Initialize variables for messages
    $error_message = "";
    $success_message = "";

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
    
    <?php include 'includes/navbar.php'; ?>
    
    <div class="history-container">
        <h1 class="history-title">Riwayat Peminjaman</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message" style="background-color: #DFF2BF; color: #4F8A10; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="background-color: #FFBABA; color: #D8000C; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
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
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>