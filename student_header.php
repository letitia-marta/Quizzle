<header class="header">
    <div class="flex">
        <a id="hamburger" class="hamburger">
            <img src="images/menu_icon.png" alt="Buton cu imagine" class="navbar-button-image">
        </a>

        <div class="header-title">
            <h1>Quizzle <span class="teacher-label">Student</span></h1>
        </div>

        <nav class="navbar">
            <a href="student_page.php">home</a>
            <a href="admin_contacts.php">settings</a>
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
            <p>Student: <span><?php echo $_SESSION['student_name']; ?></span></p>
            <p>Email: <span><?php echo $_SESSION['student_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">Log out</a>
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
