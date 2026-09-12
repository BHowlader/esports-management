<?php

require_once "dbConnect.php";


function recordExpense($amount, $description, $expense_type, $payment_method,
                       $transaction_date, $recorded_by)
{
    $conn = dbConnection();

    mysqli_begin_transaction($conn);

    $type = "Expense";

    $sql = "INSERT INTO transaction
                (transaction_type, amount, description, transaction_date, recorded_by)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sdssi",
        $type,
        $amount,
        $description,
        $transaction_date,
        $recorded_by
    );

    if(!mysqli_stmt_execute($stmt))
    {
        mysqli_rollback($conn);
        return "Could not save the transaction.";
    }

    $transaction_id = mysqli_insert_id($conn);

    $sql = "INSERT INTO expense (transaction_id, expense_type, payment_method)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $transaction_id,
        $expense_type,
        $payment_method
    );

    if(!mysqli_stmt_execute($stmt))
    {
        mysqli_rollback($conn);
        return "Could not save the expense detail.";
    }

    mysqli_commit($conn);

    return true;
}

function getExpenseRecords()
{
    $conn = dbConnection();

    $sql = "SELECT e.expense_id, e.expense_type, e.payment_method,
                   t.amount, t.description, t.transaction_date,
                   u.name AS recorded_by_name
            FROM expense e
            JOIN transaction t ON t.transaction_id = e.transaction_id
            JOIN users u       ON u.u_id = t.recorded_by
            WHERE t.transaction_type = 'Expense'
            ORDER BY t.transaction_date DESC, e.expense_id DESC";

    return mysqli_query($conn, $sql);
}

function getTotalExpense()
{
    $conn = dbConnection();

    $sql = "SELECT COALESCE(SUM(amount), 0) AS total
            FROM transaction WHERE transaction_type = 'Expense'";

    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    return $row["total"];
}

?>
