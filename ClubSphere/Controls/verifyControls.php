<?php
/* =====================================================================
   FR13 - verify / reject a submitted match result
          (verifying also fires FR14, inside the model's transaction)
   Owner: Bibek Howlader (23-54606-3)
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

require_once "../Models/resultModel.php";


if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] != "Moderator" && $_SESSION["role"] != "Admin"))
{
    echo "Access Denied!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/verifyResults.php";

    $result_id = $_POST["result_id"];
    $remarks   = trim($_POST["remarks"]);

    if(empty($result_id))
    {
        header("Location:" . $back . "?errMsg=" . urlencode("No submission selected!"));
        exit();
    }

    if(strlen($remarks) > 255)
    {
        $remarks = substr($remarks, 0, 255);
    }


                       /* FR13 - Verify and finalize */
    if(isset($_POST["verify"]))
    {
        if($remarks == "")
        {
            $remarks = null;
        }

        $result = verifyResult($result_id, $_SESSION["u_id"], $remarks);

        if($result === true)
        {
            header("Location:" . $back . "?okMsg=" .
                urlencode("Result verified. The match is finalized and the leaderboard has been updated."));
        }
        else
        {
            header("Location:" . $back . "?errMsg=" . urlencode($result));
        }

        exit();
    }


                            /* FR13 - Reject */
    else if(isset($_POST["reject"]))
    {
        /* a rejection with no reason is useless to the team that has to
           resubmit, so the reason is mandatory here */
        if($remarks == "")
        {
            header("Location:" . $back . "?errMsg=" .
                urlencode("Please write a reason before rejecting a result."));
            exit();
        }

        $result = rejectResult($result_id, $_SESSION["u_id"], $remarks);

        if($result === true)
        {
            header("Location:" . $back . "?okMsg=" .
                urlencode("Result rejected. The team can now submit a corrected result."));
        }
        else
        {
            header("Location:" . $back . "?errMsg=" . urlencode($result));
        }

        exit();
    }


    header("Location:" . $back . "?errMsg=" . urlencode("Unknown action!"));
    exit();
}


                   /* Data for Views/verifyResults.php */

$pendingResults = getPendingResults();

$resultHistory  = getResultHistory(15);

?>
