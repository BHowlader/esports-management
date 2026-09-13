<?php
require_once "dbConnect.php";

function getInactiveBannedUsers()
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, uni_id, email_id, role, status
            FROM users
            WHERE status IN ('Inactive', 'Banned')
            ORDER BY u_id DESC";

    return mysqli_query($conn, $sql);
}

function deleteUserAccount($u_id)
{
    $conn = dbConnection();

    $sql = "DELETE FROM users
            WHERE u_id = ?
            AND status IN ('Inactive', 'Banned')";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $u_id);

    return mysqli_stmt_execute($stmt);
}