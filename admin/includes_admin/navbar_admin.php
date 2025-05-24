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
            <li><a href="dashboard_admin.php">Dashboard</a></li>
            <li><a href="history_admin.php">History</a></li>
            <li><a href="includes_admin/logout.php">logout</a></li>
        </ul>   
    </div>
</nav>

<script>
    function toggleMenu() {
        var menuItems = document.getElementById("menuItems");
        menuItems.classList.toggle("show");
    }
</script> 