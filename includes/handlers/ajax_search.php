<?php
include("../../config/config.php");
include("../../includes/classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

//if there are underscores, assume user is searching for usernames
if ($userLoggedIn == "admin")  {
    if(strpos($query, '_') !== false)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '%$query%' LIMIT 8");
    else if (count($names) == 2)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') LIMIT 8");
    else if (count($names) == 1)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') LIMIT 8");    
}
else   {
    if(strpos($query, '_') !== false)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '%$query%' AND user_closed='no' LIMIT 8");
    else if (count($names) == 2)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8");
    else if (count($names) == 1)
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8");    
}
    

if ($userLoggedIn != 'admin') {
    if($query != "") {
        while($row = mysqli_fetch_array($usersReturnedQuery)) {
            if ($row['username'] == 'admin')
                continue;

            $user = new User($con, $userLoggedIn);
            if ($row['username'] != $userLoggedIn) 
                $mutual_friends = $user->getMutualFriends($row['username'] . " friends in common");
            else
                $mutual_friends = "";

            echo "<div class='resultDisplay'>
                    <a href='../user/profile.php?profile_username=" . $row['username'] . "' style='color: #1485BD'>
                        <div class='liveSearchProfilePic'>
                            <img src='../../" . $row['profile_pic'] . "'>
                        </div>
                        <div class='liveSearchText'>
                            " . $row['first_name'] . " " . $row['last_name'] . "
                            <p>" . $row['username'] . "</p>";
            if ($row['username'] == $userLoggedIn) 
                echo "<p id='grey'>This is you!</p>";
            echo "</div></a></div>";
        }
    }
}
else {
    if($query != "") {
        while($row = mysqli_fetch_array($usersReturnedQuery)) {
            if ($row['username'] == 'admin')
                continue;

            echo "<div class='resultDisplay'>
                    <a href='../user/profile.php?profile_username=" . $row['username'] . "' style='color: #1485BD'>
                        <div class='liveSearchProfilePic'>
                            <img src='../../" . $row['profile_pic'] . "'>
                        </div>
                        <div class='liveSearchText'>
                            " . $row['first_name'] . " " . $row['last_name'] . "
                            <p>" . $row['username'] . "</p>";
            echo "</div></a></div>";
        }
    }
}


?>