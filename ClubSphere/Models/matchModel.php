<?php
/* =====================================================================
   FR11 - Moderators shall be able to set and modify match times
          within a tournament.
   Owner: Bibek Howlader (23-54606-3)

   Follows the same pattern as Models/userModels.php - list functions
   return the mysqli_result and the view loops over it with
   mysqli_fetch_assoc, action functions return true or an error string.
   ===================================================================== */

require_once "dbConnect.php";


function getAllTournaments()
{
    $conn = dbConnection();

    $sql = "SELECT tournament_id, title, status
            FROM tournament
            ORDER BY start_date DESC";

    return mysqli_query($conn, $sql);
}


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


/* ---------------------------------------------------------------------
   A team cannot play two matches within two hours of each other.
   Returns the name of the clashing team, or null if the slot is free.
   $ignore_match_id lets a moderator re-save the same match without it
   clashing with itself.
   ------------------------------------------------------------------- */
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

    /* work out WHICH team is double booked, for a useful error message */
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


/* ---------------------------------------------------------------------
   FR11 core - set or modify the time of an existing match.
   A completed match is locked: once a result is verified the schedule
   is part of the record and must not be rewritten.
   Returns true, or an error message for the view to show.
   ------------------------------------------------------------------- */
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


/* Matches a member may report on: scheduled, and their own team is in it. */
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
                 ON tm.user_id = ?
                AND tm.status  = 'Active'
                AND tm.team_id IN (m.team1_id, m.team2_id)
            WHERE m.status = 'Scheduled'
            ORDER BY m.match_time ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


/* FR12 says "Members OR Moderators", so a moderator gets every
   scheduled match rather than only their own team's. */
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


/* Small counters for the moderator dashboard cards. */
function countMatchesByStatus($status)
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total FROM matches WHERE status = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $status);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $row["total"];
}


/* The next few fixtures, for the "Upcoming Matches" panel. */
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
