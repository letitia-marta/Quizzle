<?php

include 'config.php';
session_start();

$user_id = $_SESSION['student_id'];

if(!isset($user_id))
{
    header('location:login.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home</title>

     <!--font awsome link-->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

     <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="home">

   <div class="content">
      <h3>If you don’t like to read, you haven’t found the right book.</h3>
      <p>Bibliophile Boutique este sursa ta de lectură.</p>
      <p>
        Aici poti găsi cărți pe placul tuturor.
      </p>
      <a href="about.php" class="white-btn">Vezi mai mult</a>
   </div>


   
    </section>

<section class="products">

   <h1 class="title"  style="margin-top: 80px">Cele mai îndrăgite</h1>
   <br>
        <hr class = "line">

        <br>
        <br>
        <br>



      
   <div class="load-more" style="margin-top: 2rem; text-align:center">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>




<section class="home-contact">

   <div class="content">
      <h3>Ai întrebări?</h3>
      <p>Cum am putea îmbunătăți platforma noastră? </p>
      <p>Opinia ta este importantă pentru noi!</p>
      <a href="contact.php" class="white-btn">Contactează-ne</a>
   </div>

</section>








    <?php include 'footer.php' ?>
    <script src="js/script.js"></script>
</body>
</html>