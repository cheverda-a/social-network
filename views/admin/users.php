<?php
include("../../includes/admin_header.php");
$user_obj = new User($con, $userLoggedIn);

if(isset($_GET['q']) && $_GET['q'] != 'admin') {
    $query = $_GET['q'];
}
else {
    $query = '';
}

if(isset($_GET['type'])) {
    $type = $_GET['type'];
}
else {
    $type = 'name';
}

?>

    <div class="user_details" style='background-color: transparent;'>
        <div class="search">
            <form action="users.php" method="GET" name="search_form">
                <div class="button_holder" style="position: absolute; left: 0%; top: 50px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search users" autocomplete="off" id="search_text_input" style="position: absolute; left: 6%; top: 50px;">

            </form>

            <!-- <div class="search_results"></div> -->
            <!-- <div class="search_results_footer_empty"></div> -->

        </div>
    </div>

    <div class="main_column column" id="main_column">
        <h4 style="margin: 8px 0px 2px 20px;">All Users</h4>
        
        <div class="search_results">
        <?php echo $user_obj->getUsers(); 
        ?>
        </div>

        <!-- <div class="search_results_footer_empty"></div> -->

    </div>
    

</body>
</html>