<?php

require_once "dbConnect.php";

function recomputeLeaderboard($tournament_id, $conn = null)
{
    if($conn == null)
    {
        $conn = dbConnection();

        if(!$conn)
        {
            return false;
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM team_rating WHERE tournament_id = ?");

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    if(!mysqli_stmt_execute($stmt))
    {
        return false;
    }

    $sql = "INSERT INTO team_rating (tournament_id, team_id)
            SELECT tr.tournament_id, tr.team_id
            FROM tournament_register tr
            WHERE tr.tournament_id = ? AND tr.status = 'Approved'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    if(!mysqli_stmt_execute($stmt))
    {
        return false;
    }

    $sql = "INSERT INTO team_rating
                (tournament_id, team_id, played, won, drawn, lost,
                 rounds_for, rounds_against, points)
            SELECT  s.tournament_id,
                    s.team_id,
                    COUNT(*),
                    SUM(s.gf >  s.ga),
                    SUM(s.gf =  s.ga),
                    SUM(s.gf <  s.ga),
                    SUM(s.gf),
                    SUM(s.ga),
                    SUM(CASE WHEN s.gf > s.ga THEN 3
                             WHEN s.gf = s.ga THEN 1
                             ELSE 0 END)
            FROM (
                    SELECT m.tournament_id, m.team1_id AS team_id,
                           r.score_team1 AS gf, r.score_team2 AS ga
                    FROM matches m
                    JOIN match_result r ON r.match_id = m.match_id
                    WHERE r.verification_status = 'Verified'
                      AND m.tournament_id = ?

                    UNION ALL

                    SELECT m.tournament_id, m.team2_id AS team_id,
                           r.score_team2 AS gf, r.score_team1 AS ga
                    FROM matches m
                    JOIN match_result r ON r.match_id = m.match_id
                    WHERE r.verification_status = 'Verified'
                      AND m.tournament_id = ?
                 ) AS s
            GROUP BY s.tournament_id, s.team_id
            ON DUPLICATE KEY UPDATE
                played         = VALUES(played),
                won            = VALUES(won),
                drawn          = VALUES(drawn),
                lost           = VALUES(lost),
                rounds_for     = VALUES(rounds_for),
                rounds_against = VALUES(rounds_against),
                points         = VALUES(points)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $tournament_id, $tournament_id);

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getTournamentStandings($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.team_name, t.game_name,
                   r.played, r.won, r.drawn, r.lost,
                   r.rounds_for, r.rounds_against,
                   (r.rounds_for - r.rounds_against) AS round_diff,
                   r.points
            FROM team_rating r
            JOIN team t ON t.team_id = r.team_id
            WHERE r.tournament_id = ?
            ORDER BY r.points DESC, round_diff DESC, r.rounds_for DESC, t.team_name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

function getOverallStandings()
{
    $conn = dbConnection();

    $sql = "SELECT t.team_name, t.game_name,
                   SUM(r.played) AS played,
                   SUM(r.won)    AS won,
                   SUM(r.drawn)  AS drawn,
                   SUM(r.lost)   AS lost,
                   SUM(r.rounds_for)     AS rounds_for,
                   SUM(r.rounds_against) AS rounds_against,
                   (SUM(r.rounds_for) - SUM(r.rounds_against)) AS round_diff,
                   SUM(r.points) AS points
            FROM team_rating r
            JOIN team t ON t.team_id = r.team_id
            GROUP BY r.team_id, t.team_name, t.game_name
            ORDER BY points DESC, round_diff DESC, rounds_for DESC, t.team_name ASC";

    return mysqli_query($conn, $sql);
}

function getUserTeam($user_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.team_id, t.team_name, t.game_name
            FROM team_member tm
            JOIN team t ON t.team_id = tm.team_id
            WHERE tm.u_id = ? AND tm.status = 'Accepted'
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if($row)
    {
        return $row;
    }
    else
    {
        return null;
    }
}


function getRecentForm($team_id, $limit)
{
    $conn = dbConnection();

    $sql = "SELECT CASE
                     WHEN m.winner_id IS NULL              THEN 'D'
                     WHEN m.winner_id = ?                  THEN 'W'
                     ELSE 'L'
                   END AS outcome,
                   m.match_time
            FROM matches m
            JOIN match_result r ON r.match_id = m.match_id
            WHERE m.status = 'Completed'
              AND r.verification_status = 'Verified'
              AND (m.team1_id = ? OR m.team2_id = ?)
            ORDER BY m.match_time DESC
            LIMIT ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "iiii", $team_id, $team_id, $team_id, $limit);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


function countTeamWins($team_id)
{
    $conn = dbConnection();

    $sql = "SELECT COALESCE(SUM(won), 0) AS wins FROM team_rating WHERE team_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $team_id);

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $row["wins"];
}

?>
