<?php
require_once "dbConnect.php";

function createAlert($user_id, $title, $message, $alert_type, $related_id = null)
{
    $conn = dbConnection();

    $sql = "INSERT INTO alerts (user_id, title, message, alert_type, related_id)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "isssi",
        $user_id,
        $title,
        $message,
        $alert_type,
        $related_id
    );

    return mysqli_stmt_execute($stmt);
}

function getUserAlerts($user_id)
{
    $conn = dbConnection();

    $sql = "SELECT alert_id, title, message, alert_type, related_id, is_read, created_at
            FROM alerts
            WHERE user_id = ? OR user_id IS NULL
            ORDER BY created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

function markAlertRead($alert_id, $user_id)
{
    $conn = dbConnection();

    $sql = "UPDATE alerts
            SET is_read = 1
            WHERE alert_id = ?
            AND (user_id = ? OR user_id IS NULL)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $alert_id, $user_id);

    return mysqli_stmt_execute($stmt);
}

function getUpcomingMatches()
{
    $conn = dbConnection();

    $sql = "SELECT m.match_id, m.match_time, m.venue,
                   t1.team_name AS team1,
                   t2.team_name AS team2,
                   tr.title AS tournament
            FROM matches m
            LEFT JOIN team t1 ON m.team1_id = t1.team_id
            LEFT JOIN team t2 ON m.team2_id = t2.team_id
            INNER JOIN tournament tr ON m.tournament_id = tr.tournament_id
            WHERE m.match_time IS NOT NULL
            AND m.match_time >= NOW()
            ORDER BY m.match_time ASC
            LIMIT 20";

    return mysqli_query($conn, $sql);
}