<?php
    include 'config.php';

    if (!isset($_SESSION['teacher_id'])) {
        header('location:login.php');
        exit();
    }

    $teacher_id = $_SESSION['teacher_id'];

    $query = "SELECT c.class_name 
            FROM classes c
            WHERE c.teacher = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $classes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }

    $stmt->close();
?>

<header class="header">
    <div class="flex">
        <a id="hamburger" class="hamburger">
            <img src="images/menu_icon.png" alt="Buton cu imagine" class="navbar-button-image">
        </a>

        <nav class="navbar">
            <a href="teacher_page.php">Home</a>
            <a href="admin_contacts.php">Settings</a>
            <h3>My Classes</h3>
            <?php 
            if (!empty($classes)) {
                foreach ($classes as $class) {
                    echo '<a href="#">' . htmlspecialchars($class['class_name']) . '</a>';
                }
            } else {
                echo '<p>No Classes Found</p>';
            }
            ?>
        </nav>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="account-box">
            <p>Name: <span><?php echo $_SESSION['teacher_name']; ?></span></p>
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