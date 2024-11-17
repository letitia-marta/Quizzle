<?php
    include 'config.php';
    session_start();

    $student_id = $_SESSION['student_id'] ?? null;

    if (!isset($student_id)) {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['class_id'])) {
        $class_id = $_GET['class_id'];
        
    if (isset($student_id)) {
            $query = "
                SELECT c.* 
                FROM classes c 
                INNER JOIN student_classes sc ON c.class_id = sc.class_id 
                WHERE c.class_id = '$class_id' AND sc.user_id = '$student_id'
            ";
            $result = mysqli_query($conn, $query);
        }

        if ($result && mysqli_num_rows($result) > 0) {
            $class = mysqli_fetch_assoc($result);
        } else {
            echo "Class not found or you don't have permission to view it.";
            exit();
        }
    } else {
        echo "No class selected.";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Class Details</title>
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>
        <?php
            if (isset($student_id)) {
                include 'student_header.php';
            }
        ?>

        <section class="class-details">
            <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
            <p>Details about the class go here...</p>
            <?php if (isset($student_id)): ?>
                <p>You are enrolled in this class.</p>
            <?php endif; ?>
        </section>
    </body>
</html>
