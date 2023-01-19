<?php
require '../../config/config.php';
require '../../includes/form_handlers/register_handler.php';
require '../../includes/form_handlers/login_handler.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Pleiades!</title>
    <link rel="stylesheet" href="../../assets/css/register_style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="../../assets/js/register.js"></script>
</head>
<body>

    <?php

    if(isset($_POST['register_button'])) {
        echo '
        <script>
        $(document).ready(function() {
            $(".first").hide();
            $(".second").show();
        });
        </script>
        ';
    }

    ?>

    <div class='wrapper'>
        <div class='login_box'>
            <div class='login_header'>
                <h1>pleiades</h1>
            </div>

            <div class="first">
                <form action="register.php" method="POST">
                    <input type="email" name="log_email" placeholder="Email" value="<?php
                if(isset($_SESSION['log_email'])) {
                    echo $_SESSION['log_email'];
                }
                ?>" required>
                <br>
                <input type="password" name="log_password" placeholder="Password">
                <br>
                <input type="submit" name="login_button" value="Log in"><br>
                <?php 
                    if(in_array("<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>Email or password was incorrect.</span><br>", $error_array)) {
                        echo "<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>Email or password was incorrect.</span><br>"; 
                    }
                    else if (in_array("<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>You were indefinitely banned for breaking the Pleiades rules.</span><br>", $error_array)) {
                        echo "<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>You were indefinitely banned for breaking the Pleiades rules.</span><br>";
                    }
                ?>
                <p>New to Pleiades? <a href="#" id="signup" class="signup" style="color: white;">Sign up!</a></p>
                </form>
                <br>
            </div>
        
            <div class="second">
                <form action="register.php" method="POST">
                    <input type="text" name="reg_fname" placeholder="First Name" value="<?php
                    if(isset($_SESSION['reg_fname'])) {
                        echo $_SESSION['reg_fname'];
                    }
                    ?>" 
                    required><br>
                    <?php if(in_array("Your first name must be between 2 and 25 characters.<br>", $error_array)) echo "Your first name must be between 2 and 25 characters.<br>"; ?>
                    
                    <input type="text" name="reg_lname" placeholder="Last Name" value="<?php
                    if(isset($_SESSION['reg_lname'])) {
                        echo $_SESSION['reg_lname'];
                    }
                    ?>" 
                    required><br>
                    <?php if(in_array("Your last name must be between 2 and 25 characters.<br>", $error_array)) echo "Your last name must be between 2 and 25 characters.<br>"; ?>
                    
                    <input type="email" name="reg_email" placeholder="Email" value="<?php
                    if(isset($_SESSION['reg_email'])) {
                        echo $_SESSION['reg_email'];
                    }
                    ?>" 
                    required><br>
                    <input type="email" name="reg_email2" placeholder="Confirm Email" value="<?php
                    if(isset($_SESSION['reg_email2'])) {
                        echo $_SESSION['reg_email2'];
                    }
                    ?>" 
                    required><br>
                    <?php if(in_array("Email already in use.<br>", $error_array)) echo "Email already in use.<br>";
                        else if(in_array("Invalid format.<br>", $error_array)) echo "Invalid format.<br>"; 
                        else if(in_array("Emails don't match.<br>", $error_array)) echo "Emails don't match.<br>"; ?>
                    
                    <input type="password" name="reg_password" placeholder="Password" value="<?php
                    if(isset($_SESSION['reg_password'])) {
                        echo $_SESSION['reg_password'];
                    }
                    ?>" 
                    required><br>
                    
                    <input type="password" name="reg_password2" placeholder="Confirm Password" value="<?php
                    if(isset($_SESSION['reg_password2'])) {
                        echo $_SESSION['reg_password2'];
                    }
                    ?>" 
                    required><br>
                    
                    <?php if(in_array("Your passwords do not match.<br>", $error_array)) echo "Your passwords do not match.<br>";
                        else if(in_array("Your password can only contain english characters and numbers.<br>", $error_array)) echo "Your password can only contain english characters and numbers.<br>"; 
                        else if(in_array("Your password must be between 5 and 30 characters.<br>", $error_array)) echo "Your password must be between 5 and 30 characters.<br>"; ?>

                <input type="submit" name="register_button" value="Register"><br>
                <p>Already have an account? <a href="#" id="signin" class="signin" style="color: white;">Sing in!</a></p>

                <?php if(in_array("<span style='color: #14C800;'> You are all set! Go ahead and log in!</span><br>", $error_array)) echo "<span style='color: #14C800;'> You are all set! Go ahead and log in!</span><br>"; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>