<?php

require_once "dbConnect.php";
require_once "tournamentModels.php";

/*
    bracketModels.php

    FR10 - The system shall automatically generate knockout-style
           tournament brackets based on registered teams.

    How the generation works
    ------------------------
    1. read every approved team of the tournament, registration order
       is the seed order
    2. round the team count up to the next power of two (4, 8, 16, ...)
       so the elimination tree is complete
    3. place the seeds with the standard 1-vs-last pairing, the empty
       slots become byes
    4. write round 1 with the real pairings, and every later round as
       empty placeholder matches
    5. a team that drew a bye is advanced into round 2 straight away
*/




/* standard knockout seeding order, for size 8 it gives 1,8,5,4,3,6,7,2 */

function seedOrder($size)
{
    $order = array(1, 2);

    while(count($order) < $size)
    {
        $next = array();

        $sum = count($order) * 2 + 1;

        foreach($order as $seed)
        {
            $next[] = $seed;
            $next[] = $sum - $seed;
        }

        $order = $next;
    }

    return $order;
}


function nextPowerOfTwo($n)
{
    $size = 2;

    while($size < $n)
    {
        $size = $size * 2;
    }

    return $size;
}


function bracketExists($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT match_id FROM matches WHERE tournament_id = ? LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

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


function clearBracket($tournament_id)
{
    $conn = dbConnection();

    $sql = "DELETE FROM matches WHERE tournament_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    return mysqli_stmt_execute($stmt);
}




                    /* FR10 - the generator */

function generateBracket($tournament_id)
{
    $conn = dbConnection();

    $teams = getRegisteredTeams($tournament_id);

    $teamCount = count($teams);

    if($teamCount < 2)
    {
        return "At least 2 registered teams are needed to generate a bracket.";
    }

    clearBracket($tournament_id);

    $size = nextPowerOfTwo($teamCount);

    $order = seedOrder($size);

    $totalRounds = (int)round(log($size, 2));


    /* bracket position -> team id, null means a bye */

    $slots = array();

    foreach($order as $seed)
    {
        if($seed <= $teamCount)
        {
            $slots[] = $teams[$seed - 1]["team_id"];
        }
        else
        {
            $slots[] = null;
        }
    }


    $sql = "INSERT INTO matches (tournament_id, round_no, slot_no, team1_id, team2_id, winner_id, status)
            VALUES (?,?,?,?,?,?,?)";

    $stmt = mysqli_prepare($conn, $sql);


                    /* round 1 */

    $byeWinners = array();

    $slot_no = 1;

    for($i = 0; $i < $size; $i = $i + 2)
    {
        $team1 = $slots[$i];
        $team2 = $slots[$i + 1];
        $winner = null;
        $status = "Pending";
        $round = 1;

        if($team1 != null && $team2 == null)
        {
            $winner = $team1;
            $status = "Bye";
        }
        else if($team1 == null && $team2 != null)
        {
            $winner = $team2;
            $status = "Bye";
        }

        mysqli_stmt_bind_param(
            $stmt,
            "iiiiiis",
            $tournament_id,
            $round,
            $slot_no,
            $team1,
            $team2,
            $winner,
            $status
        );

        mysqli_stmt_execute($stmt);

        if($winner != null)
        {
            $byeWinners[$slot_no] = $winner;
        }

        $slot_no++;
    }


        /* the later rounds start empty, winners move up into them */

    $matchesInRound = $size / 2;

    for($round = 2; $round <= $totalRounds; $round++)
    {
        $matchesInRound = $matchesInRound / 2;

        for($slot = 1; $slot <= $matchesInRound; $slot++)
        {
            $team1 = null;
            $team2 = null;
            $winner = null;
            $status = "Pending";

            mysqli_stmt_bind_param(
                $stmt,
                "iiiiiis",
                $tournament_id,
                $round,
                $slot,
                $team1,
                $team2,
                $winner,
                $status
            );

            mysqli_stmt_execute($stmt);
        }
    }


        /* push every bye winner of round 1 into round 2 */

    foreach($byeWinners as $fromSlot => $team_id)
    {
        placeTeamInNextRound($tournament_id, 1, $fromSlot, $team_id, $totalRounds);
    }

    updateTournamentStatus($tournament_id, "Ongoing");

    return "";
}


/* put the winner of (round, slot) into the right half of the parent match */

function placeTeamInNextRound($tournament_id, $round, $slot, $team_id, $totalRounds, $conn = null)
{
    if($round >= $totalRounds)
    {
        return true;        /* the final has no parent match */
    }

    if($conn == null)
    {
        $conn = dbConnection();
    }

    $nextRound = $round + 1;

    $nextSlot = (int)ceil($slot / 2);

    if($slot % 2 == 1)
    {
        $sql = "UPDATE matches SET team1_id = ?
                WHERE tournament_id = ? AND round_no = ? AND slot_no = ?";
    }
    else
    {
        $sql = "UPDATE matches SET team2_id = ?
                WHERE tournament_id = ? AND round_no = ? AND slot_no = ?";
    }

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "iiii", $team_id, $tournament_id, $nextRound, $nextSlot);

    return mysqli_stmt_execute($stmt);
}


/* used from the bracket page so the tree can actually be played out */

function setMatchWinner($match_id, $winner_id)
{
    $conn = dbConnection();

    $sql = "SELECT * FROM matches WHERE match_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $match_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $match = mysqli_fetch_assoc($result);

    if(!$match)
    {
        return "Match not found.";
    }

    if($winner_id != $match["team1_id"] && $winner_id != $match["team2_id"])
    {
        return "The winner must be one of the two teams of this match.";
    }

    $sql2 = "UPDATE matches SET winner_id = ?, status = 'Completed' WHERE match_id = ?";

    $stmt2 = mysqli_prepare($conn, $sql2);

    mysqli_stmt_bind_param($stmt2, "ii", $winner_id, $match_id);

    mysqli_stmt_execute($stmt2);

    $totalRounds = getTotalRounds($match["tournament_id"]);

    placeTeamInNextRound(
        $match["tournament_id"],
        $match["round_no"],
        $match["slot_no"],
        $winner_id,
        $totalRounds
    );

    if($match["round_no"] == $totalRounds)
    {
        updateTournamentStatus($match["tournament_id"], "Completed");
    }

    return "";
}


function getTotalRounds($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT MAX(round_no) AS rounds FROM matches WHERE tournament_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    if($row["rounds"] == null)
    {
        return 0;
    }

    return (int)$row["rounds"];
}


/* every match grouped round by round, for the bracket screen */

function getBracket($tournament_id)
{
    $conn = dbConnection();

    $sql = "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name
            FROM matches m
            LEFT JOIN team t1 ON t1.team_id = m.team1_id
            LEFT JOIN team t2 ON t2.team_id = m.team2_id
            WHERE m.tournament_id = ?
            ORDER BY m.round_no ASC, m.slot_no ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $tournament_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $rounds = array();

    while($row = mysqli_fetch_assoc($result))
    {
        $rounds[$row["round_no"]][] = $row;
    }

    return $rounds;
}


function roundName($round, $totalRounds)
{
    $fromEnd = $totalRounds - $round;

    if($fromEnd == 0)
    {
        return "Final";
    }
    else if($fromEnd == 1)
    {
        return "Semi Final";
    }
    else if($fromEnd == 2)
    {
        return "Quarter Final";
    }
    else
    {
        return "Round " . $round;
    }
}

?>
