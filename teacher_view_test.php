<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'] ?? null;
    if (!isset($admin_id))
    {
        header('location:login.php');
        exit();
    }

    if (!isset($_GET['test_id']))
    {
        echo "No test selected.";
        exit();
    }

    $test_id = $_GET['test_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $test_name = mysqli_real_escape_string($conn, $_POST['test_name']);
        $test_description = mysqli_real_escape_string($conn, $_POST['test_description']);
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

        $update_test_query = "UPDATE tests SET 
            test_name = '$test_name',
            test_description = '$test_description',
            due_date = '$due_date'
            WHERE test_id = '$test_id' AND teacher_id = '$admin_id'";
        mysqli_query($conn, $update_test_query);

        $total_points = 0;

        if (!empty($_POST['questions']))
        {
            foreach ($_POST['questions'] as $question_id => $question_data)
            {
                if (isset($question_data['delete']))
                {
                    $delete_question_query = "DELETE FROM questions WHERE question_id = '$question_id'";
                    mysqli_query($conn, $delete_question_query);
                    continue;
                }

                $question_text = mysqli_real_escape_string($conn, $question_data['text']);
                $points = intval($question_data['points']);
                $total_points += $points;

                $update_question_query = "UPDATE questions SET 
                    question_text = '$question_text',
                    points = $points
                    WHERE question_id = '$question_id'";
                mysqli_query($conn, $update_question_query);

                if (!empty($question_data['options']))
                {
                    foreach ($question_data['options'] as $option_id => $option_data)
                    {
                        if (strpos($option_id, 'new_') === 0)
                        {
                            $option_text = mysqli_real_escape_string($conn, $option_data['text']);
                            $is_correct = isset($option_data['is_correct']) ? 1 : 0;

                            $insert_option_query = "INSERT INTO question_options (question_id, option_text, is_correct) 
                                VALUES ('$question_id', '$option_text', $is_correct)";
                            mysqli_query($conn, $insert_option_query);
                        }
                        else
                        {
                            $option_text = mysqli_real_escape_string($conn, $option_data['text']);
                            $is_correct = isset($option_data['is_correct']) ? 1 : 0;

                            $update_option_query = "UPDATE question_options SET 
                                option_text = '$option_text',
                                is_correct = $is_correct
                                WHERE option_id = '$option_id'";
                            mysqli_query($conn, $update_option_query);
                        }
                    }
                }
            }
        }

        if (!empty($_POST['new_questions']))
        {
            foreach ($_POST['new_questions'] as $new_question)
            {
                $new_question_text = mysqli_real_escape_string($conn, $new_question['text']);
                $new_points = intval($new_question['points']);
                $total_points += $new_points;

                $insert_question_query = "INSERT INTO questions (test_id, question_text, points) 
                    VALUES ('$test_id', '$new_question_text', $new_points)";
                mysqli_query($conn, $insert_question_query);

                $new_question_id = mysqli_insert_id($conn);

                if (!empty($new_question['options']))
                {
                    foreach ($new_question['options'] as $new_option)
                    {
                        $new_option_text = mysqli_real_escape_string($conn, $new_option['text']);
                        $new_is_correct = isset($new_option['is_correct']) ? 1 : 0;

                        $insert_option_query = "INSERT INTO question_options (question_id, option_text, is_correct) 
                            VALUES ('$new_question_id', '$new_option_text', '$new_is_correct')";
                        mysqli_query($conn, $insert_option_query);
                    }
                }
            }
        }

        $update_total_query = "UPDATE tests SET total_points = $total_points WHERE test_id = '$test_id'";
        mysqli_query($conn, $update_total_query);

        header("Location: teacher_view_test.php?test_id=$test_id");
        exit();
    }

    $test_query = "SELECT * FROM tests WHERE test_id = '$test_id' AND teacher_id = '$admin_id'";
    $test_result = mysqli_query($conn, $test_query);
    $test = mysqli_fetch_assoc($test_result);

    $questions = [];
    $options = [];
    $question_query = "SELECT * FROM questions WHERE test_id = '$test_id'";
    $question_result = mysqli_query($conn, $question_query);
    while ($question = mysqli_fetch_assoc($question_result))
    {
        $questions[$question['question_id']] = $question;
        $option_query = "SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'";
        $option_result = mysqli_query($conn, $option_query);
        while ($option = mysqli_fetch_assoc($option_result))
        {
            $options[$question['question_id']][] = $option;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>View Test</title>
        <link rel="stylesheet" href="css/test.css">
        <script>
            let questionCounter = <?php echo count($questions); ?>;

            function addNewQuestion()
            {
                const newQuestionContainer = document.getElementById('new-questions-container');
                const newQuestionHTML = ` 
                    <div class="new-question-block">
                        <label>Question Text:</label>
                        <input type="text" name="new_questions[${questionCounter}][text]" required>

                        <label>Points:</label>
                        <input type="number" name="new_questions[${questionCounter}][points]" required>

                        <div class="options-container" id="options-container-${questionCounter}">
                            <h4>Options:</h4>
                            <div class="option-item">
                                <input type="text" name="new_questions[${questionCounter}][options][0][text]" placeholder="Option Text" required>
                                <label>
                                    <input type="checkbox" name="new_questions[${questionCounter}][options][0][is_correct]">
                                    Correct
                                </label>
                            </div>
                        </div>
                        <button type="button" onclick="addOption(${questionCounter})">Add Option</button>
                    </div>
                `;
                newQuestionContainer.insertAdjacentHTML('beforeend', newQuestionHTML);
                questionCounter++;
            }

            function addOption(questionIndex)
            {
                const optionsContainer = document.getElementById(`options-container-${questionIndex}`);
                const optionCount = optionsContainer.querySelectorAll('.option-item').length;
                const newOptionHTML = `
                    <div class="option-item">
                        <input type="text" name="new_questions[${questionIndex}][options][${optionCount}][text]" placeholder="Option Text" required>
                        <label>
                            <input type="checkbox" name="new_questions[${questionIndex}][options][${optionCount}][is_correct]">
                            Correct
                        </label>
                        <div class="remove-option-container">
                            <button type="button" onclick="removeOption(this)">Remove Option</button>
                        </div>
                    </div>
                `;
                optionsContainer.insertAdjacentHTML('beforeend', newOptionHTML);
            }

            function addOptionForExistingQuestion(questionId)
            {
                const optionsContainer = document.getElementById(`existing-options-container-${questionId}`);
                const optionCount = optionsContainer.querySelectorAll('.option-item').length;
                const newOptionHTML = `
                    <div class="option-item">
                        <input type="text" name="questions[${questionId}][options][new_${optionCount}][text]" placeholder="Option Text" required>
                        <label>
                            <input type="checkbox" name="questions[${questionId}][options][new_${optionCount}][is_correct]">
                            Correct
                        </label>
                        <div class="remove-option-container">
                            <button type="button" onclick="removeOption(this)">Remove Option</button>
                        </div>
                    </div>
                `;
                optionsContainer.insertAdjacentHTML('beforeend', newOptionHTML);
            }

            function removeOption(button)
            {
                const optionItem = button.closest('.option-item');
                optionItem.remove();
            }
        </script>
    </head>

    <body>
        <?php
            include 'teacher_header.php';
        ?>

        <div class="create-test-container">
            <h2>View Test</h2>

            <form method="POST">
                <label for="test_name">Test Name:</label>
                <input type="text" name="test_name" value="<?= $test['test_name'] ?>" required>

                <label for="test_description">Test Description:</label>
                <textarea name="test_description"><?= $test['test_description'] ?></textarea>

                <label for="total_points">Total Points:</label>
                <input type="number" name="total_points" value="<?= $test['total_points'] ?>" disabled>

                <label for="due_date">Due Date:</label>
                <input type="datetime-local" name="due_date" value="<?= $test['due_date'] ?>" required>

                <h3>Questions</h3>
                <div id="questions-container">
                    <?php foreach ($questions as $question_id => $question): ?>
                        <div>
                            <label>Question Text:</label>
                            <input type="text" name="questions[<?= $question_id ?>][text]" value="<?= $question['question_text'] ?>" required>

                            <label>Points:</label>
                            <input type="number" name="questions[<?= $question_id ?>][points]" value="<?= $question['points'] ?>" required>

                            <div id="existing-options-container-<?= $question_id ?>">
                                <h4>Options:</h4>
                                <?php if (!empty($options[$question_id])): ?>
                                    <?php foreach ($options[$question_id] as $option): ?>
                                        <div class="option-item">
                                            <input type="text" name="questions[<?= $question_id ?>][options][<?= $option['option_id'] ?>][text]" value="<?= $option['option_text'] ?>" required>
                                            <label>
                                                <input type="checkbox" name="questions[<?= $question_id ?>][options][<?= $option['option_id'] ?>][is_correct]" <?= $option['is_correct'] ? 'checked' : '' ?>>
                                                Correct
                                            </label>
                                            <div class="remove-option-container">
                                                <button type="button" onclick="removeOption(this)">Remove Option</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" onclick="addOptionForExistingQuestion(<?= $question_id ?>)">Add Option</button>
                            <br>
                            <button type="button" onclick="this.parentElement.remove()">Remove Question</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="new-questions-container"></div>
                <button type="button" onclick="addNewQuestion()">Add Question</button>

                <button type="submit">Save</button>
            </form>
        </div>
    </body>
</html>
