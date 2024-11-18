<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['teacher_id'] ?? null;
if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

if (!isset($_GET['test_id'])) {
    echo "No test selected.";
    exit();
}

$test_id = $_GET['test_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $test_name = mysqli_real_escape_string($conn, $_POST['test_name']);
    $test_description = mysqli_real_escape_string($conn, $_POST['test_description']);
    $total_points = intval($_POST['total_points']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

    $update_test_query = "UPDATE tests SET 
        test_name = '$test_name',
        test_description = '$test_description',
        total_points = $total_points,
        due_date = '$due_date'
        WHERE test_id = '$test_id' AND teacher_id = '$admin_id'";
    mysqli_query($conn, $update_test_query);

    if (!empty($_POST['questions'])) {
        foreach ($_POST['questions'] as $question_id => $question_data) {
            $question_text = mysqli_real_escape_string($conn, $question_data['text']);
            $points = intval($question_data['points']);
            $question_type = mysqli_real_escape_string($conn, $question_data['type']);

            $update_question_query = "UPDATE questions SET 
                question_text = '$question_text',
                points = $points,
                question_type = '$question_type'
                WHERE question_id = '$question_id'";
            mysqli_query($conn, $update_question_query);

            if ($question_type === 'multiple_choice' && !empty($question_data['options'])) {
                foreach ($question_data['options'] as $option_id => $option_data) {
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

    if (!empty($_POST['new_questions'])) {
        foreach ($_POST['new_questions'] as $new_question) {
            $new_question_text = mysqli_real_escape_string($conn, $new_question['text']);
            $new_points = intval($new_question['points']);
            $new_type = mysqli_real_escape_string($conn, $new_question['type']);

            $insert_question_query = "INSERT INTO questions (test_id, question_text, points, question_type) 
                VALUES ('$test_id', '$new_question_text', $new_points, '$new_type')";
            mysqli_query($conn, $insert_question_query);
            $new_question_id = mysqli_insert_id($conn);

            if ($new_type === 'multiple_choice' && !empty($new_question['options'])) {
                foreach ($new_question['options'] as $new_option) {
                    $new_option_text = mysqli_real_escape_string($conn, $new_option['text']);
                    $new_is_correct = isset($new_option['is_correct']) ? 1 : 0;

                    $insert_option_query = "INSERT INTO question_options (question_id, option_text, is_correct) 
                        VALUES ('$new_question_id', '$new_option_text', '$new_is_correct')";
                    mysqli_query($conn, $insert_option_query);
                }
            }
        }
    }

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
while ($question = mysqli_fetch_assoc($question_result)) {
    $questions[$question['question_id']] = $question;

    if ($question['question_type'] === 'multiple_choice') {
        $option_query = "SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'";
        $option_result = mysqli_query($conn, $option_query);
        while ($option = mysqli_fetch_assoc($option_result)) {
            $options[$question['question_id']][] = $option;
        }
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

        function addNewQuestion() {
            const newQuestionContainer = document.getElementById('new-questions-container');
            const newQuestionHTML = `
                <div class="new-question-block">
                    <label>Question Text:</label>
                    <input type="text" name="new_questions[${questionCounter}][text]" required>

                    <label>Points:</label>
                    <input type="number" name="new_questions[${questionCounter}][points]" required>

                    <label>Type:</label>
                    <select name="new_questions[${questionCounter}][type]">
                        <option value="multiple_choice">Multiple Options</option>
                    </select>

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


        function addOption(questionIndex) {
            const optionsContainer = document.getElementById(`options-container-${questionIndex}`);
            const optionCount = optionsContainer.querySelectorAll('.option-item').length;
            const newOptionHTML = `
                <div class="option-item">
                    <input type="text" name="new_questions[${questionIndex}][options][${optionCount}][text]" placeholder="Option Text" required>
                    <label>
                        <input type="checkbox" name="new_questions[${questionIndex}][options][${optionCount}][is_correct]">
                        Correct
                    </label>
                </div>
            `;
            optionsContainer.insertAdjacentHTML('beforeend', newOptionHTML);
        }

        function confirmSave() {
            return confirm("Are you sure you want to save the changes?");
        }

    </script>
</head>
<body>
    <?php include 'teacher_header.php'; ?>

    <div class="create-test-container">
        <h2>View Test</h2>

        <form method="POST" onsubmit="return confirmSave('Are you sure you want to save changes?')">

            <label for="class_id">Class:</label>
            <select name="class_id" id="class_id" disabled>
                <?php
                $class_query = "SELECT class_id, class_name FROM classes WHERE teacher = '$admin_id'";
                $class_result = mysqli_query($conn, $class_query);
                while ($row = mysqli_fetch_assoc($class_result)) {
                    $selected = ($row['class_id'] === $test['class_id']) ? 'selected' : '';
                    echo "<option value='" . $row['class_id'] . "' $selected>" . $row['class_name'] . "</option>";
                }
                ?>
            </select>

            <label for="test_name">Test Name:</label>
            <input type="text" name="test_name" value="<?= $test['test_name'] ?>" required>

            <label for="test_description">Test Description:</label>
            <textarea name="test_description"><?= $test['test_description'] ?></textarea>

            <label for="total_points">Total Points:</label>
            <input type="number" name="total_points" value="<?= $test['total_points'] ?>" required>

            <label for="due_date">Due Date:</label>
            <input type="datetime-local" name="due_date" value="<?= $test['due_date'] ?>" required>

            <h3>Questions</h3>
            <div id="questions-container">
                <?php foreach ($questions as $question_id => $question): ?>
                    <div>
                        <input type="hidden" name="questions[<?= $question_id ?>][id]" value="<?= $question_id ?>">

                        <label>Question Text:</label>
                        <input type="text" name="questions[<?= $question_id ?>][text]" value="<?= $question['question_text'] ?>" required>

                        <label>Points:</label>
                        <input type="number" name="questions[<?= $question_id ?>][points]" value="<?= $question['points'] ?>" required>

                        <label>Type:</label>
                        <select name="questions[<?= $question_id ?>][type]">
                            <option value="multiple_choice" <?= $question['question_type'] == 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                        </select>

                        <?php if (!empty($options[$question_id])): ?>
                            <h4>Options:</h4>
                            <?php foreach ($options[$question_id] as $option): ?>
                                <div>
                                    <input type="text" name="questions[<?= $question_id ?>][options][<?= $option['option_id'] ?>][text]" value="<?= $option['option_text'] ?>" required>
                                    <label>
                                        <input type="checkbox" name="questions[<?= $question_id ?>][options][<?= $option['option_id'] ?>][is_correct]" <?= $option['is_correct'] ? 'checked' : '' ?>>
                                        Correct
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="new-questions-container"></div>
            <button type="button" onclick="addNewQuestion()" id="add-question-button">+</button>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
