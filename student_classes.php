<?php
    include 'config.php';
    session_start();

    $user_id = $_SESSION['student_id'];
    if (!isset($user_id)) {
        header('location:login.php');
        exit();
    }

    $query = "SELECT c.class_name, u.name AS teacher_name 
            FROM classes c 
            JOIN student_classes sc ON c.class_id = sc.class_id 
            JOIN users u ON c.teacher = u.id 
            WHERE sc.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $classes1 = [];
        while ($row = $result->fetch_assoc()) {
            $classes1[] = $row;
        }
    } else {
        echo "Error executing query: " . $conn->error;
    }

    $stmt->close();
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Classes</title>
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>

        <?php include 'student_header.php'; ?>

        <section class="dashboard">
            <h1 class="title">My Classes</h1>

            <?php if (!empty($message)) { ?>
                <h2><?php echo $message; ?></h2>
            <?php } else { ?>
                <div class="class-grid">
                    <?php foreach ($classes1 as $class) { ?>
                        <div class="class-card">
                            <h3><?php echo $class['class_name']; ?></h3>
                            <p>Teacher: <?php echo isset($class['teacher_name']) ? $class['teacher_name'] : 'No teacher assigned'; ?></p>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>

        <a href="" class="plus">+<span class="tooltip-text">Join a class</span></a>

    </body>
</html>