<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'];

    if (!isset($admin_id)) {
        header('location:login.php');
        exit();
    }

    $query = "SELECT * FROM classes WHERE teacher = '$admin_id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    function generateClassCode($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomCode = '';
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomCode;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
        $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
        if (!empty($class_name)) {
            $class_code = generateClassCode();

            $checkQuery = "SELECT class_code FROM classes WHERE class_code = '$class_code'";
            $result = mysqli_query($conn, $checkQuery);
            while (mysqli_num_rows($result) > 0) {
                $class_code = generateClassCode();
                $result = mysqli_query($conn, $checkQuery);
            }

            $query = "INSERT INTO classes (class_name, teacher, class_code) VALUES ('$class_name', '$admin_id', '$class_code')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = "Class created successfully!";
                header("Location: teacher_classes.php");
                exit();
            } else {
                $_SESSION['message'] = "Failed to create class!";
                header("Location: teacher_classes.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Class name cannot be empty!";
            header("Location: teacher_classes.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your Classes - Teacher</title>
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>
        <?php include 'teacher_header.php'; ?>

        <section class="dashboard">
            <h2>Your Classes</h2>
            <div class="class-grid">
                <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<div class='class-card' onclick='window.location.href=\"teacher_class_details.php?class_id=" . $row['class_id'] . "\"'>
                                    <h3>" . htmlspecialchars($row['class_name']) . "</h3>
                                    <p>Class Code: " . htmlspecialchars($row['class_code']) . "</p>
                                </div>";
                        }
                    } else {
                        echo "<p>No classes found.</p>";
                    }
                ?>
            </div>
        </section>

        <a href="#" id="open-popup" class="plus">+<span class="tooltip-text">Create a class</span></a>

        <div class="popup" id="popup">
            <div class="popup-content">
                <span class="close-btn" id="close-popup">&times;</span>
                <h3>Create a new class</h3>
                <form method="POST">
                    <input type="text" name="class_name" placeholder="Enter class name" required>
                    <button type="submit" name="create_class">Create</button>
                </form>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo (strpos($_SESSION['message'], 'successfully') !== false) ? 'success' : 'error'; ?>" id="form-message">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
            </div>
        </div>

        <script>
            const popup = document.getElementById('popup');
            const openPopup = document.getElementById('open-popup');
            const closePopup = document.getElementById('close-popup');
            const formMessage = document.getElementById('form-message');

            openPopup.addEventListener('click', (e) => {
                e.preventDefault();
                popup.style.display = 'flex';
                if (formMessage) {
                    formMessage.style.display = 'none';
                }
            });

            closePopup.addEventListener('click', () => {
                popup.style.display = 'none';
                if (formMessage) {
                    formMessage.style.display = 'none';
                }
            });

            window.addEventListener('click', (event) => {
                if (event.target === popup) {
                    popup.style.display = 'none';
                    if (formMessage) {
                        formMessage.style.display = 'none';
                    }
                }
            });

            window.onload = function() {
                if (formMessage && formMessage.textContent.trim() !== "") {
                    popup.style.display = 'flex';
                    formMessage.style.display = 'block';
                }
            };
        </script>
    </body>
</html>
