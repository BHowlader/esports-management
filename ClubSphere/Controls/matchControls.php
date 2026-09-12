<?php

ob_start();

session_start();

require_once "../Models/matchModel.php";

if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] != "Moderator" && $_SESSION["role"] != "Admin"))
{
    echo "Access Denied!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $match_id      = $_POST["match_id"];
    $tournament_id = $_POST["tournament_id"];

    $back = "../Views/matchSchedule.php?tournament_id=" . $tournament_id;

    if(isset($_POST["cancel"]))
    {
        if(cancelMatch($match_id))
        {
            header("Location:" . $back . "&okMsg=" . urlencode("Match cancelled."));
        }
        else
        {
            header("Location:" . $back . "&errMsg=" . urlencode("Could not cancel that match."));
        }

        exit();
    }

    $match_time = trim($_POST["match_time"]);
    $venue      = trim($_POST["venue"]);

    $errMsg = "";
    $hasErr = false;


    if(empty($match_time))
    {
        $hasErr = true;
        $errMsg = "Match time cannot be empty!";
    }
    else
    {
        $timestamp = strtotime($match_time);

        if($timestamp == false)
        {
            $hasErr = true;
            $errMsg = "That is not a valid date and time!";
        }
        else if($timestamp < time() - 86400)
        {
            $hasErr = true;
            $errMsg = "Match time cannot be more than a day in the past!";
        }
        else
        {
            $match_time = date("Y-m-d H:i:s", $timestamp);
        }
    }


    if(strlen($venue) > 100)
    {
        $hasErr = true;
        $errMsg = "Venue is too long! 100 characters maximum.";
    }


    if($hasErr)
    {
        header("Location:" . $back . "&errMsg=" . urlencode($errMsg));
        exit();
    }


    $result = setMatchTime($match_id, $match_time, $venue, $_SESSION["u_id"]);

    if($result === true)
    {
        header("Location:" . $back . "&okMsg=" .
            urlencode("Match scheduled for " . date("d M Y, g:i A", strtotime($match_time)) . "."));
    }
    else
    {
        header("Location:" . $back . "&errMsg=" . urlencode($result));
    }

    exit();
}

$tournaments = getAllTournaments();

if(isset($_GET["tournament_id"]))
{
    $selectedTournament = $_GET["tournament_id"];
}
else
{
    $firstTournament = mysqli_fetch_assoc($tournaments);

    if($firstTournament)
    {
        $selectedTournament = $firstTournament["tournament_id"];
    }
    else
    {
        $selectedTournament = 0;
    }

    mysqli_data_seek($tournaments, 0);
}

if($selectedTournament > 0)
{
    $matches = getMatchesByTournament($selectedTournament);
}
else
{
    $matches = false;
}

?>
