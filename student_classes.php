<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'];

    if (!isset($student_id)) {
        header('location:login.php');
        exit;
    }

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_class'])) {
        $class_code = mysqli_real_escape_string($conn, $_POST['class_code']);
        if (!empty($class_code)) {

            $class_query = "SELECT class_id FROM classes WHERE class_code = '$class_code'";
            $class_result = mysqli_query($conn, $class_query);

            if (mysqli_num_rows($class_result) > 0) {
                $class_data = mysqli_fetch_assoc($class_result);
                $class_id = $class_data['class_id'];

                $check_enrollment = "SELECT * FROM student_classes WHERE user_id = '$student_id' AND class_id = '$class_id'";
                $enrollment_result = mysqli_query($conn, $check_enrollment);

                if (mysqli_num_rows($enrollment_result) == 0) {

                    $enrollment_date = date('Y-m-d H:i:s');
                    $enroll_query = "INSERT INTO student_classes (user_id, class_id, enrollment_date) VALUES ('$student_id', '$class_id', '$enrollment_date')";

                    if (mysqli_query($conn, $enroll_query)) {
                        $message = "Successfully joined the class!";
                    } else {
                        $message = "Failed to join the class. Please try again.";
                    }
                } else {
                    $message = "You are already enrolled in this class.";
                }
            } else {
                $message = "Invalid class code. Please try again.";
            }
        } else {
            $message = "Class code cannot be empty.";
        }
    }

        $query = "
            SELECT c.class_name, c.class_code, c.class_id 
            FROM classes c
            INNER JOIN student_classes sc ON c.class_id = sc.class_id
            WHERE sc.user_id = '$student_id'
        ";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn)); 
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your Classes</title>
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>
        <?php include 'student_header.php'; ?>

        <section class="dashboard">

            <div class="student-list" style="display: flex; justify-content: center; align-items: center; margin-bottom:60px;">
                <h3>Your Classes</h3>
            </div>
            
            <div class="class-grid">
                <?php

                if (mysqli_num_rows($result) > 0) {

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='class-card' onclick='window.location.href=\"student_class_details.php?class_id=" . $row['class_id'] . "\"'>
                                <h3>" . htmlspecialchars($row['class_name']) . "</h3>
                                <p>Class Code: " . htmlspecialchars($row['class_code']) . "</p>
                            </div>";
                    }
                } else {
                    echo "<p>You are not enrolled in any classes yet.</p>";
                }
                ?>
            </div>

            <a href="#" id="open-popup" class="plus">+<span class="tooltip-text">Join a class</span></a>

            <div class="popup" id="popup">
                <div class="popup-content">
                    <span class="close-btn" id="close-popup">&times;</span>
                    <h3>Join a class</h3>
                    <form method="POST">
                        <input type="text" name="class_code" placeholder="Enter class code" required>
                        <button type="submit" name="join_class">Join</button>
                    </form>

                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo (strpos($message, 'Successfully') !== false) ? 'success' : 'error'; ?>" id="form-message">
                            <?php echo $message; ?>
                        </div>
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
        </section>
    </body>
</html>
