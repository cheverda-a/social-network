<?php
class Post {
	private $user_obj;
	private $con;

	public function __construct($con, $user){
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}

	public function submitPost($body, $user_to, $imageName) {
		$body = strip_tags($body); 
		$body = mysqli_real_escape_string($this->con, $body);
		$check_empty = preg_replace('/\s+/', '', $body); 
      
		if($check_empty != "") {
			$body_array = preg_split("/\s+/", $body);

			foreach($body_array as $key => $value) {
				if(strpos($value, "www.youtube.com/watch?v=") !== false) {
					$link = preg_split("!&!", $value);
					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value ."\'></iframe><br>";
					$body_array[$key] = $value;
				}
			}
			$body = implode(" ", $body_array);

			$date_added = date("Y-m-d H:i:s");
			$added_by = $this->user_obj->getUsername();

			//якщо власний профіль, то нікому не адресується
			if($user_to == $added_by) {
				$user_to = "none";
			}

			//вставка посту
			$query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '0', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '$imageName')");
			$returned_id = mysqli_insert_id($this->con);

            if($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, 'profile_post');
            }

			//збільшити к-сть постів користувача
			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++;
			$update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");

			$stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are 
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big 
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		     highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	         thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like 
             hate sleepy reason for some little yes bye choose";

             //Convert stop words into array - split at white space
			$stopWords = preg_split("/[\s,]+/", $stopWords);

			//забрати всю пунктуацію
			$no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

