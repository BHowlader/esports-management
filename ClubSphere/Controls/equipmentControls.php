<?php

ob_start();

session_start();

require_once "../Models/equipmentModel.php";

if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}

$validConditions = array("New", "Good", "Fair", "Damaged");
$validStatuses   = array("Available", "In Use", "Maintenance");


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/equipment.php";

    $item_condition = $_POST["item_condition"] ?? "";
    $status         = $_POST["status"] ?? "";

    if(!in_array($item_condition, $validConditions) || !in_array($status, $validStatuses))
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Please choose a valid condition and status!"));
        exit();
    }

    if(isset($_POST["update_equipment"]))
    {
        $item_id = $_POST["equipment_id"] ?? "";

        if(!ctype_digit($item_id))
        {
            header("Location:" . $back . "?errMsg=" . urlencode("Invalid equipment item!"));
        }
        else if(updateItemState($item_id, $item_condition, $status))
        {
            header("Location:" . $back . "?okMsg=" . urlencode("Equipment updated."));
        }
        else
        {
            header("Location:" . $back . "?errMsg=" . urlencode("Could not update that item."));
        }

        exit();
    }

    $item_name = trim($_POST["equipment_name"] ?? "");
    $category  = trim($_POST["category"] ?? "");

    $errMsg = "";
    $hasErr = false;

    if(empty($item_name))
    {
        $hasErr = true;
        $errMsg = "Equipment name is required!";
    }
    else if(strlen($item_name) > 100)
    {
        $hasErr = true;
        $errMsg = "Equipment name is too long! 100 characters maximum.";
    }

    if(empty($category))
    {
        $hasErr = true;
        $errMsg = "Category is required!";
    }
    else if(strlen($category) > 50)
    {
        $hasErr = true;
        $errMsg = "Category is too long! 50 characters maximum.";
    }

    if($hasErr)
    {
        header("Location:" . $back . "?errMsg=" . urlencode($errMsg));
    }
    else if(addItem($item_name, $category, $item_condition, $status))
    {
        header("Location:" . $back . "?okMsg=" . urlencode("Equipment added."));
    }
    else
    {
        header("Location:" . $back . "?errMsg=" . urlencode("Could not add that equipment."));
    }

    exit();
}

$items = getAllItems();

?>
