<?php
session_start();

if(!isset($_SESSION["u_id"]))
{
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Alerts</title>
    <link rel="stylesheet" href="../Css/alerts.css">
</head>

<body>

<div class="container">

    <h1>Alerts</h1>

    <div id="alertsContainer"></div>

    <h1>Upcoming Matches</h1>

    <div id="matchesContainer"></div>

</div>

<script src="../Js/alerts.js"></script>

</body>
</html>