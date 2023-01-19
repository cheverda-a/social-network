<?php

if(isset($_POST['login_button'])) {
    $email = filter_var($_POST['log_email'], FILTER_VALIDATE_EMAIL);
    $_SESSION['log_email'] = $email;
    $password = md5($_POST['log_password']);

    $check_database_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $check_login_query = mysqli_num_rows($check_database_query);

    if($check_login_query == 1) {
        $row = mysqli_fetch_array($check_database_query);
        $username = $row['username'];

        $user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND user_closed='yes'");
        if (mysqli_num_rows($user_closed_query) == 1) {
            $reopen_account = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE email='$email'");
        }

        if($row['user_blocked'] == 'yes') {
            array_push($error_array, "<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>You were indefinitely banned for breaking the Pleiades rules.</span><br>");
            // var_dump($)
        }
        else {
            $_SESSION['username'] = $username;
            if ($username == 'admin')
                header("Location: ../admin/admin.php");
            else
                header("Location: index.php?sort=1");
            exit();
            }
    }
    else {
        array_push($error_array, "<span style='color: #ffffff; text-shadow: 2px 2px #270035;'>Email or password was incorrect.</span><br>");
    }
}

?>