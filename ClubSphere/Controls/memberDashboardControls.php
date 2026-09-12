<?php

require_once "../Models/userModels.php";

if(!isset($_SESSION["u_id"]))
{
    echo "Please login first!";
    exit();
}

$u_id = $_SESSION["u_id"];

$userProfile = getUserProfile($u_id);

?>