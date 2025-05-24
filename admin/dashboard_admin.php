<?php
include 'includes_admin/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="css/dashboard_admin.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<?php include 'includes_admin/navbar_admin.php'; ?>

    <div class="dashboard-container">
        <h2>Data Barang</h2>
        <?php
        include '../includes/database/connection.php';
        $result = $conn->query("SELECT * FROM barang ORDER BY nama_barang");
        if ($result && $result->num_rows > 0): ?>
        <table style="width:100%;border-collapse:collapse;margin-top:10px;">
            <thead>
                <tr style="background:#f1f1f1;">
                    <th style="padding:8px;border:1px solid #ccc;">No</th>
                    <th style="padding:8px;border:1px solid #ccc;">Nama Barang</th>
                    <th style="padding:8px;border:1px solid #ccc;">Jumlah</th>
                    <th style="padding:8px;border:1px solid #ccc;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="padding:8px;border:1px solid #ccc;text-align:center;"> <?php echo $no++; ?> </td>
                    <td style="padding:8px;border:1px solid #ccc;"> <?php echo htmlspecialchars($row['nama_barang']); ?> </td>
                    <td style="padding:8px;border:1px solid #ccc;text-align:center;"> <?php echo $row['jumlah']; ?> </td>
                    <td style="padding:8px;border:1px solid #ccc;text-align:center;">
                        <a href="#" class="edit-btn" data-id="<?php echo $row['id']; ?>" style="color:#007bff;text-decoration:underline;">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Tidak ada data barang.</p>
        <?php endif; ?>
    </div>
    <!-- Modal Edit Barang -->
    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeModalBtn">&times;</span>
            <div id="modalFormContent">Loading...</div>
        </div>
    </div>
    <div id="modalOverlay" class="modal-overlay" style="display:none;"></div>
    <script>
    // Open modal and load form via AJAX
    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var itemId = this.getAttribute('data-id');
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('modalFormContent').innerHTML = 'Loading...';
            // Load form via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'edit_barang.php?id=' + itemId + '&popup=1', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('modalFormContent').innerHTML = xhr.responseText;
                    // Attach submit event
                    var form = document.getElementById('editBarangForm');
                    if (form) {
                        form.onsubmit = function(ev) {
                            ev.preventDefault();
                            var formData = new FormData(form);
                            var xhr2 = new XMLHttpRequest();
                            xhr2.open('POST', 'edit_barang.php?id=' + itemId + '&popup=1', true);
                            xhr2.onload = function() {
                                if (xhr2.status === 200) {
                                    // Close modal and reload table
                                    document.getElementById('editModal').style.display = 'none';
                                    document.getElementById('modalOverlay').style.display = 'none';
                                    location.reload();
                                } else {
                                    alert('Gagal update barang!');
                                }
                            };
                            xhr2.send(formData);
                        }
                    }
                } else {
                    document.getElementById('modalFormContent').innerHTML = 'Gagal memuat form.';
                }
            };
            xhr.send();
        });
    });
    // Close modal
    document.getElementById('closeModalBtn').onclick = function() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    };
    document.getElementById('modalOverlay').onclick = function() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    };
    </script>
</body>
</html>
