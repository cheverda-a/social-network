<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Message.php");

$limit = 20;

$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropDown($_REQUEST, $limit);

?>