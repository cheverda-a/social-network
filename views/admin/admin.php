<?php
include("../../includes/admin_header.php");

?>

<div class="profile_left">
    <img src="../../<?php  echo $user['profile_pic']; ?>" alt="profile picture">
    <a style='margin: 41%; '><?php echo $userLoggedIn; ?></a>
 	<br><br>
</div>

<div class="search">
    <form action="../main/search.php" method="GET" name="search_form">

    </form>

</div>

<div class="main_column column" style='background-color: transparent;'>
            <form id="sort_post" method="POST" action="index.php?sort=<?php $sort; ?>"> 
                <label id="sort_label" for="sort" style="color: white;">Recent
                </label>
            </form>

            <script>
                let sortBtn = document.getElementById('sort');
                let sort_label = document.getElementById('sort_label');
                $("#sort").on('change', function() {
                    if ($(this).is(':checked')) {
                        $sort = 'Recent';
                    }
                    else {
                        $sort = 'Oldest';
                    }
                    // console.log($sort);
                    $('#sort_post').submit();
                });
            </script>
    

            <div class="posts_area"></div>
            <img id='loading' src="../../assets/images/icons/loading.gif">
	    </div>

    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
        // console.log($sort);
        
        $(document).ready(function() {

            $('#loading').show();

            //Original ajax request for loading first posts 
            $.ajax({
                url: "../../includes/handlers/ajax_load_posts.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn,
                cache: false,

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
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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