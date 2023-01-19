<?php 
include("../../includes/header.php");
include("../../includes/form_handlers/settings_handler.php");

if ($userLoggedIn == 'admin') {
    header("Location: ../main/admin.php");
}

?>

<div class="main_column column">

	<h4>Account Settings</h4>
	<?php
		echo "<img src='../../" . $user['profile_pic'] ."' class='small_profile_pic'>";
	?>
	<br>
	<a href="upload.php">Upload new profile picture</a> <br><br><br>

	<p>
		Modify the values and click 'Update Details'.
	</p>

	<?php
	$user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
	$row = mysqli_fetch_array($user_data_query);

	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	?>

	<form action="settings.php" method="POST">
		<table>
			<tr>
				<th>
					First Name: 
				</th>
				<th>
					<input type="text" name="first_name" value="<?php echo $first_name; ?>" id="settings_input"><br>
				</th>
			</tr>
			<tr>
				<th>
					Last Name: 
				</th>
				<th>
					<input type="text" name="last_name" value="<?php echo $last_name; ?>" id="settings_input"><br>
				</th>
			</tr>
			<tr>
				<th>
					Email: 
				</th>
				<th>
					<input type="text" name="email" value="<?php echo $email; ?>" id="settings_input"><br>
				</th>
			</tr>
		</table>

		<?php echo $message; ?>

		<input type="submit" name="update_details" id="save_details" value="Update Details" class="deep_blue settings_submit"><br>
	</form>

	<h4>Change Password</h4>
	<form action="settings.php" method="POST">
		<table>
			<tr>
				<th>
					Old Password: 
				</th>
				<th>
					<input type="password" name="old_password" id="settings_input"><br>
				</th>				
			</tr>
			<tr>
				<th>
					New Password: 
				</th>
				<th>
					<input type="password" name="new_password_1" id="settings_input">
				</th>
			</tr>
			<tr>
				<th>
					Confirm Password: 
				</th>
				<th>
					<input type="password" name="new_password_2" id="settings_input"><br>
				</th>

			</tr>
			
		</table>
		<?php echo $password_message; ?>

		<input type="submit" name="update_password" id="save_details" value="Update Password" class="deep_blue settings_submit"><br>
	</form>

	<h4>Close Account</h4>
	<form action="settings.php" method="POST">
		<input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit">
	</form>


</div>