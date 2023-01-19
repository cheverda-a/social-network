<?php
session_start();
session_destroy();
header("Location: ../../views/user/register.php");
?>