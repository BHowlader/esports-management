<?php
session_start();

if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] ?? "") !== "Admin")
{
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Backup</title>
    <link rel="stylesheet" href="../Css/backup.css">
</head>

<body>

<div class="container">

    <h1>Database Backup</h1>

    <button id="backupBtn">Create Backup</button>

    <p id="message"></p>

</div>

<script src="../Js/backup.js"></script>

</body>
</html>