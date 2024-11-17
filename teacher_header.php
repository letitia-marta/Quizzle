<header class="header">
    <div class="flex">
        <a id="hamburger" class="hamburger">
            <img src="images/menu_icon.png" alt="Buton cu imagine" class="navbar-button-image">
        </a>

        <div class="header-title">
            <h1>Quizzle <span class="teacher-label">Teacher</span></h1>
        </div>


        <nav class="navbar">
            <a href="teacher_page.php">Home</a>
            <a href="admin_contacts.php">Settings</a>
        </nav>

        <div class="icons">
            <a id="menu-btn" class="menu-icon">
                <img src="images/menu_icon.png" alt="Menu Button" class="icon-button-image">
            </a>
            <a id="user-btn" class="user-icon">
                <img src="images/user_icon.png" alt="User Button" class="icon-button-image">
            </a>
        </div>


        <div class="account-box">
            <p>Teacher: <span><?php echo $_SESSION['teacher_name']; ?></span></p>
            <p>Email: <span><?php echo $_SESSION['teacher_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">Logout</a>
        </div>

        <script src="js/admin_script.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const menuButton = document.querySelector(".hamburger");
                const navbar = document.querySelector(".navbar");

                menuButton.addEventListener("click", function() {
                    navbar.classList.toggle("navbar-expanded");
                });
            });
        </script>

    </div>
</header>
