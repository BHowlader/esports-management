<?php

session_start();

require_once "../Models/userModels.php";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(!isset($_SESSION["u_id"]))
    {
        echo "Please login first!";
        exit();
    }

    $u_id = $_SESSION["u_id"];

    $game_type = $_POST["game_type"];
    $ranking = $_POST["ranking"];
    $social_link = $_POST["social_link"];

    $result = updateProfile(
        $u_id,
        $game_type,
        $ranking,
        $social_link
    );

    if($result)
    {
        header("Location: ../Views/memberDashboard.php");
        exit();
    }
    else
    {
        echo "Profile Update Failed!";
    }
}

?>