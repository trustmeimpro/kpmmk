<nav class="navbar">
    <div class="search-container">
        <input type="text" class="search-bar" placeholder="Search...">
    </div>
    <div class="menu-container">
        <div class="menu-button" onclick="toggleMenu()">
            <div class="menu-icon"></div>
            <div class="menu-icon"></div>
            <div class="menu-icon"></div>
        </div>
        <ul class="menu-items" id="menuItems">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="history.php">History</a></li>
            <li><a href="admin/login_form.php">Login</a></li>
        </ul>
    </div>
</nav>

<script>
    function toggleMenu() {
        var menuItems = document.getElementById("menuItems");
        menuItems.classList.toggle("show");
    }
</script> 