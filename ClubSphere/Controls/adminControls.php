<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}


require_once "../Models/userModels.php";

  /*Prevents others viewing the admin works*/

if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}



if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $u_id = $_POST["u_id"];

                   /* FR4 - Approve */
    if(isset($_POST["approve"]))
    {
        updateUserStatus($u_id, "Approved");
    }

                           /* FR4 - Reject */
    else if(isset($_POST["reject"]))
    {
        updateUserStatus($u_id, "Rejected");
    }

         /* FR5 - Change Role */
    else if(isset($_POST["changeRole"]))
    {
        $role = $_POST["role"];

        updateUserRole($u_id, $role);
    }

    header("Location:../Views/adminMembers.php");
    exit();
}
    $pendingUsers = getPendingUsers();

    $approvedUsers = getApprovedUsers();

    $totalMembers = getTotalApprovedMembers();

?>

