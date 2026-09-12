<?php

ob_start();

session_start();

require_once "../Models/expenseModel.php";
require_once "../Models/fundModel.php";

if(!isset($_SESSION["u_id"]) || $_SESSION["role"] != "Admin")
{
    echo "Access Denied!";
    exit();
}

$paymentMethods = array("Cash", "Bank Transfer", "Mobile Banking", "Card");


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $back = "../Views/expenses.php";

    $description      = trim($_POST["title"] ?? "");
    $amount           = trim($_POST["amount"] ?? "");
    $expense_type     = trim($_POST["category"] ?? "");
    $payment_method   = trim($_POST["payment_method"] ?? "");
    $transaction_date = trim($_POST["date"] ?? "");

    $errMsg = "";
    $hasErr = false;

    if(empty($description))
    {
        $hasErr = true;
        $errMsg = "Expense title is required!";
    }
    else if(strlen($description) > 255)
    {
        $hasErr = true;
        $errMsg = "Expense title is too long! 255 characters maximum.";
    }

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

    if(empty($expense_type))
    {
        $hasErr = true;
        $errMsg = "Category is required!";
    }
    else if(strlen($expense_type) > 50)
    {
        $hasErr = true;
        $errMsg = "Category is too long! 50 characters maximum.";
    }

    if(!in_array($payment_method, $paymentMethods))
    {
        $hasErr = true;
        $errMsg = "Please choose a valid payment method!";
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
            $errMsg = "You cannot log an expense with a future date!";
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

    $result = recordExpense(
        $amount,
        $description,
        $expense_type,
        $payment_method,
        $transaction_date,
        $_SESSION["u_id"]
    );

    if($result === true)
    {
        header("Location:" . $back . "?okMsg=" .
            urlencode("Expense of " . number_format($amount, 2) . " TK logged."));
    }
    else
    {
        header("Location:" . $back . "?errMsg=" . urlencode($result));
    }

    exit();
}

$expenseRecords = getExpenseRecords();

$totalIncome    = getTotalIncome();

$totalExpense   = getTotalExpense();

$clubFund       = getClubFund();

?>
