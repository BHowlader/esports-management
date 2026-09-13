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
    <title>Sponsor Directory</title>
    <link rel="stylesheet" href="../Css/sponsors.css">
</head>

<body>

<div class="container">

    <h1>Sponsor Directory</h1>

    <div class="form-box">

        <h2>Add Sponsor</h2>

        <form id="sponsorForm">

            <input type="text" id="name" placeholder="Sponsor name" required>

            <input type="text" id="contact" placeholder="Contact information">

            <input type="number" id="amount" placeholder="Sponsorship amount" step="0.01">

            <label>Contract Start</label>
            <input type="date" id="start">

            <label>Contract End</label>
            <input type="date" id="end">

            <textarea id="notes" placeholder="Notes"></textarea>

            <button type="submit">Add Sponsor</button>

        </form>

        <p id="message"></p>

    </div>

    <div id="sponsorContainer"></div>

</div>

<script src="../Js/sponsors.js"></script>

</body>
</html>