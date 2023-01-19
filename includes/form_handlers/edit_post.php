<?php
require '../../config/config.php';

if(isset($_GET['post_id']) && isset($_POST['edit_body'])) {
    $post_id = $_GET['post_id'];
    $edit_body = $_POST['edit_body'];

}

$query = mysqli_query($con, "UPDATE posts SET body='$edit_body' WHERE id='$post_id'");
// var_dump();

?>