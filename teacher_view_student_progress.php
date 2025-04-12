<?php
    include 'config.php';
    session_start();

    $teacher_id = $_SESSION['teacher_id'] ?? null;

    if (!isset($teacher_id))
    {
        header('location:login.php');
        exit();
    }

    $test_id = $_GET['test_id'] ?? null;
    $student_id = $_GET['student_id'] ?? null;

    if (!$test_id || !$student_id)
    {
        echo "Test or student not selected.";
        exit();
    }

    $test_query = "SELECT * FROM tests WHERE test_id = '$test_id'";
    $test_result = mysqli_query($conn, $test_query);

    $student_name_query = "SELECT name FROM users WHERE id = '$student_id'";
    $student_name_result = mysqli_query($conn, $student_name_query);

    if ($student_name_result && mysqli_num_rows($student_name_result) > 0)
    {
        $student_name = mysqli_fetch_assoc($student_name_result)['name'];
    }
    else
    {
        $student_name = "Unknown Student";
    }

    if ($test_result && mysqli_num_rows($test_result) > 0)
    {
        $test = mysqli_fetch_assoc($test_result);

        $options_query = "
            SELECT q.question_text, o.option_text, o.is_correct, a.answer_choice, o.option_id, q.points AS question_points, q.question_id,
                (SELECT COUNT(*) FROM question_options o2 WHERE o2.question_id = q.question_id AND o2.is_correct = 1) AS correct_options_count
            FROM questions q
            LEFT JOIN question_options o ON q.question_id = o.question_id
            LEFT JOIN answers a ON a.answer_choice = o.option_id AND a.student_id = '$student_id' AND a.test_id = '$test_id'
            WHERE q.test_id = '$test_id'
            ORDER BY q.question_id, o.option_id
        ";

        $options_result = mysqli_query($conn, $options_query);
    }
    else
    {
        echo "Test not found.";
        exit();
    }

    $total_score = 0;
    $total_points = (int)$test['total_points'];

    $current_question = null;
    $options_html = "";
    $questions_data = [];

    while ($option = mysqli_fetch_assoc($options_result))
    {
        if ($current_question !== $option['question_text'])
        {
            if ($current_question !== null)
            {
                if ($question_total_points < 0)
                {
                    $question_total_points = 0;
                }
                $total_score += $question_total_points;

                $questions_data[] = [
                    'question' => $current_question,
                    'options_html' => $options_html,
                    'question_total_points' => $question_total_points,
                    'question_points' => $question_points
                ];
            }

            $current_question = $option['question_text'];
            $options_html = "";
            $question_total_points = 0;
            $question_points = $option['question_points'];
            $correct_options_count = $option['correct_options_count'];
            $points_per_correct_option = $question_points / $correct_options_count;
            $penalty_per_wrong_option = $points_per_correct_option / 2;
        }

        $class = '';
        $points = 0;

        if ($option['answer_choice'] == $option['option_id'])
        {
            if ($option['is_correct'])
            {
                $points = $points_per_correct_option;
            }
            else
            {
                $points = -$penalty_per_wrong_option;
            }
        }

        $class .= $option['is_correct'] ? ' correct' : ' incorrect';
        $class .= $option['answer_choice'] == $option['option_id'] ? ' selected' : '';

        $options_html .= "<li class='$class'>
            <span class='option-text'>" . htmlspecialchars($option['option_text']) . "</span>
            <input type='checkbox' " . ($option['answer_choice'] == $option['option_id'] ? "checked" : "") . " disabled>
            <span class='points'>" . number_format($points, 2) . " points</span>
        </li>";

        $question_total_points += $points;
    }

    if ($current_question !== null)
    {
        if ($question_total_points < 0)
        {
            $question_total_points = 0;
        }
        $total_score += $question_total_points;

        $questions_data[] = [
            'question' => $current_question,
            'options_html' => $options_html,
            'question_total_points' => $question_total_points,
            'question_points' => $question_points
        ];
    }

    $percentage = ($total_score > 0) ? ($total_score / $total_points) * 100 : 0;

    if ($percentage >= 100)
    {
        $badge = "<img src='images/star.png' alt='Star Badge' class='badge-image'>";
    }
    elseif ($percentage >= 90)
    {
        $badge = "<img src='images/star_face.png' alt='Star Badge' class='badge-image'>";
    }
    elseif ($percentage >= 70)
    {
        $badge = "<img src='images/like.png' alt='Like Badge' class='badge-image'>";
    }
    elseif ($percentage >= 50)
    {
        $badge = "<img src='images/neutral_face.png' alt='Neutral Badge' class='badge-image'>";
    }
    else
    {
        $badge = "<img src='images/very_bad.png' alt='Very Bad Badge' class='badge-image'>";
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/test.css">
        <title>Student Progress</title>
        <style>
            .question-card {
                border: 1px solid #ccc;
                border-radius: 8px;
                margin: 20px 0;
                padding: 15px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                background-color: #f9f9f9;
            }

            .badge-container {
                text-align: center;
                margin: 20px 0;
            }

            .badge-image {
                width: 100px;
                height: auto;
            }

            .question-card h3 {
                margin-top: 0;
            }

            .options-list {
                list-style: none;
                padding: 0;
                display: flex;
                flex-direction: column;
            }

            .options-list li {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 5px 0;
            }

            .option-text {
                flex: 2;
                font-size: 1.2em;
                text-align: left;
            }

            .selected-text {
                flex: 1;
                text-align: center;
                font-weight: bold;
                font-size: 1em;
                color: #666;
            }

            .points {
                flex: 1;
                text-align: right;
                font-size: 1em;
                color: #555;
            }

            .options-list li.correct {
                color: green;
                font-weight: bold;
            }

            .options-list li.incorrect {
                color: red;
            }

            .selected {
                font-weight: bold;
                font-style: italic;
                color: blue;
            }
        </style>
    </head>

    <body>
        <?php
            include 'teacher_header.php';
        ?>

        <div class="test-view">
            <div class="badge-container">
                <?php echo $badge; ?>
            </div>

            <h2><?php echo htmlspecialchars($test['test_name']); ?> - <?php echo htmlspecialchars($student_name); ?>'s Answers</h2>
            <p>Description: <?php echo htmlspecialchars($test['test_description']); ?></p>
            <p>Total Points: <?php echo htmlspecialchars($test['total_points']); ?></p>
            <p>Student's Score: <?php echo $total_score; ?> / <?php echo htmlspecialchars($test['total_points']); ?></p>

            <h3>Answers:</h3>

            <?php
                foreach ($questions_data as $data)
                {
                    echo "<div class='question-card'>";
                    echo "<h3>" . htmlspecialchars($data['question']) . " - Points: " . htmlspecialchars($data['question_total_points'] . " / " . $data['question_points']) . "</h3>";
                    echo "<ul class='options-list'>" . $data['options_html'] . "</ul>";
                    echo "</div>";
                }
            ?>
        </div>
    </body>
</html>
