<?php

require_once "dbConnect.php";


function getAllSponsors()
{
    $conn = dbConnection();

    $sql = "SELECT sponsor_id, sponsor_name FROM sponsor ORDER BY sponsor_name ASC";

    return mysqli_query($conn, $sql);
}

function recordIncome($amount, $source_type, $source_name, $sponsor_id,
                      $category, $description, $transaction_date, $recorded_by)
{
    if($source_type == "Sponsorship")
    {
        if(empty($sponsor_id))
        {
            return "A sponsorship must be linked to a sponsor.";
        }
    }
    else
    {
        $sponsor_id = null;
    }

    $conn = dbConnection();

    mysqli_begin_transaction($conn);

    $type = "Income";

    $sql = "INSERT INTO transaction
                (transaction_type, amount, category, description,
                 transaction_date, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sdsssi",
        $type,
        $amount,
        $category,
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

    $sql = "INSERT INTO income
                (transaction_id, sponsor_id, source_name, source_type)
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iiss",
        $transaction_id,
        $sponsor_id,
        $source_name,
        $source_type
    );

    if(!mysqli_stmt_execute($stmt))
    {
        mysqli_rollback($conn);
        return "Could not save the income detail.";
    }

    mysqli_commit($conn);

    return true;
}

function getIncomeRecords($filter_type)
{
    $conn = dbConnection();

    $sql = "SELECT i.income_id, i.source_name, i.source_type,
                   t.transaction_id, t.amount, t.category, t.description,
                   t.transaction_date,
                   s.sponsor_name,
                   u.name AS recorded_by_name
            FROM income i
            JOIN transaction t  ON t.transaction_id = i.transaction_id
            LEFT JOIN sponsor s ON s.sponsor_id = i.sponsor_id
            JOIN users u        ON u.u_id = t.recorded_by
            WHERE t.transaction_type = 'Income'";

    if($filter_type != "")
    {
        $sql = $sql . " AND i.source_type = ?";
    }

    $sql = $sql . " ORDER BY t.transaction_date DESC, i.income_id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if($filter_type != "")
    {
        mysqli_stmt_bind_param($stmt, "s", $filter_type);
    }

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

function getIncomeSummary()
{
    $conn = dbConnection();

    $sql = "SELECT i.source_type, COUNT(*) AS entries, SUM(t.amount) AS total
            FROM income i
            JOIN transaction t ON t.transaction_id = i.transaction_id
            WHERE t.transaction_type = 'Income'
            GROUP BY i.source_type";

    return mysqli_query($conn, $sql);
}

function getClubFund()
{
    $conn = dbConnection();

    $sql = "SELECT
              COALESCE(SUM(CASE WHEN transaction_type = 'Income'  THEN amount END), 0) -
              COALESCE(SUM(CASE WHEN transaction_type = 'Expense' THEN amount END), 0)
              AS balance
            FROM transaction";

    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    return $row["balance"];
}


function getTotalIncome()
{
    $conn = dbConnection();

    $sql = "SELECT COALESCE(SUM(amount), 0) AS total
            FROM transaction WHERE transaction_type = 'Income'";

    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    return $row["total"];
}


function deleteIncome($income_id)
{
    $conn = dbConnection();

    $sql = "DELETE t FROM transaction t
            JOIN income i ON i.transaction_id = t.transaction_id
            WHERE i.income_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $income_id);

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}

?>
