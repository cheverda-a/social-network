<?php  
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

if(isset($_POST['sort'])) {
    $sort = $_POST['sort'];
}
else {
    $sort = 1;
}

// var_dump($order);
$limit = 1000; //Number of posts to be loaded per call
$posts = new Post($con, $_REQUEST['userLoggedIn']);

if ($_REQUEST['userLoggedIn'] == 'admin') 
    $posts->loadAllPosts($_REQUEST, $limit);
else
    $posts->loadPostsFriends($_REQUEST, $limit, $sort);
?>