			//якщо посилання не враховувати для статистики
			if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false
				&& strpos($no_punctuation, "http") === false && strpos($no_punctuation, "youtube") === false){
				//перевести пост у масив
				$keywords = preg_split("/[\s,]+/", $no_punctuation);

				foreach($stopWords as $value) {
					foreach($keywords as $key => $value2){
						if(strtolower($value) == strtolower($value2))
							$keywords[$key] = "";
					}
				}

				foreach ($keywords as $value) {
				    $this->calculateTrend(ucfirst($value));
				}

             }
		}
	}

	public function calculateTrend($term) {

		if($term != '') {
			$query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

			if(mysqli_num_rows($query) == 0)
				$insert_query = mysqli_query($this->con, "INSERT INTO trends(title,hits) VALUES('$term','1')");
			else 
				$insert_query = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
		}

	}

	public function loadPostsFriends($data, $limit, $sort) {

		$page = $data['page']; 
		$userLoggedIn = $this->user_obj->getUserName();

		if($page == 1) 
			$start = 0;
		else {
			$start = ($page - 1) * $limit;
		}
		
		$str = ""; //стрінг для повернення 
		if ($sort == 2)
			$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id ASC");
		else
			$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

		if(mysqli_num_rows($data_query) > 0) {

			$num_iterations = 0;
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];
				$imagePath = $row['image'];

				//
				if($row['user_to'] == "none") {
					$user_to = "";
				}
				else {
					$user_to_obj = new User($this->con, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='profile.php?profile_username=" . $row['user_to'] ."'>" . $user_to_name . "</a>";
				}

				//перевірка чи акаунт закритий
				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					continue;
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {

                        if($num_iterations++ < $start)
                            continue; 

                        else {
                            $count++;
                        }

						if($userLoggedIn == $added_by) {
                            $delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
							$edit_button = "<button class='edit_button' id='post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-pen-to-square'></i></button>";
						}
                        else {
                            $delete_button = "";
                            $edit_button = "";
							$restore_button = '';
						}

						if($userLoggedIn == 'admin') {
							$delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
							$restore_button = "<button class='restore_button' id='restore_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-solid fa-trash-can-arrow-up'></i></button>";
						}

                        $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = mysqli_fetch_array($user_details_query);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];


                        ?>

                    <script> 
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a") && !target.is("button")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block") 
									element.style.display = "none";
								else 
									element.style.display = "block";
							}
						}

					</script>


                    <?php

                        $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = mysqli_num_rows($comments_check);

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); 
                        $end_date = new DateTime($date_time_now); 
                        $interval = $start_date->diff($end_date); 
                        if($interval->y >= 1) {
                            if($interval == 1)
                                $time_message = $interval->y . " year ago"; //1 year ago
                            else 
                                $time_message = $interval->y . " years ago"; //1+ year ago
                        }
                        else if ($interval-> m >= 1) {
                            if($interval->d == 0) {
                                $days = " ago";
                            }
                            else if($interval->d == 1) {
                                $days = $interval->d . " day ago";
                            }
                            else {
                                $days = ' ' . $interval->d . " days ago";
                            }


                            if($interval->m == 1) {
                                $time_message = $interval->m . " month ". $days;
                            }
                            else {
                                $time_message = $interval->m . " months ". $days;
                            }

                        }
                        else if($interval->d >= 1) {
                            if($interval->d == 1) {
                                $time_message = "Yesterday";
                            }
                            else {
                                $time_message = ' ' . $interval->d . " days ago";
                            }
                        }
                        else if($interval->h >= 1) {
                            if($interval->h == 1) {
                                $time_message = $interval->h . " hour ago";
                            }
                            else {
                                $time_message = $interval->h . " hours ago";
                            }
                        }
                        else if($interval->i >= 1) {
                            if($interval->i == 1) {
                                $time_message = $interval->i . " minute ago";
                            }
                            else {
                                $time_message = $interval->i . " minutes ago";
                            }
                        }
                        else {
                            if($interval->s < 30) {
                                $time_message = "Just now";
                            }
                            else {
                                $time_message = $interval->s . " seconds ago";
                            }
                        }

						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
										</div>";
						}
						else {
							$imageDiv = "";
						}

                        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='../../$profile_pic' width='50' style='margin-right: 7px;'>
                                    </div>

                                    <div class='posted_by' style='color:#ACACAC;'>
									<a href='../user/profile.php?profile_username=$added_by'>$first_name $last_name </a> $user_to 
                                        <p style='float: right; font-size: 13px;'>
                                            $time_message
                                            &nbsp;
											$edit_button
											&nbsp;
                                            $delete_button
                                        </p>
                                    </div>

                                    <div id='post_body' style='margin-top: 3px;'>
                                        $body
                                        <br>
										$imageDiv
                                        <br>
                                    </div>

                                    <div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='../main/like.php?post_id=$id' scrolling='no'></iframe>
								    </div>
                                </div>
                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='../main/comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							    </div>
                                <hr>";
                }
				?>

                <script>

                    $(document).ready(function() {
                        $('#delete_post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("../../includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
                                if(result) 
                                    location.reload();
                            });
                        });
                    });

					$(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
								location.replace("../main/post.php?id=<?php echo $id; ?>&edit=yes");
                        });
                    });

                </script>

                <?php

			} //End while loop

			if($count > $limit) 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
			else 
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre; color: white;'> No more posts to show! </p>";
		}

		echo $str;

	}

	public function loadAllPosts($data, $limit, $order = '1') {

		$page = $data['page']; 
		$userLoggedIn = $this->user_obj->getUserName();

		if($page == 1) 
			$start = 0;
		else 
			$start = ($page - 1) * $limit;


		$str = ""; //стрінг для повернення
		if ($order == 'Oldest')
			$data_query = mysqli_query($this->con, "SELECT * FROM posts ORDER BY id ASC");
		else
			$data_query = mysqli_query($this->con, "SELECT * FROM posts ORDER BY id DESC");

		if(mysqli_num_rows($data_query) > 0) {

			$num_iterations = 0;
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];
				$deleted = $row['deleted'];
				$imagePath = $row['image'];

				if($row['user_to'] == "none") {
					$user_to = "";
				}
				else {
					$user_to_obj = new User($this->con, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='../admin/user_profile.php?profile_username=" . $row['user_to'] ."'>" . $user_to_name . "</a>";
				}

				//перевірка чи закритий акаунт
				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					continue;
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);

                        if($num_iterations++ < $start)
                            continue; 

                        if($count > $limit) {
                            break;
                        }
                        else {
                            $count++;
                        }

						if($userLoggedIn == 'admin') {
							$delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
							$restore_button = "<button class='restore_button' id='restore_post$id' style='background-color: transparent; font-weight: bold; z-index: 2; border: none;'><i class='fa-solid fa-trash-can-arrow-up'></i></button>";

						}
                        else {
                            $delete_button = "";
							$edit_button = "";
							$restore_button = "";
						}

                        $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = mysqli_fetch_array($user_details_query);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];
                        ?>

                    <script> 
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a") && !target.is("button")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block") 
									element.style.display = "none";
								else 
									element.style.display = "block";
							}
						}

					</script>


                    <?php

                        $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = mysqli_num_rows($comments_check);

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); //дати публікації
                        $end_date = new DateTime($date_time_now); //час зараз
                        $interval = $start_date->diff($end_date); //різниця між датами
                        if($interval->y >= 1) {
                            if($interval == 1)
                                $time_message = $interval->y . " year ago"; //1 year ago
                            else 
                                $time_message = $interval->y . " years ago"; //1+ year ago
                        }
                        else if ($interval-> m >= 1) {
                            if($interval->d == 0) {
                                $days = " ago";
                            }
                            else if($interval->d == 1) {
                                $days = $interval->d . " day ago";
                            }
                            else {
                                $days = ' ' . $interval->d . " days ago";
                            }


                            if($interval->m == 1) {
                                $time_message = $interval->m . " month ". $days;
                            }
                            else {
                                $time_message = $interval->m . " months ". $days;
                            }

                        }
                        else if($interval->d >= 1) {
                            if($interval->d == 1) {
                                $time_message = "Yesterday";
                            }
                            else {
                                $time_message = ' ' . $interval->d . " days ago";
                            }
                        }
                        else if($interval->h >= 1) {
                            if($interval->h == 1) {
                                $time_message = $interval->h . " hour ago";
                            }
                            else {
                                $time_message = $interval->h . " hours ago";
                            }
                        }
                        else if($interval->i >= 1) {
                            if($interval->i == 1) {
                                $time_message = $interval->i . " minute ago";
                            }
                            else {
                                $time_message = $interval->i . " minutes ago";
                            }
                        }
                        else {
                            if($interval->s < 30) {
                                $time_message = "Just now";
                            }
                            else {
                                $time_message = $interval->s . " seconds ago";
                            }
                        }

						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
										</div>";
						}
						else {
							$imageDiv = "";
						}

                        $str .= "<div class='status_post' ";
						if ($deleted == 'yes')
							$str .= "style='border: 3px solid #00b8ff;'";

							$str .=	"onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='../../$profile_pic' width='50' style='margin-right: 7px;'>
                                    </div>

                                    <div class='posted_by' style='color:#ACACAC;'>
                                        <a href='../admin/user_profile.php?profile_username=$added_by'> $first_name $last_name </a> $user_to 
                                        <p style='float: right; font-size: 13px;'>
                                            $time_message
											&nbsp;";
						if ($deleted == 'no')
							$str .= $delete_button;
						else 
							$str .= $restore_button; 
							$str .= "
                                        </p>
                                    </div>

                                    <div id='post_body' style='margin-top: 3px;'>
										$body
										<br>
										$imageDiv
										<br>
                                    </div>

                                    <div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='../main/like.php?post_id=$id' scrolling='no' style='background-color: transparent;'></iframe>
								    </div>

                                </div>

                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='../main/comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							    </div>
                                <hr>";
                }
				?>

                <script>

					$(document).ready(function() {
						$('#delete_post<?php echo $id; ?>').on('click', function() {
							console.log('errror');
							bootbox.confirm("Delete this post?", function(result) {
								$.post("../../includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
								if(result) 
									location.reload();
							});
						});
					});

					$(document).ready(function() {
						$('#restore_post<?php echo $id; ?>').on('click', function() {
							console.log('restore click');
							bootbox.confirm("Restore this post?", function(result) {
								$.post("../../includes/form_handlers/restore_post.php?post_id=<?php echo $id; ?>", {result:result});
								if(result) 
									location.reload();
							});
						});
					});

                </script>

                <?php

			} //End while loop

			if($count > $limit) 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
			else 
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre; color: white;'> No more posts to show! </p>";
		// }

		echo $str;

	}

    public function loadProfilePosts($data, $limit) {

		$page = $data['page']; 
        $profileUser = $data['profileUsername'];
		$userLoggedIn = $this->user_obj->getUserName();

		if($page == 1) 
			$start = 0;
		else 
			$start = ($page - 1) * $limit;

		$str = ""; //стрінг до повернення
		if ($userLoggedIn == 'admin')
			$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");
		else
			$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");

		if(mysqli_num_rows($data_query) > 0) {


			$num_iterations = 0; 
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {
				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];
				$deleted = $row['deleted'];
				$imagePath = $row['image'];

                        if($num_iterations++ < $start)
                            continue; 


                        if($count > $limit) {
                            break;
                        }
                        else {
                            $count++;
                        }

                        if($userLoggedIn == $added_by) {
                            $delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
							$edit_button = "<button class='edit_button' id='post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-pen-to-square'></i></button>";
						}
                        else {
                            $delete_button = "";
                            $edit_button = "";
							$restore_button = '';
						}

						if($userLoggedIn == 'admin') {
							$delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
							$restore_button = "<button class='restore_button' id='restore_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-solid fa-trash-can-arrow-up'></i></button>";
						}

                        $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = mysqli_fetch_array($user_details_query);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];


                        ?>

                    <script> 
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a") && !target.is("button")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block") 
									element.style.display = "none";
								else 
									element.style.display = "block";
							}
						}

					</script>


                        <?php

                        $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = mysqli_num_rows($comments_check);

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); //час публікації
                        $end_date = new DateTime($date_time_now); //час зараз
                        $interval = $start_date->diff($end_date); //різниця в часі
                        if($interval->y >= 1) {
                            if($interval == 1)
                                $time_message = $interval->y . " year ago"; 
                            else 
                                $time_message = $interval->y . " years ago"; // більше року назад
                        }
                        else if ($interval-> m >= 1) {
                            if($interval->d == 0) {
                                $days = " ago";
                            }
                            else if($interval->d == 1) {
                                $days = $interval->d . " day ago";
                            }
                            else {
                                $days = ' ' . $interval->d . " days ago";
                            }


                            if($interval->m == 1) {
                                $time_message = $interval->m . " month ". $days;
                            }
                            else {
                                $time_message = $interval->m . " months ". $days;
                            }

                        }
                        else if($interval->d >= 1) {
                            if($interval->d == 1) {
                                $time_message = "Yesterday";
                            }
                            else {
                                $time_message = ' ' . $interval->d . " days ago";
                            }
                        }
                        else if($interval->h >= 1) {
                            if($interval->h == 1) {
                                $time_message = $interval->h . " hour ago";
                            }
                            else {
                                $time_message = $interval->h . " hours ago";
                            }
                        }
                        else if($interval->i >= 1) {
                            if($interval->i == 1) {
                                $time_message = $interval->i . " minute ago";
                            }
                            else {
                                $time_message = $interval->i . " minutes ago";
                            }
                        }
                        else {
                            if($interval->s < 30) {
                                $time_message = "Just now";
                            }
                            else {
                                $time_message = $interval->s . " seconds ago";
                            }
                        }

						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
										</div>";
						}
						else {
							$imageDiv = "";
						}

                        $str .= "<div class='status_post'";
						if ($deleted == 'yes')
							$str .= "style='border: 3px solid #00b8ff;";
						
						$str .= "onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='../../$profile_pic' width='50' style='margin-right: 7px;'>
                                    </div>

                                    <div class='posted_by' style='color:#ACACAC;'>
										<a href='../user/profile.php?profile_username=$added_by'>$first_name $last_name </a> 
                                         
                                        <p style='float: right; font-size: 13px;'>
                                            $time_message
											&nbsp;
											$edit_button
											&nbsp;";
									if ($deleted == 'no')
										$str .= $delete_button;
									else 
										$str .= $restore_button;
                                    $str .= "
                                        </p>
                                    </div>

                                    <div id='post_body' style='margin-top: 3px;'>
										$body
										<br>
										$imageDiv
										<br>
                                    </div>

                                    <div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='../main/like.php?post_id=$id' scrolling='no'></iframe>
								    </div>

                                </div>
                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='../main/comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							    </div>
                                <hr>";
                
				?>

              	<script>

					$(document).ready(function() {
						$('#delete_post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {
								$.post("../../includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();
							});
						});
					});

					
					$(document).ready(function() {
						$('#post<?php echo $id; ?>').on('click', function() {
								location.replace("../main/post.php?id=<?php echo $id; ?>&edit=yes");
						});
					});

					$(document).ready(function() {
						$('#restore_post<?php echo $id; ?>').on('click', function() {
							console.log('restore click');
							bootbox.confirm("Restore this post?", function(result) {
								$.post("../../includes/form_handlers/restore_post.php?post_id=<?php echo $id; ?>", {result:result});
								if(result) 
									location.reload();
							});
						});
					});

				</script>
				

                <?php

			} //End while loop

			if($count > $limit) 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
			else 
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre; color: white;'> No more posts to show! </p>";
		}

		echo $str;

	}

    public function getSinglePost($post_id, $edit) {

		$userLoggedIn = $this->user_obj->getUsername();

		$opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

		$str = ""; 
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

		if(mysqli_num_rows($data_query) > 0) {
			$row = mysqli_fetch_array($data_query); 
			$id = $row['id'];
			$body = $row['body'];
			$added_by = $row['added_by'];
			$date_time = $row['date_added'];
			$imagePath = $row['image'];

			if($row['user_to'] == "none") {
				$user_to = "";
			}
			else {
				$user_to_obj = new User($this->con, $row['user_to']);
				$user_to_name = $user_to_obj->getFirstAndLastName();
				$user_to = "to <a href='../user/profile.php?profile_username=" . $row['user_to'] ."'>" . $user_to_name . "</a>";
				}

				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					return;
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);
				if($user_logged_obj->isFriend($added_by)) {
					if($userLoggedIn == $added_by) {
						$delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
						$edit_button = "<button class='edit_button' id='edit_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-pen-to-square'></i></button>";
						$save_button = "<button class='save_button' id='save_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-solid fa-check'></i></button>";
					}
					else {
						$delete_button = "";
						$edit_button = "";
						$save_button = "";
					}

					if($userLoggedIn == 'admin') {
						$delete_button = "<button class='delete_button' id='delete_post$id' style='background-color: transparent; font-weight: bold; border: none;'><i class='fa-regular fa-trash-can'></i></button>";
					}


					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
					$user_row = mysqli_fetch_array($user_details_query);
					$first_name = $user_row['first_name'];
					$last_name = $user_row['last_name'];
					$profile_pic = $user_row['profile_pic'];


					?>
					<script> 
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a") && !target.is("button")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block") 
									element.style.display = "none";
								else 
									element.style.display = "block";
							}
						}

					</script>
				<?php

					$comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
					$comments_check_num = mysqli_num_rows($comments_check);


					//Timeframe
					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates 
					if($interval->y >= 1) {
						if($interval == 1)
							$time_message = $interval->y . " year ago"; //1 year ago
						else 
							$time_message = $interval->y . " years ago"; //1+ year ago
					}
					else if ($interval->m >= 1) {
						if($interval->d == 0) {
							$days = " ago";
						}
						else if($interval->d == 1) {
							$days = $interval->d . " day ago";
						}
						else {
							$days = $interval->d . " days ago";
						}


						if($interval->m == 1) {
							$time_message = $interval->m . " month ". $days;
						}
						else {
							$time_message = $interval->m . " months ". $days;
						}

					}
					else if($interval->d >= 1) {
						if($interval->d == 1) {
							$time_message = "Yesterday";
						}
						else {
							$time_message = $interval->d . " days ago";
						}
					}
					else if($interval->h >= 1) {
						if($interval->h == 1) {
							$time_message = $interval->h . " hour ago";
						}
						else {
							$time_message = $interval->h . " hours ago";
						}
					}
					else if($interval->i >= 1) {
						if($interval->i == 1) {
							$time_message = $interval->i . " minute ago";
						}
						else {
							$time_message = $interval->i . " minutes ago";
						}
					}
					else {
						if($interval->s < 30) {
							$time_message = "Just now";
						}
						else {
							$time_message = $interval->s . " seconds ago";
						}
					}

					if ($imagePath != "") {
						$imageDiv = "<div class='postedImage'>
									<img src='$imagePath'>
									</div>";
					}
					else {
						$imageDiv = "";
					}

					$str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='../../$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
								<a href='../user/profile.php?profile_username=$added_by'>$first_name $last_name </a> $user_to
									<p style='float: right; font-size: 13px;'>
                                        $time_message
										&nbsp;
										$edit_button
                                        &nbsp;
                                        $delete_button
                                    </p>
								</div>
								<div id='post_body' style='margin-top: 5px;'>";
					if ($edit == "yes")
						$str .= "<textarea id='edit_body' style='height: 95px; width: 80%;'>$body</textarea>
						$save_button";
					else 
						$str .= $body;
						
								$str .= "<br>
									$imageDiv
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";


				?>
				<script>

					$(document).ready(function() {
						$('#delete_post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("../../includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});
					});

					$(document).ready(function() {
						$('#edit_post<?php echo $id; ?>').on('click', function() {
							if ('<?php echo $edit; ?>' == 'yes')
								location.replace("../main/post.php?id=<?php echo $id; ?>&edit=no");
							else
								location.replace("../main/post.php?id=<?php echo $id; ?>&edit=yes");
						});
					});

					$(document).ready(function() {
						$('#save_post<?php echo $id; ?>').on('click', function() {
							let edit_body = $("#edit_body").val();
								$.post("../../includes/form_handlers/edit_post.php?post_id=<?php echo $id; ?>", {edit_body: edit_body}).done(function(error) {
									if (error != "") {
										alert(error);
										return;
									}
									location.replace("../main/post.php?id=<?php echo $id; ?>&edit=no");
								});

								// if(result)
								// });
						});
					});

				</script>
				<?php
				}
				else {
					echo "<p style='color: white;'>You cannot see this post because you are not friends with this user.</p>";
					return;
				}
		}
		else {
			echo "<p style='color: white;'>No post found. If you clicked a link, it may be broken.</p>";
					return;
		}

		echo $str;
	}
}

?>