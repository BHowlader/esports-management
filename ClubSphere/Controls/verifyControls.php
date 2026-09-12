<?php

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

    else if(isset($_POST["reject"]))
    {
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

$pendingResults = getPendingResults();

$resultHistory  = getResultHistory(15);

?>
