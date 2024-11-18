<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'];

    if (!isset($admin_id)) {
        header('location:login.php');
        exit;
    }

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
        $class_id = $_POST['class_id'] ?? '';
        $test_name = $_POST['test_name'] ?? '';
        $test_description = $_POST['test_description'] ?? '';
        $due_date = $_POST['due_date'] ?? '';
        $total_points = $_POST['total_points'] ?? 0;

        if (!empty($class_id) && !empty($test_name) && $total_points > 0) {
            $query = "INSERT INTO tests (class_id, teacher_id, test_name, test_description, total_points, due_date) 
                    VALUES ('$class_id', '$admin_id', '$test_name', '$test_description', '$total_points', '$due_date')";

            if (mysqli_query($conn, $query)) {
                $test_id = mysqli_insert_id($conn);

                if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                    foreach ($_POST['questions'] as $index => $question) {
                        $question_text = trim($question['question_text'] ?? '');
                        $question_points = intval($question['question_points'] ?? 0);
                        $question_type = trim($question['question_type'] ?? '');
                        $options = $question['options'] ?? [];
                        $correct_option = $question['correct_option'] ?? null;

                        if (empty($question_text) || $question_points <= 0 || empty($question_type)) {
                            continue;
                        }

                        $current_time = date('Y-m-d H:i:s'); 
                        $query_question = "INSERT INTO questions (test_id, question_text, question_type, points, created_at) 
                                        VALUES ('$test_id', '$question_text', '$question_type', '$question_points', '$current_time')";

                        if (mysqli_query($conn, $query_question)) {
                            $question_id = mysqli_insert_id($conn);

                            if ($question_type === 'multiple_choice' && !empty($options)) {
                                foreach ($options as $option_index => $option) {
                                    $option_text = mysqli_real_escape_string($conn, trim($option));
                                    $is_correct = ($correct_option !== null && intval($correct_option) === $option_index) ? 1 : 0;

                                    if (!empty($option_text)) {
                                        $query_option = "INSERT INTO question_options (question_id, option_text, is_correct) 
                                                        VALUES ('$question_id', '$option_text', '$is_correct')";
                                        mysqli_query($conn, $query_option);
                                    }
                                }
                            }
                        } else {
                            die("Error inserting question: " . mysqli_error($conn));
                        }
                    }
                }
                $message = "Test created successfully!";
            } else {
                $message = "Error inserting test: " . mysqli_error($conn);
            }
        } else {
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
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['class_id'] . "'>" . $row['class_name'] . "</option>";
                    }
                    ?>
                </select>

                <label for="test_name">Test Name:</label>
                <input type="text" id="test_name" name="test_name" required>

                <label for="test_description">Test Description:</label>
                <textarea id="test_description" name="test_description" placeholder="Optional description"></textarea>

                <label for="total_points">Total Points:</label>
                <input type="number" id="total_points" name="total_points" required>

                <label for="due_date">Due Date:</label>
                <input type="datetime-local" id="due_date" name="due_date" required>

                <h3>Add Questions to Test</h3>
                <div id="questions-container">
                </div>

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
            document.getElementById('add-question-button').addEventListener('click', function() {
                var questionHTML = `
                    <div class="question" data-index="${questionIndex}">
                        <label>Question Text:</label>
                        <textarea name="questions[${questionIndex}][question_text]" required></textarea>

                        <label>Points:</label>
                        <input type="number" name="questions[${questionIndex}][question_points]" required>

                        <label>Type:</label>
                        <select name="questions[${questionIndex}][question_type]" required>
                            <option value="multiple_choice">Multiple Options</option>
                        </select>

                        <div class="options-container">
                            <div class="option-item">
                                <input type="text" name="questions[${questionIndex}][options][]" placeholder="Option 1">
                                <label>
                                    <input type="checkbox" name="questions[${questionIndex}][correct_option]" value="0">
                                    Mark as Correct
                                </label>
                            </div>
                            <button type="button" class="add-option-btn" onclick="addOption(${questionIndex})">Add Option</button>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()">Remove Question</button>
                    </div>
                `;
                document.getElementById('questions-container').insertAdjacentHTML('beforeend', questionHTML);
                questionIndex++;
            });

            function addOption(questionIndex) {
                var optionIndex = document.querySelectorAll(`.question[data-index="${questionIndex}"] .option-item`).length;
                var optionHTML = `
                    <div class="option-item">
                        <input type="text" name="questions[${questionIndex}][options][]" placeholder="New Option">
                        <label>
                            <input type="checkbox" name="questions[${questionIndex}][correct_option]" value="${optionIndex}">
                            Mark as Correct
                        </label>
                    </div>
                `;
                var optionsContainer = document.querySelector(`.question[data-index="${questionIndex}"] .options-container`);
                optionsContainer.insertAdjacentHTML('beforeend', optionHTML);
            }
        </script>
    </body>
</html>
