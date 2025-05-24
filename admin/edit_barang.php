<?php
// Cek login admin
include 'includes_admin/auth_check.php';
include '../includes/database/connection.php';

if (!isset($_GET['id'])) {
    if (empty($_GET['popup'])) {
        header('Location: dashboard_admin.php');
        exit();
    } else {
        echo '<div class="error">ID barang tidak ditemukan.</div>';
        exit();
    }
}

$id = intval($_GET['id']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah = intval($_POST['jumlah']);
    $stmt = $conn->prepare("UPDATE barang SET jumlah = ? WHERE id = ?");
    $stmt->bind_param("ii", $jumlah, $id);
    if ($stmt->execute()) {
        if (!empty($_GET['popup'])) {
            // Untuk popup, hanya return 200 OK
            exit();
        } else {
            header('Location: dashboard_admin.php');
            exit();
        }
    } else {
        $error = "Gagal mengupdate jumlah barang.";
    }
}

// Ambil data barang
$stmt = $conn->prepare("SELECT * FROM barang WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();
if (!$barang) {
    if (empty($_GET['popup'])) {
        header('Location: dashboard_admin.php');
        exit();
    } else {
        echo '<div class="error">Barang tidak ditemukan.</div>';
        exit();
    }
}

if (!empty($_GET['popup'])): ?>
    <div class="modal-form">
        <h3>Edit Jumlah Barang</h3>
        <form method="POST" id="editBarangForm">
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="jumlah" min="0" value="<?php echo $barang['jumlah']; ?>" required>
            </div>
            <button type="submit" class="btn-submit">Simpan</button>
            <?php if ($error) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>
        </form>
    </div>
<?php else: ?>
<?php endif; ?>
