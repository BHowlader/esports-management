<?php

require_once "dbConnect.php";


function addItem($item_name, $category, $item_condition, $status)
{
    $conn = dbConnection();

    $sql = "INSERT INTO inventory_item (item_name, category, item_condition, status)
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ssss", $item_name, $category, $item_condition, $status);

    return mysqli_stmt_execute($stmt);
}

function getAllItems()
{
    $conn = dbConnection();

    $sql = "SELECT item_id, item_name, category, item_condition, status, assigned_to
            FROM inventory_item
            ORDER BY item_id ASC";

    return mysqli_query($conn, $sql);
}

function updateItemState($item_id, $item_condition, $status)
{
    $conn = dbConnection();

    $sql = "UPDATE inventory_item
            SET item_condition = ?,
                status         = ?,
                assigned_to    = IF(? = 'In Use', assigned_to, NULL)
            WHERE item_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "sssi", $item_condition, $status, $status, $item_id);

    return mysqli_stmt_execute($stmt);
}

function assignItem($item_id, $assigned_to)
{
    $conn = dbConnection();

    $sql = "UPDATE inventory_item
            SET status = 'In Use', assigned_to = ?
            WHERE item_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "si", $assigned_to, $item_id);

    return mysqli_stmt_execute($stmt);
}

?>
