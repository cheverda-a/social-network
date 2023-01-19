<?php
    include("../../includes/header.php");

    if(isset($_POST['post'])){

        $uploadOk = 1;
        $imageName = $_FILES['fileToUpload']['name'];
        $errorMessage = "";
    
        if($imageName != "") {
            $targetDir = "../../assets/images/posts/";
            $imageName = $targetDir . uniqid() . basename($imageName);
            $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);
    
            if($_FILES['fileToUpload']['size'] > 10000000) {
                $errorMessage = "Sorry your file is too large";
                $uploadOk = 0;
            }
    
            if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
                $errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
                $uploadOk = 0;
            }
    
            if($uploadOk) {
                
                if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                    //image uploaded okay
                }
                else {
                    //image did not upload
                    $uploadOk = 0;
                }
            }
    
        }
    
        if($uploadOk) {
            $post = new Post($con, $userLoggedIn);
            $post->submitPost($_POST['post_text'], 'none', $imageName);
        }
        else {
            echo "<div style='text-align:center;' class='alert alert-danger'>
                    $errorMessage
                </div>";
        }
    
    }

    if ($userLoggedIn == 'admin') {
        header("Location: ../admin/admin.php?");
    }

    if(isset($_GET['sort'])){
        $sort = $_GET['sort'];
    }
    else {
        $sort = 1;
    }

?>

        <div class="user_details column">
            <a href="../user/profile.php?profile_username=<?php echo $userLoggedIn; ?>">
                <img src="../../<?php  echo $user['profile_pic']; ?>" alt="profile picture">
            </a>
          
            <div class="user_details_left_right">

                <a href="../user/profile.php?profile_username=<?php echo $userLoggedIn?>" class="username" style='font-weight: bold;'>
                    <?php
                    echo  $user['first_name'] . " " . $user['last_name'];
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

        <div class="main_column column" style='background-color: transparent;'>
            <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" id="fileToUpload">
                <textarea name="post_text" id="post_text" placeholder="Got something to say?" style=' font-family: "HelveticaNeueCyr-Light";'></textarea>
                <br>
                <input type="submit" name="post" id="post_button" value="Post now">
            </form>

            <?php 
            if ($sort == 2) {
                echo '<a href="index.php?sort=1" >
                <button id="sortRecent" class="recent" >
                <i class="fa-solid fa-arrow-up-1-9"></i>
                </button>
                </a>';
            }
            else if ($sort == 1) {
                echo '<a href="index.php?sort=2">
                <button id="sortOldest" class="recent">
                <i class="fa-solid fa-arrow-down-9-1"></i>
                </button></a>';
            }
            
            ?>

            <div class="posts_area"></div>
            <img id='loading' src="../../assets/images/icons/loading.gif">
	    </div>

        <div class="user_details column">

            <h4>Popular</h4>

            <div class="trends">
                <?php 
                $query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 5");

                foreach ($query as $row) {
                    
                    $word = $row['title'];
                    $word_dot = strlen($word) >= 14 ? "..." : "";

                    $trimmed_word = str_split($word, 14);
                    $trimmed_word = $trimmed_word[0];

                    echo "<div style'padding: 1px'>";
                    echo $trimmed_word . $word_dot;
                    echo "<br></div><br>";


                }

                ?>
            </div>
        </div>


    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
        var sort = '<?php echo $sort; ?>';
        console.log(sort);
        
        $(document).ready(function() {

            $('#loading').show();

            //Original ajax request for loading first posts 
            $.ajax({
                url: "../../includes/handlers/ajax_load_posts.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn + "&sort=" + sort,
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
                        url: "../../includes/handlers/ajax_load_posts.php",
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&sort=" + sort,
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


    </div>
</body>
</html>