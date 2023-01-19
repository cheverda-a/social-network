<?php
require  $_SERVER['DOCUMENT_ROOT'] . '/course_wcrk/config/config.php';
include($_SERVER['DOCUMENT_ROOT'] . "/course_wcrk/includes/classes/User.php");
include($_SERVER['DOCUMENT_ROOT'] . "/course_wcrk/includes/classes/Post.php");
include($_SERVER['DOCUMENT_ROOT'] . "/course_wcrk/includes/classes/Message.php");
include($_SERVER['DOCUMENT_ROOT'] . "/course_wcrk/includes/classes/Notification.php");

if(isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);
}
else {
    header("Location: register.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pleiades: Admin</title>

    	<!-- Javascript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
        <script src="../../assets/js/rumblr.js"></script>
        <script src="../../assets/js/jquery.Jcrop.js"></script>
        <script src="../../assets/js/jcrop_bits.js"></script>

        <link rel="stylesheet" href="https://unpkg.com/jcrop/dist/jcrop.css">

        <!-- CSS -->
        <script src="https://kit.fontawesome.com/df25ac4c5a.js" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

        <link rel="stylesheet" href="../../assets/css/jquery.Jcrop.css" type="text/css" />
        <link rel="stylesheet" type="text/css" href="../../assets/css/style.css">

    <style>
        @font-face {
        font-family: "AlongSanss2-BoldItalic";
        src: url("assets/fonts/AlongSanss2-BoldItalic.otf");
        }

        @font-face {
        font-family: "Helvetica";
        src: url("assets/fonts/Helvetica.ttf");
        }

        @font-face {
        font-family: "HelveticaNeueCyr-Light";
        src: url("assets/fonts/HelveticaNeueCyr-Light.ttf");
        }

        body {
            background-color: #001935;
            line-height: 17px;
        }

        .logo a {
        font-family: "AlongSanss2-BoldItalic";
        text-decoration: none;
        font-size: 27px;
        position: relative;
        top: 17px;
        color: white;
        margin-left: 15px;
        }

        nav {
        position: absolute;
        float: right;
        right: 1px;
        margin: 15px 15px;
        }

        nav a {
        color: rgba(255, 255, 255, 0.35);
        margin-left: 20px;
        text-decoration: none;
        font-size: 17px;
        /* position: relative; */
        }

        nav a:hover {
        color: white;
        text-decoration: none;
        }

        /* .column {
            margin-top: 10%;
        } */

        .profile_left {
        top: -5px;
        left: 57px;
        border-radius: 14px;
        width: 25%;
        max-width: 242px;
        min-width: 130px;
        height: 100%;
        float: left;
        position: relative;
        background-color: #405368;
        color: #fff;
        font-size: 16px;
        margin-right: 0px;
        margin-top: 7%;
        }

        iframe body {
            background-color: #001935;
        }

        .top_bar {
        width: 100%;
        height: 50px;
        background-color: #001935;
        border-bottom: 1px solid rgba(255, 255, 255, 0.13);
        margin-top: 0;
        padding: 0;
        min-width: 1050px;
        display: inline-flex;
        position: fixed;
        z-index: 10;
        left: 0;
        top: 0;
        }

        .search #search_text_input {
        position: absolute;
        left: 9%;
        top: 300px;
        }

        .button_holder {
        position: absolute;
        left: 3%;
        top: 300px;
        }

    </style>

</head>
<body>

    <div class="top_bar">
        <div class="logo">
            <a href="admin.php">Pleiades</a>
        </div>

        <nav>
            <a href="admin.php">
                <i class="fa-solid fa-house"></i>
            </a>
            <a href="users.php">
                <i class="fa-solid fa-user"></i>
            </a>
            <a href="../../includes/handlers/logout.php">
                <i class="fa-solid fa-sign-out"></i>
            </a>
        </nav>
    </div>

    <div class="wrapper">     
