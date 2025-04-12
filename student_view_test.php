<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'] ?? null;

    if (!isset($student_id))
    {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['test_id']))
    {
        $test_id = $_GET['test_id'];

        $test_query = "SELECT * FROM tests WHERE test_id = '$test_id'";
        $test_result = mysqli_query($conn, $test_query);

        if ($test_result && mysqli_num_rows($test_result) > 0)
        {
            $test = mysqli_fetch_assoc($test_result);
            $question_query = "SELECT * FROM questions WHERE test_id = '$test_id'";
            $question_result = mysqli_query($conn, $question_query);
        }
        else
        {
            echo "Test not found or you don't have permission to view it.";
            exit();
        }
    }
    else
    {
        echo "No test selected.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $total_score = 0;

        foreach ($_POST['answers'] as $question_id => $selected_options)
        {
            $selected_options = is_array($selected_options) ? $selected_options : [];
            $question_query = "SELECT * FROM questions WHERE question_id = '$question_id'";
            $question_result = mysqli_query($conn, $question_query);

            if ($question_result && mysqli_num_rows($question_result) > 0)
            {
                $question = mysqli_fetch_assoc($question_result);
                $points_per_question = $question['points'];

                $option_query = "SELECT * FROM question_options WHERE question_id = '$question_id'";
                $option_result = mysqli_query($conn, $option_query);

                $correct_options = [];
                if ($option_result && mysqli_num_rows($option_result) > 0)
                {
                    while ($option = mysqli_fetch_assoc($option_result))
                    {
                        if ($option['is_correct'])
                        {
                            $correct_options[] = $option['option_id'];
                        }
                    }

                    $is_correct_answer = empty(array_diff($selected_options, $correct_options)) && empty(array_diff($correct_options, $selected_options));

                    if ($is_correct_answer)
                    {
                        $total_score += $points_per_question;
                    }
                }

                if (is_array($selected_options))
                {
                    foreach ($selected_options as $option_id)
                    {
                        $is_selected = 1;
                        $insert_query = "INSERT INTO answers (student_id, answer_choice, test_id, question_id, is_selected, submitted_at) 
                                        VALUES ('$student_id', '$option_id', '$test_id', '$question_id', '$is_selected', NOW())";
                        mysqli_query($conn, $insert_query);
                    }
                }
            }
        }

        echo json_encode([
            'success' => true,
            'redirect_url' => "student_test_submission.php?test_id=$test_id&score=$total_score"
        ]);
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
        <style>
            .test-view li:hover {
                transform: none !important;
            }
        </style>
    </head>

    <body>
        <?php if (isset($student_id)) { include 'student_header.php'; } ?>

        <div class="test-view">
            <h2><?php echo htmlspecialchars($test['test_name']); ?></h2>
            <p>Description: <?php echo htmlspecialchars($test['test_description']); ?></p>
            <p>Total Points: <?php echo htmlspecialchars($test['total_points']); ?></p>
            <p>Due Date: <?php echo htmlspecialchars($test['due_date']); ?></p>

            <h3>Questions:</h3>
            <form id="testForm" method="POST">
                <ul>
                    <?php
                    if (mysqli_num_rows($question_result) > 0)
                    {
                        while ($question = mysqli_fetch_assoc($question_result))
                        {
                            echo "<li>";
                            echo "<b><p>" . htmlspecialchars($question['question_text']) . "</p></b>";

                            $option_query = "SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'";
                            $option_result = mysqli_query($conn, $option_query);

                            if (mysqli_num_rows($option_result) > 0)
                            {
                                echo "<ul>";
                                while ($option = mysqli_fetch_assoc($option_result))
                                {
                                    echo "<li>";
                                    echo "<label>";
                                    echo "<input type='checkbox' name='answers[{$question['question_id']}][]' value='{$option['option_id']}' />";
                                    echo htmlspecialchars($option['option_text']);
                                    echo "</label>";
                                    echo "</li>";
                                }
                                echo "</ul>";
                            }
                            echo "</li>";
                        }
                    }
                    else
                    {
                        echo "<p class='error-message'>No questions found for this test.</p>";
                    }
                    ?>
                </ul>
                <div class="rezolvare">
                    <button type="submit" class="submit-btn" style="margin-top: -3px; margin-right: 30px">Submit Test</button>
                    <button type="button" class="submit-btn" style="margin-top: -1px; margin-right: 205px" onclick="clearAnswers()">Clear Test</button>
                </div>
            </form>
        </div>

        <script>
            function clearAnswers()
            {
                const form = document.getElementById('testForm');
                form.reset();
            }

            document.getElementById('testForm').addEventListener('submit', function(event)
            {
                event.preventDefault();

                const formData = new FormData(this);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.href = result.redirect_url;
                    } else {
                        alert('There was an error submitting the test.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('There was an error submitting the test.');
                });
            });
        </script>
    </body>
</html>
