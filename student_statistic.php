<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'] ?? null;

    if (!isset($student_id))
    {
        header('location:login.php');
        exit();
    }

    $class_tests_query = "
        SELECT DISTINCT
            c.class_id, 
            c.class_name, 
            t.test_id, 
            t.test_name, 
            t.total_points, 
            t.due_date,
            r.score AS score_obtained
        FROM classes c
        INNER JOIN student_classes sc ON c.class_id = sc.class_id
        INNER JOIN tests t ON t.class_id = c.class_id
        LEFT JOIN results r ON r.test_id = t.test_id AND r.student_id = '$student_id'
        WHERE sc.user_id = '$student_id'
        ORDER BY c.class_name, t.due_date
    ";

    $class_tests_result = mysqli_query($conn, $class_tests_query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/test.css">
        <title>Student Statistics</title>
    </head>

    <body>
        <?php
            if (isset($student_id))
            {
                include 'student_header.php';
            }
        ?>

        <div class="test-view">
            <h2>Test Statistics by Class</h2>
            <?php
                $current_class = null;

                if (mysqli_num_rows($class_tests_result) > 0)
                {
                    while ($row = mysqli_fetch_assoc($class_tests_result))
                    {
                        $class_name = $row['class_name'];
                        $test_id = $row['test_id'];
                        $test_name = $row['test_name'];
                        $total_points = $row['total_points'];
                        $due_date = $row['due_date'];
                        $score_obtained = $row['score_obtained'] ?? null;
                        $current_date = date("Y-m-d H:i:s");

                        if ($current_class !== $class_name)
                        {
                            if ($current_class !== null)
                            {
                                echo "</div>"; 
                            }

                            echo "<div class='class-section'>";
                            echo "<h3>" . htmlspecialchars($class_name) . "</h3>";
                            $current_class = $class_name;
                        }

                        if ($score_obtained !== null)
                        {
                            $status = "Score: $score_obtained / $total_points";
                        }
                        elseif ($current_date > $due_date)
                        {
                            $status = "<p style='color: red;'>The due date has passed.</p>";
                        }
                        else
                        {
                            $status = "<a href='student_view_test.php?test_id=$test_id' class='action-btn'>Solve</a>";
                        }

                        echo "
                        <div class='test-card'>
                            <div class='test-info'>
                                <h4>" . htmlspecialchars($test_name) . "</h4>
                                <p>Total Points: $total_points</p>
                                <p>Due Date: $due_date</p>
                            </div>
                            
                            <div class='test-status' style='margin-top: 27px; margin-right: 10px'>
                                $status
                            </div>";

                        if ($score_obtained !== null)
                        {
                            echo "<a href='student_progress.php?test_id=$test_id' class='action-btn'>My Answers </a>";
                        }
                        echo "</div>"; 
                    }
                    echo "</div>";
                }
                else
                {
                    echo "<p>No tests found for your enrolled classes.</p>";
                }
            ?>
        </div>
    </body>
</html>
