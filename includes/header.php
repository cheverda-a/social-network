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
    <title>Pleiades</title>
    
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
        body {
            background-image: url("../../assets/images/backgrounds/sky.jpg");
            /* background-repeat: no-repeat; */
            /* background-size: cover; */
        }
    </style>

</head>
<body style="background-color: #001935;">

    <div class="top_bar">
        <div class="logo">
            <a href="../user/index.php">Pleiades</a>
        </div>

        <div class="search">
            <form action="../main/search.php" method="GET" name="search_form">
                <div class="button_holder">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search Pleiades" autocomplete="off" id="search_text_input">


            </form>

            <div class="search_results">
                
            </div>

            <div class="search_results_footer_empty">

            </div>

        </div>

        <nav>
			<?php
				//Unread messages 
				$messages = new Message($con, $userLoggedIn);
				$num_messages = $messages->getUnreadNumber();

                //Unread notifications 
				$notification = new Notification($con, $userLoggedIn);
				$num_notification = $notification->getUnreadNumber();

                $user_obj = new User($con, $userLoggedIn);
				$num_requests = $user_obj->getNumberOfFriendRequests();
			?>

            <a href="../user/profile.php?profile_username=<?php echo $userLoggedIn; ?>">
                <i class="fa-solid fa-house"></i>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
                <i class="fa-solid fa-envelope"></i>
                <?php
                if($num_messages > 0)
                    echo "<span class='notification_badge' id='unread_message'>" . $num_messages . "</span>";
                ?>
                 <script>
                    console.log(<?php echo $num_messages?>);
                </script>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
                <i class="fa-solid fa-bolt"></i>
                <?php
                if($num_notification > 0)
                    echo "<span class='notification_badge' id='unread_notification'>" . $num_notification . "</span>";
                ?>
                <script>
                    console.log(<?php echo $num_notification?>);
                </script>
            </a>
            <a href="../main/requests.php">
                <i class="fa-solid fa-user"></i>
                <?php
                if($num_requests > 0)
                    echo "<span class='notification_badge' id='unread_requests'>" . $num_requests . "</span>";
                ?>
                <script>
                    console.log(<?php echo $num_requests?>);
                </script>
            </a>
            <a href="../main/settings.php">
                <i class="fa-solid fa-gear"></i>
            </a>
            <a href="../../includes/handlers/logout.php">
                <i class="fa-solid fa-sign-out"></i>
            </a>
        </nav>

        <div class="dropdown_data_window" style="height:0px; border:none;">
            <input type="hidden" id="dropdown_data_type" value="">
        </div>

    </div>


    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';

        $(document).ready(function() {

            $(.dropdown_data_window).scroll(function() {
                var inner_height = $('.dropdown_data_window').innerHeight(); // div containing posts
                var scroll_top = $('.dropdown_data_window').scrollTop();
                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                if((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {
                    var pageName;
                    var type = $('#dropdown_data_type');

                    if(type == 'notification') 
                        pageName = "ajax_load_notifications.php";
                    else if (ty[e == 'message'])
                        pageName = 'ajax_load_messages.php';
                    
                    var ajaxReq = $.ajax({
                        url: "includes/handlers/" + pageName,
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                        cache: false,

                        success: function(response) {
                            $('.dropdown_data_window').find('.nextPageDropdownData').remove();
                            $('.dropdown_data_window').find('.noMoreDropdownData').remove();

                            $('.dropdown_data_window').append(response);
                        }
                    });
                }

                return false;

            }); // end (window).scroll(fucntion())

        });

    </script>

    <div class="wrapper">     