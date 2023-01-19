<?php
class User {
    private $user;
    private $con;

    public function __construct($con, $user) {
        $this-> con = $con;
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user'");
        $this->user = mysqli_fetch_array($user_details_query);
    }

    public function getUserName() {
        return $this->user['username'];
    }

	public function getNumberOfFriendRequests() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$username'");
		return mysqli_num_rows($query);
	}

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['num_posts'];
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
		// echo $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name'] . " " . $row['last_name'];
    }

	public function getFirstName() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name'];
    }

	public function getProfilePic() {
		$username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
		// var_dump($username);
		return $row['profile_pic'];
	}

    public function getFriendArray() {
		$username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
		return $row['friend_array'];
	}

    public function isClosed() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);

        if($row['user_closed'] == 'yes')
            return true;
        else 
            return false;

    }

    public function isFriend($username_to_check) {
        $usernameComma = ',' . $username_to_check . ',';
        if((strstr($this->user['friend_array'],$usernameComma) || $username_to_check == $this->user['username'])) {
            return true;
        }
        else
            return false;
    }
    
	public function isBlocked() {
		$user = $this->user['username'];
		$check_request_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user' AND user_blocked='yes'");
		if(mysqli_num_rows($check_request_query) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	public function blockUser($user_to_block) {
		// $logged_in_user = $this->user['username'];

		$query = mysqli_query($this->con, "UPDATE users SET user_blocked='yes' WHERE username='$user_to_block'");
	}

	public function unblockUser($user_to_unblock) {
		// $logged_in_user = $this->user['username'];

		$query = mysqli_query($this->con, "UPDATE users SET user_blocked='no' WHERE username='$user_to_unblock'");
	}

	public function didReceiveRequest($user_from) {
		$user_to = $this->user['username'];
		$check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'");
		if(mysqli_num_rows($check_request_query) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	public function didSendRequest($user_to) {
		$user_from = $this->user['username'];
		$check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'");
		if(mysqli_num_rows($check_request_query) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	public function removeFriend($user_to_remove) {
		$logged_in_user = $this->user['username'];

		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$user_to_remove'");
		$row = mysqli_fetch_array($query);
		$friend_array_username = $row['friend_array'];

		$new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$logged_in_user'");

		$new_friend_array = str_replace($this->user['username'] . ",", "", $friend_array_username);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$user_to_remove'");
	}

	public function sendRequest($user_to) {
		$user_from = $this->user['username'];
		$query = mysqli_query($this->con, "INSERT INTO friend_requests VALUES('', '$user_to', '$user_from')");
	}

	public function getMutualFriends($user_to_check) {
		$mutualFriends = 0;
		$user_array = $this->user['friend_array'];
		$user_array_explode = explode(",", $user_array);

		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$user_to_check'");
		$row = mysqli_fetch_array($query);
		if(isset($row['friend_array'])) {
			$user_to_check_array = $row['friend_array'];
			$user_to_check_array_explode = explode(",", $user_to_check_array);

			foreach($user_array_explode as $i) {
				foreach($user_to_check_array_explode as $j) {

					if($i == $j && $i != "") {
						$mutualFriends++;
					}
				}
			}
		}
		return $mutualFriends;
	}

	public function getFriends() {
		$userLoggedIn = $this->user['username'];
		$return_string = "";

		$user_array = $this->user['friend_array'];
		$user_array_explode = explode(",", $user_array);

		foreach($user_array_explode as $friend_username) {

			$query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$friend_username'");
			$row = mysqli_fetch_array($query);
			
			if(isset($row['first_name'])) {
				$friend = new User($this->con, $friend_username);

				$return_string .= 
					"<a href='../user/profile.php?profile_username=$friend_username'>
						<div>
						<img src='../../" . $friend->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px; height: 50px;'>"
						. $friend->getFirstAndLastName() .
						"</div>
					</a>";
			}
		}

		if(empty($return_string)) {
			$return_string = "<p style='margin: 20px;'>No friends yet :(</p>";
		}

		return $return_string;
	}

	public function getUsers() {
		$username = $this->user['username'];
		$return_string = '';

		if ($username == 'admin') {
			$query = mysqli_query($this->con, "SELECT first_name, last_name, signup_date, profile_pic, username, user_closed, user_blocked FROM users ORDER BY signup_date");
			$rows = $query->fetch_all(MYSQLI_ASSOC);

			foreach ($rows as $row) {
				if(isset($row['first_name']) && $row['first_name'] != 'admin') {
					$user = new User($this->con, $row['username']);
					$signup_date = $row['signup_date'];
					$closed = $row['user_closed'];
					$blocked = $row['user_blocked'];

					$return_string .= 
						"<div class='resultDisplay'>
						<a href='../admin/user_profile.php?profile_username=". $user->getUserName(). "' style='color: #1e96ca;'>
							<div class='liveSearchProfilePic'>
								<img src='../../" . $user->getProfilePic() . "' style='border-radius: 50%; margin-right: 5px; height: 50px;'>
							</div>
							<div class='liveSearchText'>"
							;
						
					$return_string .= $user->getFirstAndLastName() . 
								"<p>" . $user->getUserName() . "</p>
							<i style='float: right; color: #001935; font-size: 12px;'>" . $signup_date . "</i>
					</a></div></div>";
				}
			}

			return $return_string;
		}
		else return;
	}

}

?>