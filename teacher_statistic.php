<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'] ?? null;

    if (!isset($admin_id))
    {
        header('location:login.php');
        exit();
    }

    $teacher_statistics_query = "
        SELECT c.class_id, c.class_name, t.test_id, t.test_name, t.total_points, t.due_date,
            COUNT(DISTINCT a.student_id) AS students_attempted,
            COUNT(a.answer_id) AS total_answers,
            COALESCE(ROUND(SUM(a.points) / COUNT(DISTINCT a.student_id), 2), 0) AS average_score
        FROM classes c
        INNER JOIN tests t ON c.class_id = t.class_id
        LEFT JOIN answers a ON t.test_id = a.test_id
        WHERE c.teacher = '$admin_id'
        GROUP BY c.class_id, c.class_name, t.test_id, t.test_name, t.total_points, t.due_date
        ORDER BY c.class_name, t.due_date
    ";

    $teacher_statistics_result = mysqli_query($conn, $teacher_statistics_query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/test.css">
        <title>Teacher Statistics</title>
    </head>

    <body>
        <?php
            include 'teacher_header.php';
        ?>

        <div class="test-view">
            <h2>Class and Test Statistics</h2>
            <?php
                $current_class = null;

                if (mysqli_num_rows($teacher_statistics_result) > 0)
                {
                    while ($row = mysqli_fetch_assoc($teacher_statistics_result))
                    {
                        $class_name = $row['class_name'];
                        $test_id = $row['test_id'];
                        $test_name = $row['test_name'];
                        $total_points = $row['total_points'];
                        $due_date = $row['due_date'];
                        $students_attempted = $row['students_attempted'];
                        $average_score = $row['average_score'];
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

                        $status = $current_date > $due_date
                            ? "<span class='status-closed'>Test closed</span>"
                            : "<span class='status-open'>Test open</span>";

                        echo "
                        <div class='test-card'>
                            <div class='test-info'>
                                <h4>" . htmlspecialchars($test_name) . "</h4>
                                <p><i class='icon-points'></i>Total Points: $total_points</p>
                                <p><i class='icon-calendar'></i>Due Date: $due_date</p>
                                <p><i class='icon-users'></i>Students Attempted: $students_attempted</p>
                                <p><i class='icon-average'></i>Average Score: $average_score</p>
                                
                            </div>

                            <div class='test-status'>
                                <p>$status</p>
                            </div>

                            <div class='test-actions'>
                                <a href='test_results.php?test_id=$test_id' class='action-btn'>Results</a>
                                <a href='teacher_view_test.php?test_id=$test_id' class='action-btn'>Edit</a>
                            </div>
                        </div>";
                    }
                    echo "</div>";
                }
                else
                {
                    echo "<p>No tests found for your classes.</p>";
                }
            ?>
        </div>
    </body>
</html>
