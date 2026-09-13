<?php
require_once "dbConnect.php";

function addNews($title, $content, $posted_by)
{
    $conn = dbConnection();

    $sql = "INSERT INTO news (title, content, posted_by) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $title,
        $content,
        $posted_by
    );

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }

    return false;
}

function getAllNews()
{
    $conn = dbConnection();

    $sql = "SELECT news.id, news.title, news.content, news.created_at,
                   users.name
            FROM news
            INNER JOIN users ON news.posted_by = users.u_id
            ORDER BY news.created_at DESC";

    $result = mysqli_query($conn, $sql);

    return $result;
}

function deleteNews($id)
{
    $conn = dbConnection();

    $sql = "DELETE FROM news WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }

    return false;
}