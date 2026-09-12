<?php
/* =====================================================================
   FR15 - Admins shall be able to record income from sponsorships,
          donations and entry fees.
   Owner: Bibek Howlader (23-54606-3)

   ---------------------------------------------------------------------
   The SRS normalises finance to 3NF (proposal page 5): a generic
   TRANSACTION row holds what every money movement has in common
   (amount, date, who recorded it) and INCOME holds only what is
   specific to money coming in (which sponsor, what kind of source).

   So recording one income means writing TWO rows in TWO tables. If the
   transaction row were written and the income row failed, the club
   would have money in its ledger from an unknown source - which is
   exactly the mismanagement FR15 exists to prevent. Both writes
   therefore run inside one transaction.

   The member who owns FR16 (expenses) writes to the SAME transaction
   table with transaction_type = 'Expense' plus their own `expense`
   table. Neither of us touches the other's file.
   ===================================================================== */

require_once "dbConnect.php";


function getAllSponsors()
{
    $conn = dbConnection();

    $sql = "SELECT sponsor_id, sponsor_name FROM sponsor ORDER BY sponsor_name ASC";

    return mysqli_query($conn, $sql);
}


/* ---------------------------------------------------------------------
   FR15 core. Returns true, or an error message.
   $sponsor_id may be null - a donation or an entry fee has no sponsor.
   ------------------------------------------------------------------- */
function recordIncome($amount, $source_type, $source_name, $sponsor_id,
                      $category, $description, $transaction_date, $recorded_by)
{
    /* a sponsorship must name a sponsor from the directory,
       anything else must not carry one */
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


/* Ledger for the admin screen.
   $filter_type is "" for all, or Sponsorship / Donation / Entry Fee. */
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


/* Totals per source type - the small summary cards at the top. */
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


/* ---------------------------------------------------------------------
   Total club fund = all income minus all expenses.

   adminDashboard.php currently shows a hard coded "150,000 TK" on the
   Club Fund card. Once FR16 (expenses) is merged this function returns
   the real figure, so that card can become:

       <span><?php echo number_format(getClubFund()); ?> TK</span>

   It already works with only income recorded - expenses simply sum to
   zero until that module lands.
   ------------------------------------------------------------------- */
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

    /* deleting the transaction cascades to the income row
       (ON DELETE CASCADE), so the ledger can never keep an orphan */
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
