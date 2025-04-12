<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'] ?? null;

    if (!isset($admin_id))
    {
        header('location:login.php');
        exit();
    }

    $test_id = $_GET['test_id'] ?? null;

    if (!$test_id)
    {
        echo "No test selected.";
        exit();
    }

    $results_query = "
        SELECT 
            u.id AS student_id, -- Include student_id în interogare
            u.name AS student_name, 
            r.score AS total_score, 
            t.total_points
        FROM results r
        INNER JOIN users u ON r.student_id = u.id
        INNER JOIN tests t ON r.test_id = t.test_id
        WHERE r.test_id = '$test_id'
        ORDER BY r.score DESC
    ";

    $results_result = mysqli_query($conn, $results_query);
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/test.css">
        <title>Test Results</title>
    </head>

    <body>
        <?php
            include 'teacher_header.php';
        ?>

        <div class="student-list" style="display: flex; justify-content: center; align-items: center; margin-bottom:60px;">
            <h3>Test Results</h3>
        </div>

        <table>
            <thead>
                <tr> 
                    <th>Student Name</th>
                    <th>Score (Obtained / Total)</th>
                    <th>View Student Answers</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (mysqli_num_rows($results_result) > 0)
                    {
                        while ($row = mysqli_fetch_assoc($results_result))
                        {
                            $student_name = htmlspecialchars($row['student_name']);
                            $total_score = htmlspecialchars($row['total_score']);
                            $total_points = htmlspecialchars($row['total_points']);
                            echo "
                            <tr>
                                <td>$student_name</td>
                                <td>$total_score / $total_points</td>
                                <td><a href='teacher_view_student_progress.php?test_id=$test_id&student_id=" . $row['student_id'] . "' class='action-btn'>View Answers</a></td>
                            </tr>"; 
                        }
                    }
                    else
                    {
                        echo "<tr><td colspan='2'>No results found for this test.</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </body>
</html>
