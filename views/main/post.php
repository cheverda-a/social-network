<?php  
include("../../includes/header.php");

if(isset($_GET['id'])) {
	$id = $_GET['id'];
}
else {
	$id = 0;
}

if(isset($_GET['edit'])) {
	$edit = $_GET['edit'];
}
else {
	$edit = 'no';
}

?>

<div class="user_details column">
		<a href="../user/profile.php?profile_username=<?php echo $userLoggedIn; ?>">  <img src="../../<?php echo $user['profile_pic']; ?>"> </a>

		<div class="user_details_left_right">
			<a href="../user/profile.php?profile_username=<?php echo $userLoggedIn; ?>" style='font-weight: bold;'>
			<?php 
			echo $user['first_name'] . " " . $user['last_name'];

			 ?>
			</a>
            <br>
            <a href="">
                <?php
                echo "Posts: " . $user['num_posts'];
                ?>
            </a>
            <br>
            <a href="">
                <?php
                echo "Likes: " . $user['num_likes'];
                ?>
            </a>
		</div>

	</div>

	<div class="main_column column" id="main_column" style='background-color: transparent;'>

		<div class="posts_area">

			<?php 
				$post = new Post($con, $userLoggedIn);
				$post->getSinglePost($id, $edit);
			?>

		</div>

	</div>