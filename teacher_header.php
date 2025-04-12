<header class="header">
    <div class="flex">
        <a id="hamburger" class="hamburger">
            <img src="images/menu_icon.png" alt="Buton cu imagine" class="navbar-button-image">
        </a>

        <div class="header-title">
           <a href="teacher_page.php"> <h1>Quizzle <span class="teacher-label">Teacher</span></h1></a>
        </div>

        <nav class="navbar">
            <a href="teacher_page.php">Home</a>
            <br>
            <h1 style="margin-left: 30px; color: #cdb566">My classes</h1>
            <div class="class-links" id="class-links" style="display: none;">
                <?php
                    $class_query = "SELECT class_name, class_id FROM classes WHERE teacher = '$admin_id'";
                    $class_result = mysqli_query($conn, $class_query);

                    if ($class_result && mysqli_num_rows($class_result) > 0)
                    {
                        while ($class_row = mysqli_fetch_assoc($class_result))
                        {
                            echo '<a href="teacher_class_details.php?class_id=' . htmlspecialchars($class_row['class_id']) . '">' . htmlspecialchars($class_row['class_name']) . '</a><br>';
                        }
                    }
                    else
                    {
                        echo '<p>No classes assigned.</p>';
                    }
                ?>
            </div>
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
            <a href="logout.php" class="action-btn">Logout</a>
        </div>

        <script src="js/admin_script.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const menuButton = document.querySelector(".hamburger");
                const navbar = document.querySelector(".navbar");
                const classLinks = document.getElementById("class-links");

                menuButton.addEventListener("click", function() {
                    navbar.classList.toggle("navbar-expanded");

                    if (navbar.classList.contains("navbar-expanded"))
                    {
                        classLinks.style.display = "block";
                    }
                    else
                    {
                        classLinks.style.display = "none";
                    }
                });
            });
        </script>
    </div>
</header>
