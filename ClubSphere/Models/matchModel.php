<?php

require_once "dbConnect.php";


function getMatchesByTournament($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT m.match_id, m.round_no, m.match_time, m.venue, m.status,
                   m.team1_id, m.team2_id, m.winner_id,
                   t1.team_name AS team1_name,
                   t2.team_name AS team2_name,
                   w.team_name  AS winner_name
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            LEFT JOIN team w ON w.team_id = m.winner_id
            WHERE m.tournament_id = ?
            ORDER BY m.round_no ASC, m.match_time IS NULL DESC, m.match_time ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


function getMatchById($match_id)
{
    $conn = dbConnection();

    $sql = "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                   tr.title AS tournament_title
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            JOIN tournament tr ON tr.tournament_id = m.tournament_id
            WHERE m.match_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $match_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    if($row)
    {
        return $row;
    }
    else
    {
        return null;
    }
}

function findScheduleClash($tournament_id, $team1_id, $team2_id, $match_time, $ignore_match_id)
{
    $conn = dbConnection();

    $sql = "SELECT t1.team_name AS team1_name, t2.team_name AS team2_name,
                   m.team1_id, m.team2_id
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            WHERE m.tournament_id = ?
              AND m.match_id <> ?
              AND m.status <> 'Cancelled'
              AND m.match_time IS NOT NULL
              AND ABS(TIMESTAMPDIFF(MINUTE, m.match_time, ?)) < 120
              AND (m.team1_id IN (?, ?) OR m.team2_id IN (?, ?))
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iisiiii",
        $tournament_id,
        $ignore_match_id,
        $match_time,
        $team1_id,
        $team2_id,
        $team1_id,
        $team2_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    if(!$row)
    {
        return null;
    }

    if($row["team1_id"] == $team1_id || $row["team2_id"] == $team1_id)
    {
        if($row["team1_id"] == $team1_id)
        {
            return $row["team1_name"];
        }
        else
        {
            return $row["team2_name"];
        }
    }

    if($row["team1_id"] == $team2_id)
    {
        return $row["team1_name"];
    }
    else
    {
        return $row["team2_name"];
    }
}

function setMatchTime($match_id, $match_time, $venue, $moderator_id)
{
    $match = getMatchById($match_id);

    if($match == null)
    {
        return "Match not found.";
    }

    if($match["status"] == "Completed")
    {
        return "This match is already completed. Its schedule can no longer be changed.";
    }

    if($match["status"] == "Cancelled")
    {
        return "This match was cancelled.";
    }

    $clash = findScheduleClash(
        $match["tournament_id"],
        $match["team1_id"],
        $match["team2_id"],
        $match_time,
        $match_id
    );

    if($clash != null)
    {
        return "Time clash: " . $clash . " already has another match within 2 hours of that slot.";
    }

    $conn = dbConnection();

    $sql = "UPDATE matches
            SET match_time = ?, venue = ?, status = 'Scheduled', scheduled_by = ?
            WHERE match_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ssii", $match_time, $venue, $moderator_id, $match_id);

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return "Could not save the schedule.";
    }
}


function cancelMatch($match_id)
{
    $conn = dbConnection();

    $sql = "UPDATE matches
            SET status = 'Cancelled'
            WHERE match_id = ? AND status <> 'Completed'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $match_id);

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getSubmittableMatchesForUser($user_id)
{
    $conn = dbConnection();

    $sql = "SELECT DISTINCT m.match_id, m.match_time, m.status,
                   t1.team_name AS team1_name, t2.team_name AS team2_name,
                   tr.title AS tournament_title,
                   (SELECT COUNT(*) FROM match_result r
                     WHERE r.match_id = m.match_id
                       AND r.verification_status IN ('Pending','Verified')) AS open_submissions
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            JOIN tournament tr ON tr.tournament_id = m.tournament_id
            JOIN team_member tm
                 ON tm.u_id = ?
                AND tm.status  = 'Accepted'
                AND tm.team_id IN (m.team1_id, m.team2_id)
            WHERE m.status = 'Scheduled'
            ORDER BY m.match_time ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

function getAllScheduledMatches()
{
    $conn = dbConnection();

    $sql = "SELECT m.match_id, m.match_time, m.status,
                   t1.team_name AS team1_name, t2.team_name AS team2_name,
                   tr.title AS tournament_title,
                   (SELECT COUNT(*) FROM match_result r
                     WHERE r.match_id = m.match_id
                       AND r.verification_status IN ('Pending','Verified')) AS open_submissions
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            JOIN tournament tr ON tr.tournament_id = m.tournament_id
            WHERE m.status = 'Scheduled'
            ORDER BY m.match_time ASC";

    return mysqli_query($conn, $sql);
}

function countMatchesByStatus($status)
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total FROM matches
            WHERE status = ? AND team1_id IS NOT NULL AND team2_id IS NOT NULL";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $status);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $row["total"];
}

function getUpcomingMatches($limit)
{
    $conn = dbConnection();

    $sql = "SELECT m.match_id, m.match_time, m.venue,
                   t1.team_name AS team1_name, t2.team_name AS team2_name,
                   tr.title AS tournament_title
            FROM matches m
            JOIN team t1 ON t1.team_id = m.team1_id
            JOIN team t2 ON t2.team_id = m.team2_id
            JOIN tournament tr ON tr.tournament_id = m.tournament_id
            WHERE m.status = 'Scheduled' AND m.match_time IS NOT NULL
            ORDER BY m.match_time ASC
            LIMIT ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $limit);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

?>
