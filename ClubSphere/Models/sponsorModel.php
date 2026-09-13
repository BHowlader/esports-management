<?php
require_once "dbConnect.php";

function addSponsor($name, $contact, $amount, $start, $end, $notes)
{
    $conn = dbConnection();

    $sql = "INSERT INTO sponsor
            (sponsor_name, contact_info, sponsorship_amount, contract_start, contract_end, notes)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssdsss",
        $name,
        $contact,
        $amount,
        $start,
        $end,
        $notes
    );

    return mysqli_stmt_execute($stmt);
}

function getSponsors()
{
    $conn = dbConnection();

    $sql = "SELECT *
            FROM sponsor
            ORDER BY contract_end ASC";

    return mysqli_query($conn, $sql);
}

function deleteSponsor($id)
{
    $conn = dbConnection();

    $sql = "DELETE FROM sponsor WHERE sponsor_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    return mysqli_stmt_execute($stmt);
}