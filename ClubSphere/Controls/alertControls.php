<?php
session_start();

require_once "../Models/alertModel.php";

if(!isset($_SESSION["u_id"]))
{
    header("Location: ../Views/login.php");
    exit();
}

$user_id = $_SESSION["u_id"];

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $action = $_POST["action"] ?? "";

    if($action === "read")
    {
        $alert_id = intval($_POST["alert_id"] ?? 0);

        markAlertRead($alert_id, $user_id);

        header("Content-Type: application/json");

        echo json_encode([
            "success" => true
        ]);

        exit();
    }
}

$result = getUserAlerts($user_id);

$alerts = [];

while($row = mysqli_fetch_assoc($result))
{
    $alerts[] = $row;
}

$matchesResult = getUpcomingMatches();

$matches = [];

while($row = mysqli_fetch_assoc($matchesResult))
{
    $matches[] = $row;
}

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "alerts" => $alerts,
    "matches" => $matches
]);