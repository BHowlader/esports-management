<?php

ob_start();

session_start();

require_once "../Models/equipmentModel.php";

if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/trackEquipment.php";

    $item_id     = $_POST["equipment_id"] ?? "";
    $assigned_to = trim($_POST["assigned_to"] ?? "");

    if(!ctype_digit($item_id))
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Invalid equipment item!"));
    }
    else if(empty($assigned_to))
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Enter the member or team using this item!"));
    }
    else if(strlen($assigned_to) > 100)
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Name is too long! 100 characters maximum."));
    }
    else if(assignItem($item_id, $assigned_to))
    {
        header("Location:" . $back . "?okMsg=" . urlencode("Equipment assigned to " . $assigned_to . "."));
    }
    else
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Could not assign that item."));
    }

    exit();
}

$items = getAllItems();

?>
