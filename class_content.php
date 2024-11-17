<?php
    include 'config.php';
    session_start();

    if (!isset($_SESSION['student_id'])) {
        header('location:login.php');
        exit();
    }

    if (!isset($_GET['class_id'])) {
        echo "Class not specified.";
        exit();
    }

    $class_id = intval($_GET['class_id']);

    $query = "SELECT class_name, teacher, description FROM classes WHERE class_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
    } else {
        echo "Class not found.";
        exit();
    }

    $stmt->close();
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($class['class_name']); ?> - Details</title>
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>
        <?php include 'student_header.php'; ?>

        <section class="dashboard">
            <h1><?php echo htmlspecialchars($class['class_name']); ?></h1>
            <p>Teacher: <?php echo htmlspecialchars($class['teacher']); ?></p>
            <p>Description: <?php echo htmlspecialchars($class['description']); ?></p>
        </section>
    </body>
</html>
