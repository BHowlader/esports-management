<?php

require_once "dbConnect.php";
require_once "leaderboardModel.php";

function canSubmitForMatch($match_id, $user_id, $role)
{
    if($role == "Moderator" || $role == "Admin")
    {
        return true;
    }

    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total
            FROM matches m
            JOIN team_member tm
                 ON tm.team_id IN (m.team1_id, m.team2_id)
                AND tm.user_id = ?
                AND tm.status  = 'Active'
            WHERE m.match_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $user_id, $match_id);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if($row && $row["total"] > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function submitResult($match_id, $user_id, $score_team1, $score_team2, $screenshot)
{
    $conn = dbConnection();

    $stmt = mysqli_prepare($conn, "SELECT status FROM matches WHERE match_id = ?");

    mysqli_stmt_bind_param($stmt, "i", $match_id);

    mysqli_stmt_execute($stmt);

    $match = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if(!$match)
    {
        return "That match does not exist.";
    }

    if($match["status"] == "Completed")
    {
        return "This match has already been finalized by a moderator.";
    }

    if($match["status"] != "Scheduled")
    {
        return "A result can only be submitted after the match has been scheduled.";
    }

    $sql = "SELECT COUNT(*) AS total FROM match_result
            WHERE match_id = ? AND verification_status IN ('Pending','Verified')";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $match_id);

    mysqli_stmt_execute($stmt);

    $open = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if($open && $open["total"] > 0)
    {
        return "A result for this match is already waiting for a moderator, or has been verified.";
    }

    $sql = "INSERT INTO match_result
                (match_id, submitted_by, score_team1, score_team2, screenshot)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iiiis",
        $match_id,
        $user_id,
        $score_team1,
        $score_team2,
        $screenshot
    );

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return "Could not save the result.";
    }
}

function getPendingResults()
{
    $conn = dbConnection();

    $sql = "SELECT r.result_id, r.match_id, r.score_team1, r.score_team2,
                   r.screenshot, r.submitted_at,
                   u.name AS submitted_by_name,
                   t1.team_name AS team1_name,
                   t2.team_name AS team2_name,
                   m.match_time, m.tournament_id,
                   tr.title AS tournament_title
            FROM match_result r
            JOIN matches m     ON m.match_id  = r.match_id
            JOIN team t1       ON t1.team_id  = m.team1_id
            JOIN team t2       ON t2.team_id  = m.team2_id
            JOIN users u       ON u.u_id      = r.submitted_by
            JOIN tournament tr ON tr.tournament_id = m.tournament_id
            WHERE r.verification_status = 'Pending'
            ORDER BY r.submitted_at ASC";

    return mysqli_query($conn, $sql);
}


function countPendingResults()
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total FROM match_result WHERE verification_status = 'Pending'";

    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    return $row["total"];
}


function getResultHistory($limit)
{
    $conn = dbConnection();

    $sql = "SELECT r.result_id, r.score_team1, r.score_team2,
                   r.verification_status, r.verified_at, r.remarks,
                   t1.team_name AS team1_name,
                   t2.team_name AS team2_name,
                   w.team_name  AS winner_name,
                   su.name AS submitted_by_name,
                   vu.name AS verified_by_name
            FROM match_result r
            JOIN matches m ON m.match_id = r.match_id
            JOIN team t1   ON t1.team_id = m.team1_id
            JOIN team t2   ON t2.team_id = m.team2_id
            LEFT JOIN team w   ON w.team_id = m.winner_id
            JOIN users su      ON su.u_id  = r.submitted_by
            LEFT JOIN users vu ON vu.u_id  = r.verified_by
            WHERE r.verification_status <> 'Pending'
            ORDER BY r.submitted_at DESC
            LIMIT ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $limit);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

function verifyResult($result_id, $moderator_id, $remarks)
{
    $conn = dbConnection();

    mysqli_begin_transaction($conn);

    $sql = "SELECT r.result_id, r.match_id, r.score_team1, r.score_team2,
                   r.verification_status,
                   m.team1_id, m.team2_id, m.tournament_id, m.status AS match_status
            FROM match_result r
            JOIN matches m ON m.match_id = r.match_id
            WHERE r.result_id = ?
            FOR UPDATE";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $result_id);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if(!$row)
    {
        mysqli_rollback($conn);
        return "That submission no longer exists.";
    }

    if($row["verification_status"] != "Pending")
    {
        mysqli_rollback($conn);
        return "This submission was already " . strtolower($row["verification_status"]) . ".";
    }

    if($row["match_status"] == "Completed")
    {
        mysqli_rollback($conn);
        return "This match has already been finalized.";
    }

    $sql = "UPDATE match_result
            SET verification_status = 'Verified',
                verified_by = ?, verified_at = NOW(), remarks = ?
            WHERE result_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "isi", $moderator_id, $remarks, $result_id);

    if(!mysqli_stmt_execute($stmt))
    {
        mysqli_rollback($conn);
        return "Could not update the submission.";
    }

    $sql = "UPDATE match_result
            SET verification_status = 'Rejected',
                verified_by = ?, verified_at = NOW(),
                remarks = 'Superseded by another verified submission'
            WHERE match_id = ? AND result_id <> ? AND verification_status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "iii", $moderator_id, $row["match_id"], $result_id);

    mysqli_stmt_execute($stmt);

    $winner_id = null;

    if($row["score_team1"] > $row["score_team2"])
    {
        $winner_id = $row["team1_id"];
    }
    else if($row["score_team2"] > $row["score_team1"])
    {
        $winner_id = $row["team2_id"];
    }

    $sql = "UPDATE matches SET winner_id = ?, status = 'Completed' WHERE match_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $winner_id, $row["match_id"]);

    if(!mysqli_stmt_execute($stmt))
    {
        mysqli_rollback($conn);
        return "Could not finalize the match.";
    }

    if(!recomputeLeaderboard($row["tournament_id"], $conn))
    {
        mysqli_rollback($conn);
        return "Could not update the leaderboard.";
    }

    mysqli_commit($conn);

    return true;
}

function rejectResult($result_id, $moderator_id, $remarks)
{
    $conn = dbConnection();

    $sql = "UPDATE match_result
            SET verification_status = 'Rejected',
                verified_by = ?, verified_at = NOW(), remarks = ?
            WHERE result_id = ? AND verification_status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "isi", $moderator_id, $remarks, $result_id);

    if(!mysqli_stmt_execute($stmt))
    {
        return "Could not reject the submission.";
    }

    if(mysqli_stmt_affected_rows($stmt) == 0)
    {
        return "That submission is no longer pending.";
    }

    return true;
}

?>
