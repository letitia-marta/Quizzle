<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['teacher_id'];

    if (!isset($admin_id)) {
        header('location:login.php');
        exit();
    }

    if (isset($_GET['class_id'])) {
        $class_id = $_GET['class_id'];
        
        $query = "SELECT * FROM classes WHERE class_id = '$class_id' AND teacher = '$admin_id'";
        $result = mysqli_query($conn, $query);
        
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
        <?php include 'teacher_header.php'; ?>

        <section class="class-details">
            <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
            <p>Details about the class go here...</p>
        </section>
    </body>
</html>
