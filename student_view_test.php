<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'] ?? null;

    if (!isset($student_id)) {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['test_id'])) {
        $test_id = $_GET['test_id'];

        $test_query = "SELECT * FROM tests WHERE test_id = '$test_id'";
        $test_result = mysqli_query($conn, $test_query);

        if ($test_result && mysqli_num_rows($test_result) > 0) {
            $test = mysqli_fetch_assoc($test_result);

            $question_query = "SELECT * FROM questions WHERE test_id = '$test_id'";
            $question_result = mysqli_query($conn, $question_query);
        } else {
            echo "Test not found or you don't have permission to view it.";
            exit();
        }
    } else {
        echo "No test selected.";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/test.css">
        <title>View Test</title>
    </head>
    <body>

        <?php
            if (isset($student_id)) {
                include 'student_header.php';
            }
        ?>
        
        <h2><?php echo htmlspecialchars($test['test_name']); ?></h2>
        <p>Description: <?php echo htmlspecialchars($test['test_description']); ?></p>
        <p>Total Points: <?php echo htmlspecialchars($test['total_points']); ?></p>
        <p>Due Date: <?php echo htmlspecialchars($test['due_date']); ?></p>

        <h3>Questions:</h3>
        <ul>
            <?php
            if (mysqli_num_rows($question_result) > 0) {
                while ($question = mysqli_fetch_assoc($question_result)) {
                    echo "<li>" . htmlspecialchars($question['question_text']);
                    if ($question['question_type'] == 'multiple_choice') {
                        
                        $option_query = "SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'";
                        $option_result = mysqli_query($conn, $option_query);
                        if (mysqli_num_rows($option_result) > 0) {
                            echo "<ul>";
                            while ($option = mysqli_fetch_assoc($option_result)) {
                                echo "<li>" . htmlspecialchars($option['option_text']) . "</li>";
                            }
                            echo "</ul>";
                        }
                    }
                    echo "</li>";
                }
            } else {
                echo "<p>No questions found for this test.</p>";
            }
            ?>
        </ul>
    </body>
</html>
