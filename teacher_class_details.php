<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'] ?? null;

    if (!isset($admin_id)) {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['class_id'])) {
        $class_id = $_GET['class_id'];
        
        $query = "SELECT * FROM classes WHERE class_id = '$class_id' AND teacher = '$admin_id'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $class = mysqli_fetch_assoc($result);
        } else {
            echo "Class not found or you don't have permission to view it.";
            exit();
        }

        if (isset($_POST['remove_student_id'])) {
            $student_id = $_POST['remove_student_id'];
            $remove_query = "DELETE FROM student_classes WHERE class_id = '$class_id' AND user_id = '$student_id'";
            mysqli_query($conn, $remove_query);
        }

        $student_query = "SELECT u.id, u.name, u.email FROM users u
                        JOIN student_classes sc ON u.id = sc.user_id
                        WHERE sc.class_id = '$class_id'";
        $student_result = mysqli_query($conn, $student_query);
        
        if (!$student_result) {
            die("Query failed: " . mysqli_error($conn)); 
        }
    } else {
        echo "No class selected.";
        exit();
    }

    $test_query = "SELECT * FROM tests WHERE class_id = '$class_id' AND teacher_id = '$admin_id'";
    $test_result = mysqli_query($conn, $test_query);

    if (!$test_result) {
        die("Query failed: " . mysqli_error($conn)); 
    }

    if (isset($_POST['delete_test_id'])) {
        $test_id_to_delete = intval($_POST['delete_test_id']);

        if (isset($_GET['class_id'])) {
            $class_id = $_GET['class_id'];
        } else {
            die("Class ID is missing. Unable to delete test.");
        }


        $delete_options_query = "DELETE FROM question_options WHERE question_id IN (SELECT question_id FROM questions WHERE test_id = '$test_id_to_delete')";
        mysqli_query($conn, $delete_options_query);

        $delete_questions_query = "DELETE FROM questions WHERE test_id = '$test_id_to_delete'";
        mysqli_query($conn, $delete_questions_query);

        $delete_test_query = "DELETE FROM tests WHERE test_id = '$test_id_to_delete'";
        mysqli_query($conn, $delete_test_query);

        header("Location: teacher_class_details.php?class_id=$class_id");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Class Details</title>
        <link rel="stylesheet" href="css/test.css">
        <script>
            function toggleStudentList() {
                const studentList = document.getElementById("student-list");
                const toggleButton = document.getElementById("toggle-students");
                if (studentList.style.display === "none") {
                    studentList.style.display = "block";
                    toggleButton.textContent = "Hide Students";
                } else {
                    studentList.style.display = "none";
                    toggleButton.textContent = "Show Students";
                }
            }

            function confirmRemove(studentId) {
                const confirmation = confirm("Are you sure you want to remove this student from the class?");
                if (confirmation) {
                    
                    document.getElementById("remove-form-" + studentId).submit();
                }
            }
        </script>
    </head>
    <body>
        <?php

        if (isset($admin_id)) {
            include 'teacher_header.php';
        } 
        ?>

        <section class="class-details">
            <div class="class-header">
                <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
                <p class="class-code">Class Code: <span><?php echo htmlspecialchars($class['class_code']); ?></span></p>
                <p class="teacher-info">You are the teacher for this class.</p>
            </div>

            <div class="student-list">
                <h3>Enrolled Students:</h3>
                <button id="toggle-students" onclick="toggleStudentList()">Show Students</button>
                <ul id="student-list" style="display: none;">
                    <?php

                    if (mysqli_num_rows($student_result) > 0) {
                        while ($student = mysqli_fetch_assoc($student_result)) {
                            echo "<li class='student-item'>" . htmlspecialchars($student['name']) . " - " . htmlspecialchars($student['email']) . "
                                    <form id='remove-form-" . $student['id'] . "' method='POST' style='display:inline-block;'>
                                        <input type='hidden' name='remove_student_id' value='" . $student['id'] . "'>
                                        <button type='button' class='remove-button' onclick='confirmRemove(" . $student['id'] . ")'>Remove from Class</button>
                                    </form>
                                </li>";
                        }
                    } else {
                        echo "<p class='no-students'>No students enrolled in this class.</p>";
                    }
                    ?>
                </ul>
            </div>
        </section>

        <div class="test-list">
            <h3>Tests Created for this Class:</h3>
            <ul>
                <?php
                    if (mysqli_num_rows($test_result) > 0) {
                        while ($test = mysqli_fetch_assoc($test_result)) {
                            echo "<li class='test-item'>
                                    <strong>" . htmlspecialchars($test['test_name']) . "</strong>
                                    <p>Description: " . htmlspecialchars($test['test_description']) . "</p>
                                    <p>Total Points: " . htmlspecialchars($test['total_points']) . "</p>
                                    <p>Due Date: " . htmlspecialchars($test['due_date']) . "</p>
                                    <a href='teacher_view_test.php?test_id=" . $test['test_id'] . "'>View Test</a>
                                    <form method='POST' style='display:inline-block;'>
                                        <input type='hidden' name='delete_test_id' value='" . $test['test_id'] . "'>
                                        <button class='delete_test' type='submit' onclick='return confirm(\"Are you sure you want to delete this test?\")'>Delete Test</button>
                                    </form>
                                </li>";
                        }
                    } else {
                        echo "<p class='no-tests'>No tests created for this class.</p>";
                    }
                ?>
            </ul>
        </div>
    </body>
</html>
