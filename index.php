<?php
// Start session
session_start();

// Reset form submission status when returning to index page
if (isset($_SESSION['form_submitted'])) {
    unset($_SESSION['form_submitted']);
}

// Include database-related files
require_once 'includes/database/connection.php';
require_once 'includes/database/get_items.php';
require_once 'includes/database/item_images.php';

// Get items from the database
$items = getAllItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .item-image {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            z-index: 1001;
        }
        
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalShow 0.2s;
        }
        
        @keyframes modalShow {
            from {transform: scale(0.8); opacity: 0;}
            to {transform: scale(1); opacity: 1;}
        }
        
        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #777;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php if (empty($items)): ?>
            <p class="no-items">Tidak ada barang yang tersedia.</p>
        <?php else: ?>
            <div class="items-grid">
                <?php foreach ($items as $index => $item): ?>
                    <?php 
                    $imageUrl = getItemImageUrl($item['nama_barang']);
                    $isSelected = $index === 0 ? 'selected' : '';
                    ?>
                    <div class="item-card <?php echo $isSelected; ?>" data-id="<?php echo $item['id']; ?>">
                        <div class="item-image" style="background-image: url('<?php echo $imageUrl; ?>')"></div>
                        <div class="item-name"><?php echo $item['nama_barang']; ?></div>
                        <div class="selection-counter">✓</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sticky-button">
        <button class="next-button">Lanjut (<span id="selected-count">1</span>)</button>
    </div>
    
    <!-- Modal Kasus -->
    <div id="kasusModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeModalBtn">&times;</span>
            <div id="modalFormContent">Loading...</div>
        </div>
    </div>
    <div id="modalOverlay" class="modal-overlay" style="display:none;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Track selected items by gathering initially selected elements
            let selectedItems = [];
            document.querySelectorAll('.item-card.selected').forEach(item => {
                selectedItems.push(item.getAttribute('data-id'));
            });
            
            const countElement = document.getElementById('selected-count');
            
            // Update count display
            function updateSelectedCount() {
                countElement.textContent = selectedItems.length;
            }
            
            // Initialize count
            updateSelectedCount();

            // Add click event to all item cards
            const itemCards = document.querySelectorAll('.item-card');
            itemCards.forEach(card => {
                card.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    
                    // Toggle selected class on clicked card
                    this.classList.toggle('selected');
                    
                    // Update selected items array
                    if (this.classList.contains('selected')) {
                        selectedItems.push(itemId);
                    } else {
                        selectedItems = selectedItems.filter(id => id !== itemId);
                    }
                    
                    // Update the count
                    updateSelectedCount();
                });
            });
            
            // Next button click handler
            document.querySelector('.next-button').addEventListener('click', function() {
                if (selectedItems.length > 0) {
                    console.log('Selected items:', selectedItems);
                    // Open popup modal
                    openKasusModal(selectedItems);
                } else {
                    alert('Silakan pilih minimal satu barang');
                }
            });
            
            // Function to open kasus modal
            function openKasusModal(items) {
                // Show modal and overlay
                document.getElementById('kasusModal').style.display = 'block';
                document.getElementById('modalOverlay').style.display = 'block';
                document.getElementById('modalFormContent').innerHTML = 'Loading...';
                
                // Load form via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'popupKasus.php?popup=1&items=' + items.join(','), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        document.getElementById('modalFormContent').innerHTML = xhr.responseText;
                        
                        // Attach submit event to the form
                        var form = document.getElementById('kasusForm');
                        if (form) {
                            form.onsubmit = function(ev) {
                                ev.preventDefault();
                                var formData = new FormData(form);
                                var xhr2 = new XMLHttpRequest();
                                xhr2.open('POST', 'popupKasus.php?popup=1&items=' + items.join(','), true);
                                xhr2.onload = function() {
                                    if (xhr2.status === 200) {
                                        // Parse the response to check for success or second form
                                        var tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = xhr2.responseText;
                                        
                                        // Check if there's a success message with data attributes
                                        var successMsg = tempDiv.querySelector('.success-message[data-success="true"]');
                                        if (successMsg) {
                                            // Close modal and redirect to history
                                            closeKasusModal();
                                            window.location.href = 'history.php';
                                            return;
                                        }
                                        
                                        // Check if the response contains a second form
                                        if (xhr2.responseText.includes('name="step" value="2"') || xhr2.responseText.includes('Pilih Jumlah Barang')) {
                                            // Show the second form
                                            document.getElementById('modalFormContent').innerHTML = xhr2.responseText;
                                            
                                            // Attach submit event to the second form
                                            var secondForm = document.getElementById('kasusForm');
                                            if (secondForm) {
                                                secondForm.onsubmit = function(ev2) {
                                                    ev2.preventDefault();
                                                    var formData2 = new FormData(secondForm);
                                                    var xhr3 = new XMLHttpRequest();
                                                    xhr3.open('POST', 'popupKasus.php?popup=1&items=' + items.join(','), true);
                                                    xhr3.onload = function() {
                                                        if (xhr3.status === 200) {
                                                            // Parse the response to check for success
                                                            var tempDiv = document.createElement('div');
                                                            tempDiv.innerHTML = xhr3.responseText;
                                                            
                                                            // Check if there's a success message with data attributes
                                                            var successMsg = tempDiv.querySelector('.success-message[data-success="true"]');
                                                            if (successMsg) {
                                                                // Close modal and redirect to history
                                                                closeKasusModal();
                                                                window.location.href = 'history.php';
                                                            } else {
                                                                // Show the response in the modal
                                                                document.getElementById('modalFormContent').innerHTML = xhr3.responseText;
                                                            }
                                                        } else {
                                                            alert('Gagal menyimpan data!');
                                                        }
                                                    };
                                                    xhr3.send(formData2);
                                                };
                                            }
                                        } else {
                                            // Close modal and redirect to history
                                            closeKasusModal();
                                            window.location.href = 'history.php';
                                        }
                                    } else {
                                        alert('Gagal menyimpan data!');
                                    }
                                };
                                xhr2.send(formData);                                
                            };
                        }
                    } else {
                        document.getElementById('modalFormContent').innerHTML = 'Gagal memuat form.';
                    }
                };
                xhr.send();
            }
            
            // Close modal function
            function closeKasusModal() {
                document.getElementById('kasusModal').style.display = 'none';
                document.getElementById('modalOverlay').style.display = 'none';
            }
            
            // Attach close events
            document.getElementById('closeModalBtn').onclick = closeKasusModal;
            document.getElementById('modalOverlay').onclick = closeKasusModal;
        });
    </script>
</body>
</html> 