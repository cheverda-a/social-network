<?php
include("../../includes/admin_header.php");

if(isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if(isset($_POST['block_user'])) {
  $user = new User($con, $userLoggedIn);
  $user->blockUser($username);
}

if(isset($_POST['unblock_user'])) {
  $user = new User($con, $userLoggedIn);
  $user->unblockUser($username);
}

if ($username == 'admin') {
  header("Location: admin.php");
}

?>

<style type="text/css">
	 	.wrapper {
	 		margin-left: 0px;
			padding-left: 0px;
	 	} 
</style>
	
<div class="profile_left">
 	<img src="../../<?php echo $user_array['profile_pic']; ?>">
    <a style='margin: 20px; '><?php echo $username; ?></a>

 		<div class="profile_info">
 			<p><?php echo "Posts: " . $user_array['num_posts']; ?></p>
 			<p><?php echo "Likes: " . $user_array['num_likes']; ?></p>
 			<p><?php echo "Friends: " . $num_friends ?></p>
 		</div>

 		<form action="user_profile.php?profile_username=<?php echo $username; ?>" method="POST">
 			<?php 
        $profile_user_obj = new User($con, $username); 

        $user_obj = new User($con, $username);

        if ($user_obj->isBlocked()) { 
          echo '<input type="submit" name="unblock_user" class="warning" value="Unblock User"><br>';
        }
        else {
          echo '<input type="submit" name="block_user" class="danger" value="Block User"><br>';
        }
 			
 			?>
 		</form>


    <?php
      if($userLoggedIn != $username) {
        echo '<div class="profile_info_bottom" style="margin: 17px 17px; color: #1de840;">';
        echo '</div>';
        }
      else {
        echo "<br><br>";
      }
    ?>

 	</div>

   <div class="user_details column" id="friends" style=" width: 25%; float: left; margin-bottom: 20px; position: absolute; left: 6%; top: 375px;">
	<h4 style="margin: 8px 0px 2px 20px;"> <?php echo $user_obj->getFirstName(); ?>'s Friends</h4>

	<div class="loaded_conversations">
        <?php echo $user_obj->getFriends(); 
        
        ?>
	</div>
	<br>
</div>

<div class="main_column column" style='background-color: transparent;'>
    <div class="posts_area">
    <?php
      if($profile_user_obj->isClosed()) {
        echo "<div id='post_body' style='margin-top: 3px;'>This account has been closed by the user.
        </div>";
      }
    ?>

    </div>
        <img id='loading' src="../../assets/images/icons/loading.gif">

        <script>
            var div = document.getElementById("scroll_messages");
            div.scrollTop = div.scrollHeight;
        </script>
    </div>

</div>

</div>

<script>
  var userLoggedIn = '<?php echo $userLoggedIn; ?>';
  var profileUsername = '<?php echo $username; ?>';

  $(document).ready(function() {

    $('#loading').show();

    //Original ajax request for loading first posts 
    $.ajax({
      url: "../../includes/handlers/ajax_load_profile_posts.php",
      type: "POST",
      data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
      cache:false,

      success: function(data) {
        $('#loading').hide();
        $('.posts_area').html(data);
      }
    });

    $(window).scroll(function() {
      var height = $('.posts_area').height(); //Div containing posts
      var scroll_top = $(this).scrollTop();
      var page = $('.posts_area').find('.nextPage').val();
      var noMorePosts = $('.posts_area').find('.noMorePosts').val();

      if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
        $('#loading').show();

        var ajaxReq = $.ajax({
          url: "../../includes/handlers/ajax_load_profile_posts.php",
          type: "POST",
          data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
          cache:false,

          success: function(response) {
            $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
            $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 

            $('#loading').hide();
            $('.posts_area').append(response);
          }
        });

      } //End if 

      return false;

    }); //End (window).scroll(function())


  });

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script> -->


</body>
</html>