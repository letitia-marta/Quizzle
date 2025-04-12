<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'];

    if (!isset($admin_id))
    {
        header('location:login.php');
        exit;
    }

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test']))
    {
        $class_id = $_POST['class_id'] ?? '';
        $test_name = $_POST['test_name'] ?? '';
        $test_description = $_POST['test_description'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (!empty($class_id) && !empty($test_name))
        {
            $query = "INSERT INTO tests (class_id, teacher_id, test_name, test_description, total_points, due_date) 
                    VALUES ('$class_id', '$admin_id', '$test_name', '$test_description', 0, '$due_date')";

            if (mysqli_query($conn, $query))
            {
                $test_id = mysqli_insert_id($conn);
                $total_points = 0;

                if (isset($_POST['questions']) && is_array($_POST['questions']))
                {
                    foreach ($_POST['questions'] as $index => $question)
                    {
                        $question_text = trim($question['question_text'] ?? '');
                        $question_points = intval($question['question_points'] ?? 0);
                        $options = $question['options'] ?? [];
                        $correct_option = $question['correct_option'] ?? null;

                        if (empty($question_text) || $question_points <= 0)
                        {
                            continue;
                        }

                        $current_time = date('Y-m-d H:i:s'); 
                        $query_question = "INSERT INTO questions (test_id, question_text, points, created_at) 
                                        VALUES ('$test_id', '$question_text', '$question_points', '$current_time')";

                        if (mysqli_query($conn, $query_question))
                        {
                            $question_id = mysqli_insert_id($conn);
                            $total_points += $question_points;

                            $correct_options = is_array($question['correct_option']) ? $question['correct_option'] : explode(',', $question['correct_option']);

                            if (!empty($options))
                            {
                                foreach ($options as $option_index => $option)
                                {
                                    $option_text = mysqli_real_escape_string($conn, trim($option));
                                    $is_correct = in_array($option_index, array_map('intval', $correct_options)) ? 1 : 0;

                                    if (!empty($option_text))
                                    {
                                        $query_option = "INSERT INTO question_options (question_id, option_text, is_correct) 
                                                        VALUES ('$question_id', '$option_text', '$is_correct')";
                                        mysqli_query($conn, $query_option);
                                    }
                                }
                            }
                        }
                        else
                        {
                            die("Error inserting question: " . mysqli_error($conn));
                        }
                    }

                    $update_query = "UPDATE tests SET total_points = '$total_points' WHERE test_id = '$test_id'";
                    mysqli_query($conn, $update_query);
                }
                $message = "Test created successfully!";
            }
            else
            {
                $message = "Error inserting test: " . mysqli_error($conn);
            }
        }
        else
        {
            $message = "Please complete all required fields!";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quizzle - New Test</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
        <link rel="stylesheet" href="css/test.css">
    </head>
    
    <body>
        <?php include 'teacher_header.php'; ?>

        <div class="create-test-container">
            <h2>Create a New Test</h2>

            <form method="POST" action="">
                <label for="class_id">Select Class:</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Select a Class</option>
                    <?php
                        $query = "SELECT class_id, class_name FROM classes WHERE teacher = '$admin_id'";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            echo "<option value='" . $row['class_id'] . "'>" . $row['class_name'] . "</option>";
                        }
                    ?>
                </select>

                <label for="test_name">Test Name:</label>
                <input type="text" id="test_name" name="test_name" required>

                <label for="test_description">Test Description:</label>
                <textarea id="test_description" name="test_description" placeholder="Optional description"></textarea>

                <label for="due_date">Due Date:</label>
                <input type="datetime-local" id="due_date" name="due_date" required>

                <h3>Add Questions to Test</h3>
                <div id="questions-container"></div>

                <button type="button" id="add-question-button">+</button>
                <button type="submit" name="create_test">Create Test</button>
            </form>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>" id="form-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            let questionIndex = 0;
            document.getElementById('add-question-button').addEventListener('click', function()
            {
                const questionHTML = `
                    <div class="question" data-index="${questionIndex}">
                        <label>Question Text:</label>
                        <textarea name="questions[${questionIndex}][question_text]" required></textarea>

                        <label>Points:</label>
                        <input type="number" name="questions[${questionIndex}][question_points]" required>

                        <div class="options-container">
                            <div class="option-item">
                                <input type="text" name="questions[${questionIndex}][options][]" placeholder="Option 1">
                                <label>
                                    <input type="checkbox" name="questions[${questionIndex}][correct_option][]" value="0">
                                    Mark as Correct
                                </label>
                                <div class="remove-option-container">
                                    <button type="button" class="remove-option-btn">Remove Option</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="add-option-btn" onclick="addOption(${questionIndex})">Add Option</button>
                        <br>
                        <button type="button" class="remove-question-btn">Remove Question</button>
                    </div>
                `;
                document.getElementById('questions-container').insertAdjacentHTML('beforeend', questionHTML);
                questionIndex++;
            });

            function addOption(questionIndex)
            {
                const optionIndex = document.querySelectorAll(`.question[data-index="${questionIndex}"] .option-item`).length;
                const optionHTML = `
                    <div class="option-item">
                        <input type="text" name="questions[${questionIndex}][options][]" placeholder="New Option">
                        <label>
                            <input type="checkbox" name="questions[${questionIndex}][correct_option][]" value="${optionIndex}">
                            Mark as Correct
                        </label>
                        <div class="remove-option-container">
                            <button type="button" class="remove-option-btn">Remove Option</button>
                        </div>
                    </div>
                `;
                document.querySelector(`.question[data-index="${questionIndex}"] .options-container`).insertAdjacentHTML('beforeend', optionHTML);
            }

            document.getElementById('questions-container').addEventListener('click', function(event)
            {
                if (event.target.classList.contains('remove-option-btn'))
                {
                    event.target.closest('.option-item').remove();
                }
                else if (event.target.classList.contains('remove-question-btn'))
                {
                    event.target.closest('.question').remove();
                }
            });
        </script>
    </body>
</html>
