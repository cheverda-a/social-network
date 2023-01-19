<?php 
include("../../includes/header.php");

$user_obj = new User($con, $userLoggedIn);

if ($userLoggedIn == 'admin') {
    header("Location: ../admin/admin.php");
}

?>

<div class="main_column column" id="main_column" style="background-color: white;">
    <h4 style='margin: 5px 10px;'>Friend Requests</h4>
    <?php 

    $query = mysqli_query($con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
    if(mysqli_num_rows($query) == 0)
        echo "<a style='margin: 10px; color: #00b8ff;'>You have no friend requests at this time!</a>";
    else {

        while($row = mysqli_fetch_array($query)) {
            $user_from = $row['user_from'];
            $user_from_obj = new User($con, $user_from);

            echo "<img src='../../" . $user_from_obj->getProfilePic() . "' width='50' style='margin: 10px; border-radius: 5px;'>";
            echo "<a href='../user/profile.php?profile_username=$user_from'>" . $user_from_obj->getFirstAndLastName() . "</a>  sent you a friend request!";

            $user_from_friend_array = $user_from_obj->getFriendArray();

            if(isset($_POST['accept_request' . $user_from])) {
                $add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$user_from,') WHERE username='$userLoggedIn'");

                $add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_from'");

                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "You are now friends!";
                header("Location: requests.php");

            }

            if(isset($_POST['ignore_request' . $user_from])) {
                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "Request ignored!";
                header("Location: requests.php");
            }
            ?>

            <form action="requests.php" method="POST">
                <input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
                <input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">
            </form>
        <?php



        }
    }
    ?>

</div>

<div class="user_details column" id="friends">
	<h4 style="margin: 8px 0px 2px 20px;">Your Friends</h4>

	<div class="loaded_conversations">
        <?php echo $user_obj->getFriends(); 
        
        ?>
	</div>
	<br>
</div>