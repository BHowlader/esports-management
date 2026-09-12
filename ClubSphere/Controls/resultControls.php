<?php

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

    if(!$hasErr)
    {
        if(!canSubmitForMatch($match_id, $_SESSION["u_id"], $_SESSION["role"]))
        {
            $hasErr = true;
            $errMsg = "You can only submit results for matches your own team played.";
        }
    }

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

if($_SESSION["role"] == "Member")
{
    $matches = getSubmittableMatchesForUser($_SESSION["u_id"]);
}
else
{
    $matches = getAllScheduledMatches();
}

?>
