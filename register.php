<?php
    require 'vendor/autoload.php';
    include 'config.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    function generateSecurityCode()
    {
        return mt_rand(100000, 999999); 
    }

    $email = '';

    if (isset($_POST['submit']))
    {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
        $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
        $user_type = $_POST['user_type'];

        $security_code = generateSecurityCode();

        $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');

        if(mysqli_num_rows($select_user) > 0)
        {
            $message[] = 'user existent!';
        }
        else
        {
            if($pass != $cpass)
            {
                $message[] = 'parola confirmată nu se potrivește!';
            }
            else
            {
                mysqli_query($conn, "INSERT INTO `users` (name, email, password, user_type, token_code) VALUES('$name', '$email', '$cpass', '$user_type', '$security_code')") or die ('query failed');
                $message[] = 'înregistrare cu succes!';

                $mailer = new PHPMailer(true);

                try
                {
                    $mailer->isSMTP();
                    $mailer->Host       = 'smtp.gmail.com';
                    $mailer->SMTPAuth   = true;
                    $mailer->Username   = 'formularcontact1@gmail.com';
                    $mailer->Password   = 'aayg mocl ifyq bnsv';
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mailer->Port       = 587;

                    $mailer->setFrom('formularcontact1@gmail.com', 'Quizzle');
                    $mailer->addAddress($email, $name);

                    $mailer->isHTML(true);
                    $mailer->Subject = 'Cod de securitate pentru inregistrare';
                    $mailer->Body    = "Salut, $name! \n\nCodul tău de securitate este: $security_code";

                    $mailer->send();
                    echo 'Emailul a fost trimis cu succes!';
                }
                catch (Exception $e)
                {
                    echo 'Eroare la trimiterea email-ului: ', $mailer->ErrorInfo;
                }
                header('location: email_validation.php');
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="css/style.css">
    </head>

    <body>
        <?php
            if (isset($message))
            {
                foreach($message as $message)
                    echo '<div class="message">
                            <span>'.$message.'</span>
                            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
                        </div>';
            }
        ?>

        <div class="form-container">
            <form action="" method="post">
                <h3>Register</h3>
                <input type="text" name="name" placeholder="Your name" required class="box">
                <input type="email" name="email" placeholder="Your email" required class="box">
                <input type="password" name="password" placeholder="Password" required class="box">
                <input type="password" name="cpassword" placeholder="Confirm password" required class="box">

                <select name="user_type" id="" class="box">
                    <option value="student">student</option>
                    <option value="teacher">teacher</option>
                </select>
                <input type="submit" name="submit" value="Register" class="btn">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </body>
</html>
