<?php
/* =====================================================================
   FR11 - set / modify a match time
   Owner: Bibek Howlader (23-54606-3)

   Same shape as Controls/adminControls.php: guard the role, handle the
   POST, then prepare the data the view needs.
   ===================================================================== */

/* ---------------------------------------------------------------------
   ob_start() must come before anything else in this file.

   Models/dbConnect.php (FR1-FR5 module) has 30 blank lines after its
   closing ?> tag. PHP sends those to the browser the moment the file is
   included, and once ANY output has been sent, header() stops working -
   so every redirect below would silently fail with "headers already
   sent". XAMPP's default php.ini hides this because output_buffering is
   on; with it off, even the login page stops redirecting.

   The real fix is to delete those trailing blank lines - raised with the
   FR1-FR5 owner, see docs/MERGE_NOTES.md. This line makes my controllers
   work either way, so my module cannot be broken by someone else's
   php.ini.
   ------------------------------------------------------------------- */
ob_start();

session_start();

require_once "../Models/matchModel.php";


/* Prevents anyone but a moderator or an admin from scheduling. */
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


                        /* FR11 - Cancel a match */
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


                     /* FR11 - Set or change the time */
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
        /* The browser sends datetime-local as 2026-09-15T18:30. Never
           trust that - a POST can be sent from anywhere - so parse it
           and rebuild it in MySQL's format instead of passing it
           straight through. */
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


                    /* Data for Views/matchSchedule.php */

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

    /* rewind so the view can loop over the tabs from the start */
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
