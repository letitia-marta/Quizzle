<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'] ?? null;

    if (!isset($student_id))
    {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['class_id']))
    {
        $class_id = $_GET['class_id'];
        
        $query = "
            SELECT c.class_name, c.teacher, t.name AS teacher_name 
            FROM classes c 
            INNER JOIN users t ON c.teacher = t.id
            INNER JOIN student_classes sc ON c.class_id = sc.class_id
            WHERE c.class_id = '$class_id' AND sc.user_id = '$student_id'
        ";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0)
        {
            $class = mysqli_fetch_assoc($result);
        }
        else
        {
            echo "Class not found or you are not enrolled in this class.";
            exit();
        }

        $students_query = "
            SELECT u.id, u.name, u.email 
            FROM users u
            JOIN student_classes sc ON u.id = sc.user_id
            WHERE sc.class_id = '$class_id'
        ";
        $students_result = mysqli_query($conn, $students_query);

        if (!$students_result)
        {
            die("Query failed: " . mysqli_error($conn)); 
        }
    }
    else
    {
        echo "No class selected.";
        exit();
    }

    $test_query = "SELECT * FROM tests WHERE class_id = '$class_id' ";
    $test_result = mysqli_query($conn, $test_query);

    if (!$test_result)
    {
        die("Query failed: " . mysqli_error($conn));
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
            function toggleStudentList()
            {
                const studentList = document.getElementById("student-list");
                const toggleButton = document.getElementById("toggle-students");
                if (studentList.style.display === "none")
                {
                    studentList.style.display = "block";
                    toggleButton.textContent = "Hide Students";
                }
                else
                {
                    studentList.style.display = "none";
                    toggleButton.textContent = "Show Students";
                }
            }

            function confirmRemove(studentId)
            {
                const confirmation = confirm("Are you sure you want to remove this student from the class?");
                if (confirmation)
                {
                    document.getElementById("remove-form-" + studentId).submit();
                }
            }
        </script>
    </head>

    <body>
        <?php if (isset($student_id))
            {
                include 'student_header.php';
            }
        ?>

        <section class="class-details" style="width: 59%; margin: 0 auto;">
            <div class="class-header" >
                <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
                <p class="class-code">Professor: <span><?php echo htmlspecialchars($class['teacher_name']); ?></span></p>
                <p class="teacher-info">You are enrolled in this class.</p>
            </div>

            <div class="student-list">
                <h3>Enrolled Students:</h3>
                <button id="toggle-students" onclick="toggleStudentList()">Show Students</button>
                <ul id="student-list" style="display: none;">
                    <?php
                    if (mysqli_num_rows($students_result) > 0)
                    {
                        while ($student = mysqli_fetch_assoc($students_result))
                        {
                            echo "<li class='student-item'>" . htmlspecialchars($student['name']) . " - " . htmlspecialchars($student['email']) . "
                                    <form id='remove-form-" . $student['id'] . "' method='POST' style='display:inline-block;'>
                                        <input type='hidden' name='remove_student_id' value='" . $student['id'] . "'>
                                    </form>
                                </li>";
                        }
                    }
                    else
                    {
                        echo "<p class='no-students'>No students enrolled in this class.</p>";
                    }
                    ?>
                </ul>
            </div>
        </section>

        <div class="test-view">
            <h2>Tests Created for this Class</h2>

            <?php
                if (mysqli_num_rows($test_result) > 0)
                {
                    while ($test = mysqli_fetch_assoc($test_result))
                    {
                        $test_id = $test['test_id'];
                        $due_date = $test['due_date'];
                        $current_date = date("Y-m-d H:i:s");

        
                        $score_query = "
                            SELECT score 
                            FROM results 
                            WHERE student_id = '$student_id' AND test_id = '$test_id'
                        ";
                        $score_result = mysqli_query($conn, $score_query);

                        $score_row = mysqli_fetch_assoc($score_result);
                        $score = $score_row ? $score_row['score'] : null;

                        echo "<div class='test-card'>";
                        echo "<div class='test-info'>";
                        echo "<h4>" . htmlspecialchars($test['test_name']) . "</h4>";
                        echo "<p>Total Points: " . htmlspecialchars($test['total_points']) . "</p>";
                        echo "<p>Due Date: " . htmlspecialchars($due_date) . "</p>";
                        echo "</div>";

                        if ($score !== null)
                        {
                            echo "<div class='test-status' style='margin-top: 20px;'>
                                    <p>Score: $score / " . htmlspecialchars($test['total_points']) . "</p>
                                    <a href='student_progress.php?test_id=$test_id' class='action-btn'>My Answers</a>
                                </div>";
                        }
                        elseif ($current_date > $due_date)
                        {
                            echo "<div class='test-status' style='margin-top: 30px;'>
                                    <p style='color: red;'>The due date has passed.</p>
                                </div>";
                        }
                        else
                        {
                            echo "<div class='test-status' style='margin-top: 30px;'>
                                    <a href='student_view_test.php?test_id=$test_id' class='action-btn'>Solve</a>
                                </div>";
                        }
                        echo "</div>";
                    }
                }
                else
                {
                    echo "<p>No tests created for this class.</p>";
                }
            ?>
        </div>
    </body>
</html>
