<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}


require_once "../Models/bracketModels.php";
require_once "../Models/tournamentModels.php";


if(!isset($_SESSION["u_id"]))
{
    header("Location: ../Views/login.php?message=" . urlencode("Please login first!"));
    exit();
}


$role = $_SESSION["role"];

/* Admins and Moderators run the bracket, everybody else can only read it */

$canManage = false;

if($role == "Admin" || $role == "Moderator")
{
    $canManage = true;
}



if($_SERVER["REQUEST_METHOD"] == "POST")
{


                   /* FR10 - Generate the knockout bracket */

    if(isset($_POST["generateBracket"]))
    {
        if(!$canManage)
        {
            echo "Access Denied!";
            exit();
        }

        $tournament_id = $_POST["tournament_id"];

        $error = generateBracket($tournament_id);

        if($error == "")
        {
            header("Location: ../Views/bracket.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Knockout bracket generated!"));
        }
        else
        {
            header("Location: ../Views/bracket.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode($error));
        }

        exit();
    }



                   /* FR10 - Record a winner so the bracket moves on */

    else if(isset($_POST["setWinner"]))
    {
        if(!$canManage)
        {
            echo "Access Denied!";
            exit();
        }

        $tournament_id = $_POST["tournament_id"];
        $match_id = $_POST["match_id"];
        $winner_id = $_POST["winner_id"];

        if(empty($winner_id))
        {
            header("Location: ../Views/bracket.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Select the winning team first!"));
            exit();
        }

        $error = setMatchWinner($match_id, $winner_id);

        if($error == "")
        {
            header("Location: ../Views/bracket.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Winner recorded, the bracket moved forward!"));
        }
        else
        {
            header("Location: ../Views/bracket.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode($error));
        }

        exit();
    }
}




        /* data the bracket page displays */

$tournament = null;
$registeredTeams = array();
$rounds = array();
$totalRounds = 0;
$hasBracket = false;
$champion = "";

if(isset($_GET["tournament_id"]))
{
    $tournament_id = $_GET["tournament_id"];

    $tournament = getTournamentById($tournament_id);

    if($tournament)
    {
        $registeredTeams = getRegisteredTeams($tournament_id);

        $rounds = getBracket($tournament_id);

        $totalRounds = getTotalRounds($tournament_id);

        $hasBracket = bracketExists($tournament_id);


        /* the champion is the winner of the last round */

        if($totalRounds > 0 && isset($rounds[$totalRounds]))
        {
            $final = $rounds[$totalRounds][0];

            if($final["winner_id"] != null)
            {
                if($final["winner_id"] == $final["team1_id"])
                {
                    $champion = $final["team1_name"];
                }
                else
                {
                    $champion = $final["team2_name"];
                }
            }
        }
    }
}

?>
