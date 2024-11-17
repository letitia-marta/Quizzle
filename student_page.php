<?php
    include 'config.php';
    session_start();

    $admin_id = $_SESSION['student_id'];

    if (!isset($admin_id))
    {
        header('location:login.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quizzle - student</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="css/test.css">
    </head>
    <body>
        <?php include 'student_header.php' ?>

        <h1 class="title">Welcome!</h1>

        <section class="dashboard">
            <div class="button-container">
                <a href="student_classes.php" class="dashboard-button button-with-image">
                    <img src="images/cursuri.png" alt="Cursuri" class="button-image">
                    <span>Classes</span>
                </a>
                <a href="stats.php" class="dashboard-button button-with-image">
                    <img src="images/raport.png" alt="Rapoarte" class="button-image">
                    <span>Statistics</span>
                </a>
            </div>
        </section>

        <a href="enroll_class.php" class="plus">+<span class="tooltip-text">Join a class</span></a>

        <script src="js/admin_script.js"></script>
    </body>
</html>