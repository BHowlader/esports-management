<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}


require_once "../Models/tournamentModels.php";
require_once "../Models/teamModels.php";
require_once "../Models/bracketModels.php";


if(!isset($_SESSION["u_id"]))
{
    header("Location: ../Views/login.php?message=" . urlencode("Please login first!"));
    exit();
}


$u_id = $_SESSION["u_id"];
$role = $_SESSION["role"];



if($_SERVER["REQUEST_METHOD"] == "POST")
{


                   /* FR8 - Admin creates a tournament */

    if(isset($_POST["createTournament"]))
    {
        if($role != "Admin")
        {
            echo "Access Denied!";
            exit();
        }

        $title = trim($_POST["title"]);
        $game_title = trim($_POST["game_title"]);
        $rules = trim($_POST["rules"]);
        $description = trim($_POST["description"]);
        $prize = trim($_POST["prize"]);
        $region = trim($_POST["region"]);
        $start_date = $_POST["start_date"];
        $end_date = $_POST["end_date"];

        $titleErr = "";
        $game_titleErr = "";
        $rulesErr = "";
        $dateErr = "";

        $hasErr = false;


        if(empty($title))
        {
            $hasErr = true;
            $titleErr = "Title cannot be empty!";
        }

        if(empty($game_title))
        {
            $hasErr = true;
            $game_titleErr = "Please select a game title!";
        }

        if(empty($rules))
        {
            $hasErr = true;
            $rulesErr = "Rules cannot be empty!";
        }

        if(empty($start_date) || empty($end_date))
        {
            $hasErr = true;
            $dateErr = "Both start and end date are required!";
        }
        else if(strtotime($end_date) < strtotime($start_date))
        {
            $hasErr = true;
            $dateErr = "End date cannot be before the start date!";
        }


        if($hasErr)
        {
            header("Location: ../Views/eventManagement.php?title=" . urlencode($title)
                . "&description=" . urlencode($description)
                . "&rules=" . urlencode($rules)
                . "&prize=" . urlencode($prize)
                . "&titleErr=" . urlencode($titleErr)
                . "&game_titleErr=" . urlencode($game_titleErr)
                . "&rulesErr=" . urlencode($rulesErr)
                . "&dateErr=" . urlencode($dateErr));

            exit();
        }


        $tournament_id = createTournament($title, $game_title, $rules, $description,
                                          $prize, $region, $start_date, $end_date, $u_id);

        if($tournament_id > 0)
        {
            header("Location: ../Views/eventManagement.php?message="
                . urlencode("Tournament created successfully!"));
        }
        else
        {
            header("Location: ../Views/eventManagement.php?message="
                . urlencode("Tournament could not be created!"));
        }

        exit();
    }



                   /* FR9 - Member registers their team */

    else if(isset($_POST["registerTeam"]))
    {
        $tournament_id = $_POST["tournament_id"];
        $team_id = $_POST["team_id"];

        $tournament = getTournamentById($tournament_id);

        if(!$tournament)
        {
            header("Location: ../Views/tournaments.php?message=" . urlencode("Tournament not found!"));
            exit();
        }

        if(empty($team_id))
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Please select one of your teams!"));
            exit();
        }

        $team = getTeamById($team_id);

        /* only the captain may enter a team */

        if(!$team || $team["captain_id"] != $u_id)
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("You can only register a team that you captain!"));
            exit();
        }

        if($tournament["status"] != "Registration Open")
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Registration is closed for this tournament!"));
            exit();
        }

        if($team["game_name"] != $tournament["game_title"])
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("This team does not play " . $tournament["game_title"] . "!"));
            exit();
        }

        if(isTeamRegistered($tournament_id, $team_id))
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("This team is already registered!"));
            exit();
        }

        if(registerTeamForTournament($tournament_id, $team_id))
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode($team["team_name"] . " is registered for this tournament!"));
        }
        else
        {
            header("Location: ../Views/tournamentDetails.php?tournament_id=" . $tournament_id
                . "&message=" . urlencode("Registration failed!"));
        }

        exit();
    }



                   /* FR8 - Admin opens or closes registration */

    else if(isset($_POST["changeStatus"]))
    {
        if($role != "Admin")
        {
            echo "Access Denied!";
            exit();
        }

        $tournament_id = $_POST["tournament_id"];
        $status = $_POST["status"];

        if(updateTournamentStatus($tournament_id, $status))
        {
            header("Location: ../Views/eventManagement.php?message="
                . urlencode("Tournament status updated!"));
        }
        else
        {
            header("Location: ../Views/eventManagement.php?message="
                . urlencode("Status could not be updated!"));
        }

        exit();
    }
}




        /* data the tournament pages display */

$allTournaments = getAllTournaments();

$myRegistrations = getMyRegistrations($u_id);

$totalTournaments = getTotalTournaments();


$tournament = null;
$registeredTeams = array();
$myCaptainTeams = array();

if(isset($_GET["tournament_id"]))
{
    $tournament = getTournamentById($_GET["tournament_id"]);

    if($tournament)
    {
        $registeredTeams = getRegisteredTeams($_GET["tournament_id"]);


        /* only the teams this member captains, that play the right game
           and that are not entered yet, can still be registered (FR9) */

        $captainResult = getTeamsCaptainedBy($u_id);

        while($row = mysqli_fetch_assoc($captainResult))
        {
            if($row["game_name"] == $tournament["game_title"]
               && !isTeamRegistered($_GET["tournament_id"], $row["team_id"]))
            {
                $myCaptainTeams[] = $row;
            }
        }
    }
}

?>
