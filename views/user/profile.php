<?php
  include("../../includes/header.php");

  $message_obj = new Message($con, $userLoggedIn);
  if(isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
  }

  if(isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
  }
  if(isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
  }
  if(isset($_POST['respond_request'])) {
    header("Location: ../main/requests.php");
  }
  if(isset($_POST['post_message'])) {
    if(isset($_POST['message_body'])) {
      $body = mysqli_real_escape_string($con, $_POST['message_body']);
      $date = date("Y-m-d H:i:s");
      $message_obj->sendMessage($username, $body, $date);
    }

    $link = '#profileTabs a[href="#messages_div"]';
    echo "<script> 
            $(function() {
                $('" . $link ."').tab('show');
            });
          </script>";


  }

  if(isset($_POST['block_user'])) {
    $user = new User($con, $userLoggedIn);
    $user->blockUser($username);
  }
  if(isset($_POST['unblock_user'])) {
    $user = new User($con, $userLoggedIn);
    $user->unblockUser($username);
  }

  if($userLoggedIn == 'admin') {
    if(isset($_POST['block_user']))
      header("Location: ../admin/user_profile.php?profile_username=" . $username . "&block_user=yes");
    else 
      header("Location: ../admin/user_profile.php?profile_username=" . $username . "&unblock_user=yes");
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

 		<form action="../user/profile.php?profile_username=<?php echo $username; ?>" method="POST">
 			<?php 
 			$profile_user_obj = new User($con, $username); 
      if ($userLoggedIn != 'admin') {
 			if($profile_user_obj->isClosed()) {
 				header("Location: user_closed.php?");
      }
      if($profile_user_obj->isBlocked()) {
        header("Location: user_banned.php?");
      }
    }

 			$logged_in_user_obj = new User($con, $userLoggedIn); 
 			$user_obj = new User($con, $username); 

 			if($userLoggedIn != $username) {

 				if($logged_in_user_obj->isFriend($username)) {
 					echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
 				}
 				else if ($logged_in_user_obj->didReceiveRequest($username)) {
 					echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
 				}
 				else if ($logged_in_user_obj->didSendRequest($username)) {
 					echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
 				}
 				else 
 					echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
 			}
 			?>
 		</form>

    <input type="submit" class="deep_blue" data-bs-toggle="modal" data-bs-target="#post_form" value="Post Something">

    <?php
      if($userLoggedIn != $username) {
        echo "<form method='POST' action='../main/messages.php?u=" . $username . "'>
                <input type='submit' name='u' style='background-color: cadetblue;' value='Message " . $user_obj->getFirstName() . "'/>
              </form>";
        echo '<div class="profile_info_bottom" style="margin: 17px 17px; color: #1de840;">';
        echo $logged_in_user_obj->getMutualFriends($username) . " Mutual Friends";
        echo '</div>';
        }
      else {
        echo "<br><br>";
      }
      ?>
        
</div>

<div class="main_column column" style='background-color: transparent;'>

      <div class="posts_area"></div>
      <img id='loading' src="../../assets/images/icons/loading.gif">

	<script>
		var div = document.getElementById("scroll_messages");
		div.scrollTop = div.scrollHeight;
	</script>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="postModalLabel">Post Something!</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>

        <form class="profile_post" action="" method="POST">
            <div class="form-group">
                <textarea class="form-control" name="post_body"></textarea>
                <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
                <input type="hidden" name="user_to" value="<?php echo $username; ?>">
            </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post away!</button>
      </div>
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