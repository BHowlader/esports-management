<?php

require_once "dbConnect.php";

/*
    tournamentModels.php

    FR8 - Admins shall be able to create tournaments with specific rules,
          dates, and game titles.
    FR9 - Members shall be able to register their teams for upcoming
          tournaments.
*/




                    /* FR8 - create a tournament */

function createTournament($title, $game_title, $rules, $description,
                          $prize, $region, $start_date, $end_date, $created_by)
{
    $conn = dbConnection();

    $sql = "INSERT INTO tournament
            (title, game_title, rules, description, prize, region, status,
             start_date, end_date, created_by)
            VALUES (?,?,?,?,?,?, 'Registration Open', ?,?,?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssi",
        $title,
        $game_title,
        $rules,
        $description,
        $prize,
        $region,
        $start_date,
        $end_date,
        $created_by
    );

    if(mysqli_stmt_execute($stmt))
    {
        return mysqli_insert_id($conn);
    }
    else
    {
        return 0;
    }
}


function getAllTournaments()
{
    $conn = dbConnection();

    $sql = "SELECT t.*, u.name AS organizer,
                   (SELECT COUNT(*) FROM tournament_register r
                    WHERE r.tournament_id = t.tournament_id AND r.status = 'Approved') AS team_count
            FROM tournament t
            JOIN users u ON u.u_id = t.created_by
            ORDER BY t.start_date ASC";

    return mysqli_query($conn, $sql);
}


function getTournamentById($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.*, u.name AS organizer
            FROM tournament t
            JOIN users u ON u.u_id = t.created_by
            WHERE t.tournament_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}


function updateTournamentStatus($tournament_id, $status)
{
    $conn = dbConnection();

    $sql = "UPDATE tournament SET status = ? WHERE tournament_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "si", $status, $tournament_id);

    return mysqli_stmt_execute($stmt);
}


function getTotalTournaments()
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total FROM tournament";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    return $row["total"];
}




                    /* FR9 - register a team */

function registerTeamForTournament($tournament_id, $team_id)
{
    $conn = dbConnection();

    $sql = "INSERT INTO tournament_register (tournament_id, team_id, status)
            VALUES (?,?,'Approved')";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $tournament_id, $team_id);

    return mysqli_stmt_execute($stmt);
}


function isTeamRegistered($tournament_id, $team_id)
{
    $conn = dbConnection();

    $sql = "SELECT registration_id FROM tournament_register
            WHERE tournament_id = ? AND team_id = ? AND status <> 'Rejected'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $tournament_id, $team_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}


/* returns an array, because the bracket generator needs to seed by order */

function getRegisteredTeams($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT r.registration_id, r.status, r.registered_at,
                   t.team_id, t.team_name, t.game_name, u.name AS captain_name
            FROM tournament_register r
            JOIN team t  ON t.team_id = r.team_id
            JOIN users u ON u.u_id = t.captain_id
            WHERE r.tournament_id = ? AND r.status = 'Approved'
            ORDER BY r.registered_at ASC, r.registration_id ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $teams = array();

    while($row = mysqli_fetch_assoc($result))
    {
        $teams[] = $row;
    }

    return $teams;
}


/* tournaments this member has entered with a team they captain */

function getMyRegistrations($u_id)
{
    $conn = dbConnection();

    $sql = "SELECT r.registration_id, r.status, t.tournament_id, t.title,
                   t.game_title, t.start_date, tm.team_name
            FROM tournament_register r
            JOIN team tm      ON tm.team_id = r.team_id
            JOIN tournament t ON t.tournament_id = r.tournament_id
            WHERE tm.captain_id = ?
            ORDER BY t.start_date ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $u_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

?>
