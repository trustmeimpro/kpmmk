<?php
// Start session to track form submission
session_start();

// Include database connection
require_once 'includes/database/connection.php';

// Initialize variables
$error_message = '';
$success = false;

// Initialize variables for the second form
$show_qty_form = false;
$form_data = [];
$selected_items_with_data = [];

// Get selected items from URL parameter if present
$selected_items = [];
if (!empty($_GET['items'])) {
    $selected_items = explode(',', $_GET['items']);
    // Make sure all items are integers
    foreach ($selected_items as $key => $value) {
        $selected_items[$key] = intval($value);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['step']) && $_POST['step'] == '2') {
        // This is the second form (quantity selection)
        $nip = $_POST['nip'];
        $tanggal = $_POST['tanggal'];
        $waktu_mulai = $_POST['waktu_mulai'];
        $waktu_selesai = $_POST['waktu_selesai'];
        $selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];
        $item_quantities = isset($_POST['qty']) ? $_POST['qty'] : [];
        
        // Validate NIP exists in database
        $stmt = $conn->prepare("SELECT * FROM nip_guru WHERE nip = ?");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = "NIP tidak ditemukan dalam database. Silakan masukkan NIP yang valid.";
        } else {
            // NIP is valid, proceed with inserting kasus
            $status_acc = "menunggu"; // Default status
            $kondisi = "belum selesai"; // Default condition
            
            // Insert into kasus table
            $stmt = $conn->prepare("INSERT INTO kasus (nip, tanggal, waktu_mulai, waktu_selesai, status_acc, kondisi, kasus_barang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nip, $tanggal, $waktu_mulai, $waktu_selesai, $status_acc, $kondisi, $kasus_barang);
            $kasus_barang = 0; // Default value for kasus_barang field
            
            if ($stmt->execute()) {
                $kasus_id = $conn->insert_id;
                
                // Insert each selected item into kasus_barang table with quantity
                $success = true;
                
                foreach ($selected_items as $index => $item_id) {
                    $qty = isset($item_quantities[$item_id]) ? intval($item_quantities[$item_id]) : 1;
                    
                    // Check if there's enough stock
                    $check_stock = $conn->prepare("SELECT jumlah FROM barang WHERE id = ?");
                    $check_stock->bind_param("i", $item_id);
                    $check_stock->execute();
                    $stock_result = $check_stock->get_result();
                    $stock_data = $stock_result->fetch_assoc();
                    
                    if ($stock_data && $stock_data['jumlah'] >= $qty) {
                        // Update stock
                        $new_stock = $stock_data['jumlah'] - $qty;
                        $update_stock = $conn->prepare("UPDATE barang SET jumlah = ? WHERE id = ?");
                        $update_stock->bind_param("ii", $new_stock, $item_id);
                        $update_stock->execute();
                        
                        // Check if qty column exists in kasus_barang table
                        $column_check = $conn->query("SHOW COLUMNS FROM kasus_barang LIKE 'qty'");
                        
                        if ($column_check->num_rows > 0) {
                            // The qty column exists, use it
                            $stmt = $conn->prepare("INSERT INTO kasus_barang (kasus_id, barang_id, qty) VALUES (?, ?, ?)");
                            $stmt->bind_param("iii", $kasus_id, $item_id, $qty);
                        } else {
                            // The qty column doesn't exist, just insert kasus_id and barang_id
                            $stmt = $conn->prepare("INSERT INTO kasus_barang (kasus_id, barang_id) VALUES (?, ?)");
                            $stmt->bind_param("ii", $kasus_id, $item_id);
                        }
                        
                        if (!$stmt->execute()) {
                            $success = false;
                            $error_message = "Gagal menyimpan data barang: " . $conn->error;
                            break;
                        }
                    } else {
                        $success = false;
                        $error_message = "Stok tidak mencukupi untuk beberapa barang yang dipilih.";
                        break;
                    }
                }
                
                if ($success) {
                    if (!empty($_GET['popup'])) {
                        // For popup, return success message with data attributes instead of inline script
                        echo '<div class="success-message" data-success="true" data-kasus-id="' . $kasus_id . '">Data berhasil disimpan</div>';
                        exit;
                    } else {
                        // Set session variable to indicate form was submitted
                        $_SESSION['form_submitted'] = true;
                        
                        // Redirect to history page
                        header("Location: history.php");
                        exit;
                    }
                }
            } else {
                $error_message = "Gagal menyimpan data kasus: " . $conn->error;
            }
        }
    } else {
        // This is the first form (basic info)
        $nip = $_POST['nip'];
        $tanggal = $_POST['tanggal'];
        $waktu_mulai = $_POST['waktu_mulai'];
        $waktu_selesai = $_POST['waktu_selesai'];
        
        // Make sure we have selected items either from POST or from URL
        if (isset($_POST['selected_items'])) {
            $selected_items = json_decode($_POST['selected_items'], true);
            if (!is_array($selected_items)) {
                $selected_items = [];
            }
        } elseif (!empty($_GET['items'])) {
            // If not in POST, try to get from URL again
            $selected_items = explode(',', $_GET['items']);
            foreach ($selected_items as $key => $value) {
                $selected_items[$key] = intval($value);
            }
        } else {
            $selected_items = [];
        }
        
        // Validate NIP exists in database
        $stmt = $conn->prepare("SELECT * FROM nip_guru WHERE nip = ?");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = "NIP tidak ditemukan dalam database. Silakan masukkan NIP yang valid.";
        } else {
            // NIP is valid, proceed to second form
            $show_qty_form = true;
            $form_data = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'waktu_mulai' => $waktu_mulai,
                'waktu_selesai' => $waktu_selesai,
                'selected_items' => $selected_items
            ];
            
            // Initialize the array to store selected items with data
            $selected_items_with_data = [];
            
            // Get item details for the selected items from the barang table
            foreach ($selected_items as $item_id) {
                $stmt = $conn->prepare("SELECT id, nama_barang, jumlah FROM barang WHERE id = ?");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item_data = $result->fetch_assoc();
                
                if ($item_data) {
                    $selected_items_with_data[] = $item_data;
                }
            }
        }
    }
}
?>
<?php if (!empty($_GET['popup'])): ?>
    <!-- For popup mode, just return the form content -->
    
    
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <link rel="stylesheet" href="css/popupkasus.css">
<?php else: ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/popupkasus.css">
</head>
<body>
    <div class="popup-container">
        <div class="popup-content">
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
<?php endif; ?>
            
            <?php if ($show_qty_form): ?>
            <!-- Second form - Quantity selection -->
            <h2>Pilih Jumlah Barang</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="kasusForm">
                <!-- Hidden fields to carry over data from first form -->
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="nip" value="<?php echo htmlspecialchars($form_data['nip']); ?>">
                <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($form_data['tanggal']); ?>">
                <input type="hidden" name="waktu_mulai" value="<?php echo htmlspecialchars($form_data['waktu_mulai']); ?>">
                <input type="hidden" name="waktu_selesai" value="<?php echo htmlspecialchars($form_data['waktu_selesai']); ?>">
                <input type="hidden" name="selected_items" value="<?php echo htmlspecialchars(json_encode($form_data['selected_items'])); ?>">
                
                <div class="selected-items-container">
                    <?php if (empty($selected_items_with_data)): ?>
                        <p>Tidak ada barang yang dipilih.</p>
                    <?php else: ?>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Stok Tersedia</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selected_items_with_data as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                        <td><?php echo htmlspecialchars($item['jumlah']); ?></td>
                                        <td>
                                            <input type="number" name="qty[<?php echo $item['id']; ?>]" min="1" max="<?php echo $item['jumlah']; ?>" value="1" required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="button-group">
                   
                    <button type="submit" class="submit-button">Simpan</button>
                </div>
                
                <script>
                    window.goBack = function() {
                        // Use history.back() to go back to the first form
                        window.history.back();
                    };
                </script>
            </form>
            
            <?php else: ?>
            <!-- First form - Basic information -->
            <h2>Form Peminjaman Barang</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="kasusForm">
                <div class="form-group">
                    <label for="nip">NIP:</label>
                    <input type="text" id="nip" name="nip" required>
                </div>
                
                <div class="form-group">
                    <label for="tanggal">Tanggal:</label>
                    <input type="date" id="tanggal" name="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="waktu_mulai">Waktu Mulai:</label>
                    <input type="time" id="waktu_mulai" name="waktu_mulai" required>
                </div>
                
                <div class="form-group">
                    <label for="waktu_selesai">Waktu Selesai:</label>
                    <input type="time" id="waktu_selesai" name="waktu_selesai" required>
                </div>
                
                <input type="hidden" id="selected_items" name="selected_items" value="<?php echo htmlspecialchars(json_encode($selected_items)); ?>">
                
                <div class="button-group">
                    <?php if (!empty($_GET['popup'])): ?>
                        <button type="button" class="cancel-button" id="cancelButton">Batal</button>
                    <?php else: ?>
                        <button type="button" class="cancel-button" onclick="window.location.href='index.php'">Batal</button>
                    <?php endif; ?>
                    <button type="submit" class="submit-button">Lanjut</button>
                </div>
                
                <?php if (!empty($_GET['popup'])): ?>
                <script>
                    // Add event listener to cancel button
                    document.getElementById('cancelButton').addEventListener('click', function() {
                        // In popup mode, we need to tell the parent window to close the modal
                        if (window.parent && typeof window.parent.closeKasusModal === 'function') {
                            window.parent.closeKasusModal();
                        }
                    });
                </script>
                <?php endif; ?>
            </form>
            <?php endif; ?>
            
            <?php if (!empty($_GET['popup'])): ?>
                <!-- No additional content needed for popup mode -->
            <?php else: ?>
        </div>
    </div>
            <?php endif; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get selected items from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const selectedItemsParam = urlParams.get('items');
            
            if (selectedItemsParam) {
                const selectedItems = selectedItemsParam.split(',');
                console.log('Selected items:', selectedItems);
                document.getElementById('selected_items').value = JSON.stringify(selectedItems);
                
                // Display selected items for debugging
                <?php if (empty($_GET['popup'])): ?>
                const debugInfo = document.createElement('div');
                debugInfo.innerHTML = '<p><strong>Selected Items:</strong> ' + selectedItems.join(', ') + '</p>';
                document.querySelector('.popup-content').appendChild(debugInfo);
                <?php endif; ?>
            }
        });
    </script>
    
<?php if (empty($_GET['popup'])): ?>
</body>
</html>
<?php endif; ?>