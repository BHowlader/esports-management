<?php
/* =====================================================================
   FR12 - upload a match result and a supporting screenshot
   Owner: Bibek Howlader (23-54606-3)

   ---------------------------------------------------------------------
   File upload is the most dangerous form in this whole project: an
   unchecked upload folder is how a PHP shell gets onto a server. The
   checks below run in this order:

     1. the upload actually succeeded
     2. it is not bigger than 2 MB
     3. its REAL type is an image - read from the file's own bytes with
        finfo, NOT from $_FILES["type"], which the browser sends and an
        attacker can simply lie about
     4. it is saved under a new random name with an extension WE choose,
        so "evil.php" can never survive the trip

   Uploads/results/.htaccess then switches the PHP engine off in that
   folder as a third, independent lock on the same door.
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
require_once "../Models/resultModel.php";


if(!isset($_SESSION["u_id"]))
{
    echo "Please login first!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/submitResult.php";

    $match_id = $_POST["match_id"];

    $errMsg = "";
    $hasErr = false;


    if(empty($match_id))
    {
        $hasErr = true;
        $errMsg = "Please choose a match!";
    }


                              /* Scores */
    $score_team1 = trim($_POST["score_team1"]);
    $score_team2 = trim($_POST["score_team2"]);

    if($score_team1 === "" || $score_team2 === "")
    {
        $hasErr = true;
        $errMsg = "Both scores are required!";
    }
    else if(!ctype_digit($score_team1) || !ctype_digit($score_team2))
    {
        $hasErr = true;
        $errMsg = "Scores must be whole numbers, 0 or more!";
    }
    else if($score_team1 > 99 || $score_team2 > 99)
    {
        $hasErr = true;
        $errMsg = "That score looks wrong - the maximum is 99.";
    }


              /* Is this person allowed to report THIS match? */
    if(!$hasErr)
    {
        if(!canSubmitForMatch($match_id, $_SESSION["u_id"], $_SESSION["role"]))
        {
            $hasErr = true;
            $errMsg = "You can only submit results for matches your own team played.";
        }
    }


                            /* Screenshot */
    $screenshot = null;

    if(!$hasErr)
    {
        if(!isset($_FILES["screenshot"]) || $_FILES["screenshot"]["error"] == UPLOAD_ERR_NO_FILE)
        {
            $hasErr = true;
            $errMsg = "A screenshot is required as proof of the result.";
        }
        else if($_FILES["screenshot"]["error"] != UPLOAD_ERR_OK)
        {
            $hasErr = true;
            $errMsg = "The screenshot failed to upload. Please try again.";
        }
        else if($_FILES["screenshot"]["size"] > 2 * 1024 * 1024)
        {
            $hasErr = true;
            $errMsg = "The screenshot must be 2 MB or smaller.";
        }
        else
        {
            /* read the TRUE type from the file's own bytes */
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES["screenshot"]["tmp_name"]);
            finfo_close($finfo);

            $allowed = array(
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            );

            if(!isset($allowed[$mimeType]))
            {
                $hasErr = true;
                $errMsg = "Only JPG, PNG or WEBP screenshots are accepted.";
            }
            else
            {
                $uploadDir = "../Uploads/results/";

                if(!is_dir($uploadDir))
                {
                    mkdir($uploadDir, 0755, true);
                }

                /* our own random name and our own extension */
                $newName = "result_" . $match_id . "_" . bin2hex(random_bytes(8))
                         . "." . $allowed[$mimeType];

                if(move_uploaded_file($_FILES["screenshot"]["tmp_name"], $uploadDir . $newName))
                {
                    $screenshot = "Uploads/results/" . $newName;
                }
                else
                {
                    $hasErr = true;
                    $errMsg = "Could not save the screenshot on the server.";
                }
            }
        }
    }


    if($hasErr)
    {
        /* if the file already landed but something else failed, clean it
           up rather than leaving an orphan on disk */
        if($screenshot != null && file_exists("../" . $screenshot))
        {
            unlink("../" . $screenshot);
        }

        header("Location:" . $back . "?errMsg=" . urlencode($errMsg));
        exit();
    }


    $result = submitResult($match_id, $_SESSION["u_id"], $score_team1, $score_team2, $screenshot);

    if($result === true)
    {
        header("Location:" . $back . "?okMsg=" .
            urlencode("Result submitted. A moderator will verify it shortly."));
    }
    else
    {
        if($screenshot != null && file_exists("../" . $screenshot))
        {
            unlink("../" . $screenshot);
        }

        header("Location:" . $back . "?errMsg=" . urlencode($result));
    }

    exit();
}


                    /* Data for Views/submitResult.php */

/* A member only sees matches their own team is playing.
   A moderator or admin may report on any scheduled match. */
if($_SESSION["role"] == "Member")
{
    $matches = getSubmittableMatchesForUser($_SESSION["u_id"]);
}
else
{
    $matches = getAllScheduledMatches();
}

?>
