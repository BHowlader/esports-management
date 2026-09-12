<?php
/* =====================================================================
   FR15 - record club income
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

require_once "../Models/fundModel.php";


/* FR15 says ADMINS record income. Moderators are deliberately not
   allowed here - money is the one area where the SRS gives the admin
   sole responsibility. */
if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/adminFund.php";


                        /* FR15 - Delete a record */
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


                          /* FR15 - Record income */
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


                              /* Amount */
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


       /* Source type - checked against a whitelist, never trusted */
    $validTypes = array("Sponsorship", "Donation", "Entry Fee");

    if(!in_array($source_type, $validTypes))
    {
        $hasErr = true;
        $errMsg = "Please choose a valid source type!";
    }


                            /* Source name */
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


                                /* Date */
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


                     /* Data for Views/adminFund.php */

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
