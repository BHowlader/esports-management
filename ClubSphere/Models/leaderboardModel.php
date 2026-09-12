<?php
/* =====================================================================
   FR14 - The system shall automatically update rankings and
          leaderboards based on match outcomes.
   Owner: Bibek Howlader (23-54606-3)

   ---------------------------------------------------------------------
   DESIGN DECISION (remember this one for the defense)

   There are two ways to keep a leaderboard current.

     (a) INCREMENTAL - when a result is verified, run
         "UPDATE team_rating SET won = won + 1 ...".
         Fast, but it drifts. If a moderator later corrects a wrong
         result, or the Verify button is double clicked, the table is
         silently wrong forever and nothing detects it.

     (b) RECOMPUTE - delete the rows for that tournament and rebuild
         them from every verified match_result.
         This is idempotent: running it once or five times gives the
         same answer, and every number on the leaderboard can be traced
         back to the verified results behind it.

   This project uses (b). A university club plays tens of matches, not
   millions, so the cost is irrelevant and correctness wins. It runs
   inside the same database transaction as the verification in FR13, so
   the result and the standings can never disagree.

   Points: win = 3, draw = 1, loss = 0.
   ===================================================================== */

require_once "dbConnect.php";


/* $conn is passed in by FR13 so this joins the caller's transaction.
   Called with nothing, it opens its own connection. */
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

    /* 1. clear the old standings for THIS tournament only */
    $stmt = mysqli_prepare($conn, "DELETE FROM team_rating WHERE tournament_id = ?");

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    if(!mysqli_stmt_execute($stmt))
    {
        return false;
    }

    /* 2. every approved team starts on the board with zeros, so a team
          that has not played yet is still visible instead of missing */
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

    /* 3. rebuild the numbers from the verified results.

          The inner UNION ALL turns each match into TWO rows, one from
          each team's point of view, with "rounds for" and "rounds
          against" swapped. That makes the aggregate a plain GROUP BY
          instead of a pile of CASE statements.

          ON DUPLICATE KEY UPDATE fires because of the unique key
          (tournament_id, team_id) created in step 2. */
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


/* Standings for one tournament, best team first.
   Tie break: points, then round difference, then rounds scored, then
   team name - so the order is stable between page loads. */
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


/* Club wide standings: the same rows summed across every tournament. */
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


/* ---------------------------------------------------------------------
   Helpers the member dashboard can use (FR1-FR5 module is welcome to
   call these - they only read).

   getUserTeam      : which team this member plays for
   getRecentForm    : their team's last few results as W / D / L,
                      which is exactly what the coloured boxes on
                      memberDashboard.php are for
   ------------------------------------------------------------------- */
function getUserTeam($user_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.team_id, t.team_name, t.game_name
            FROM team_member tm
            JOIN team t ON t.team_id = tm.team_id
            WHERE tm.user_id = ? AND tm.status = 'Active'
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
