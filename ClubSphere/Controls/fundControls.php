<?php

ob_start();

session_start();

require_once "../Models/fundModel.php";

if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/adminFund.php";

    if(isset($_POST["delete"]))
    {
        $income_id = $_POST["income_id"];

        if(deleteIncome($income_id))
        {
            header("Location:" . $back . "?okMsg=" . urlencode("Income record deleted."));
        }
        else
        {
            header("Location:" . $back . "?errMsg=" . urlencode("Could not delete that record."));
        }

        exit();
    }

    $amount           = trim($_POST["amount"]);
    $source_type      = trim($_POST["source_type"]);
    $source_name      = trim($_POST["source_name"]);
    $category         = trim($_POST["category"]);
    $description      = trim($_POST["description"]);
    $transaction_date = trim($_POST["transaction_date"]);

    if(isset($_POST["sponsor_id"]) && $_POST["sponsor_id"] != "")
    {
        $sponsor_id = $_POST["sponsor_id"];
    }
    else
    {
        $sponsor_id = null;
    }

    $errMsg = "";
    $hasErr = false;

    if($amount === "")
    {
        $hasErr = true;
        $errMsg = "Amount is required!";
    }
    else if(!is_numeric($amount))
    {
        $hasErr = true;
        $errMsg = "Amount must be a number!";
    }
    else if($amount <= 0)
    {
        $hasErr = true;
        $errMsg = "Amount must be greater than zero!";
    }
    else if($amount > 9999999999)
    {
        $hasErr = true;
        $errMsg = "That amount is too large!";
    }

    $validTypes = array("Sponsorship", "Donation", "Entry Fee");

    if(!in_array($source_type, $validTypes))
    {
        $hasErr = true;
        $errMsg = "Please choose a valid source type!";
    }

    if(empty($source_name))
    {
        $hasErr = true;
        $errMsg = "Source name is required!";
    }
    else if(strlen($source_name) > 120)
    {
        $hasErr = true;
        $errMsg = "Source name is too long! 120 characters maximum.";
    }


    if(strlen($category) > 60)
    {
        $hasErr = true;
        $errMsg = "Category is too long! 60 characters maximum.";
    }

    if(strlen($description) > 255)
    {
        $hasErr = true;
        $errMsg = "Description is too long! 255 characters maximum.";
    }

    if(empty($transaction_date))
    {
        $hasErr = true;
        $errMsg = "Date is required!";
    }
    else
    {
        $timestamp = strtotime($transaction_date);

        if($timestamp == false)
        {
            $hasErr = true;
            $errMsg = "That is not a valid date!";
        }
        else if($timestamp > strtotime("today 23:59:59"))
        {
            $hasErr = true;
            $errMsg = "You cannot record income with a future date!";
        }
        else
        {
            $transaction_date = date("Y-m-d", $timestamp);
        }
    }


    if($hasErr)
    {
        header("Location:" . $back . "?errMsg=" . urlencode($errMsg));
        exit();
    }


    if($category == "")
    {
        $category = null;
    }

    if($description == "")
    {
        $description = null;
    }

    $result = recordIncome(
        $amount,
        $source_type,
        $source_name,
        $sponsor_id,
        $category,
        $description,
        $transaction_date,
        $_SESSION["u_id"]
    );

    if($result === true)
    {
        header("Location:" . $back . "?okMsg=" .
            urlencode("Income of " . number_format($amount, 2) . " TK recorded."));
    }
    else
    {
        header("Location:" . $back . "?errMsg=" . urlencode($result));
    }

    exit();
}

if(isset($_GET["type"]))
{
    $filterType = $_GET["type"];

    if(!in_array($filterType, array("Sponsorship", "Donation", "Entry Fee")))
    {
        $filterType = "";
    }
}
else
{
    $filterType = "";
}

$incomeRecords = getIncomeRecords($filterType);

$incomeSummary = getIncomeSummary();

$sponsors      = getAllSponsors();

$totalIncome   = getTotalIncome();

?>
