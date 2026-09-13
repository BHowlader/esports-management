<?php
session_start();

if(!isset($_SESSION["u_id"]))
{
    header("Location: login.php");
    exit();
}

$role = $_SESSION["role"] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>News & Updates</title>
    <link rel="stylesheet" href="../Css/news.css">
</head>

<body>

<div class="container">

    <h1>News & Updates</h1>

    <?php if($role === "Admin" || $role === "Moderator") { ?>

    <div class="post-box">

        <h2>Post News</h2>

        <form id="newsForm">

            <input type="text" id="title" placeholder="News title">

            <textarea id="content" placeholder="Write news or update"></textarea>

            <button type="submit">Post News</button>

        </form>

        <p id="message"></p>

    </div>

    <?php } ?>

    <div id="newsContainer"></div>

</div>

<script src="../Js/news.js"></script>

</body>
</html>