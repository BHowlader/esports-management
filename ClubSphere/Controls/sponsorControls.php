<?php
session_start();

require_once "../Models/sponsorModel.php";

if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] ?? "") !== "Admin")
{
    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Access denied"
    ]);

    exit();
}

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $action = $_POST["action"] ?? "";

    if($action === "add")
    {
        $name = trim($_POST["name"] ?? "");
        $contact = trim($_POST["contact"] ?? "");
        $amount = floatval($_POST["amount"] ?? 0);
        $start = ($_POST["start"] ?? "") ?: null;
        $end = ($_POST["end"] ?? "") ?: null;
        $notes = trim($_POST["notes"] ?? "");

        if($name === "")
        {
            echo json_encode([
                "success" => false,
                "message" => "Sponsor name is required"
            ]);

            exit();
        }

        if(addSponsor($name, $contact, $amount, $start, $end, $notes))
        {
            echo json_encode([
                "success" => true,
                "message" => "Sponsor added successfully"
            ]);
        }
        else
        {
            echo json_encode([
                "success" => false,
                "message" => "Failed to add sponsor"
            ]);
        }

        exit();
    }

    if($action === "delete")
    {
        $id = intval($_POST["id"] ?? 0);

        if(deleteSponsor($id))
        {
            echo json_encode([
                "success" => true,
                "message" => "Sponsor deleted successfully"
            ]);
        }
        else
        {
            echo json_encode([
                "success" => false,
                "message" => "Failed to delete sponsor"
            ]);
        }

        exit();
    }
}

$result = getSponsors();

$sponsors = [];

while($row = mysqli_fetch_assoc($result))
{
    $sponsors[] = $row;
}

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "sponsors" => $sponsors
]);