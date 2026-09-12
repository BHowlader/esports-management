<?php

require_once "../Controls/expenseControls.php";

?>

<!DOCTYPE html>
<html>
<head>
    <title>ClubSphere - Expense Management</title>
    <link rel="stylesheet" href="../Css/expensePages.css">
</head>
<body class="page-expenses">

    <a href="adminDashboard.php" class="back-link">&larr; Back to Dashboard</a>

    <h1>ClubSphere Financial Panel</h1>

    <?php if(isset($_GET["okMsg"])) { ?>
        <div class="msg msg-ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
    <?php } ?>

    <?php if(isset($_GET["errMsg"])) { ?>
        <div class="msg msg-err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
    <?php } ?>

    <h2>Club Financial Status Summary</h2>

    <div class="summary-box">
        Total Income: <?php echo number_format($totalIncome, 2); ?> TK
    </div>

    <div class="summary-box expense">
        Total Expenses Logged: <?php echo number_format($totalExpense, 2); ?> TK
    </div>

    <div class="summary-box balance">
        Club Balance: <?php echo number_format($clubFund, 2); ?> TK
    </div>

    <h2>Log New Expense</h2>
    <div class="form-box">
        <form method="post" action="../Controls/expenseControls.php">
            <label>Expense Title:</label>
            <input type="text" name="title" maxlength="255" required placeholder="e.g. Equipment Repair">

            <label>Amount (TK):</label>
            <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00">

            <label>Category:</label>
            <input type="text" name="category" maxlength="50" required placeholder="e.g. Logistics">

            <label>Payment Method:</label>
            <select name="payment_method" required>
                <option value="">-- choose --</option>
                <?php foreach($paymentMethods as $method) { ?>
                    <option value="<?php echo $method; ?>"><?php echo $method; ?></option>
                <?php } ?>
            </select>

            <label>Date:</label>
            <input type="date" name="date" required
                   max="<?php echo date("Y-m-d"); ?>" value="<?php echo date("Y-m-d"); ?>">

            <button type="submit" name="add_expense">Log Expense</button>
        </form>
    </div>

    <h2>Expense History Logs</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Payment</th>
            <th>Date</th>
            <th>Recorded By</th>
        </tr>
        <?php
        if(mysqli_num_rows($expenseRecords) > 0)
        {
            while($row = mysqli_fetch_assoc($expenseRecords))
            {
        ?>
        <tr>
            <td><?php echo $row["expense_id"]; ?></td>
            <td><strong><?php echo htmlspecialchars($row["description"]); ?></strong></td>
            <td><?php echo number_format($row["amount"], 2); ?> TK</td>
            <td><?php echo htmlspecialchars($row["expense_type"]); ?></td>
            <td><?php echo htmlspecialchars($row["payment_method"]); ?></td>
            <td><?php echo $row["transaction_date"]; ?></td>
            <td><?php echo htmlspecialchars($row["recorded_by_name"]); ?></td>
        </tr>
        <?php
            }
        }
        else
        {
            echo "<tr><td colspan='7' class='empty'>No expenses logged yet.</td></tr>";
        }
        ?>
    </table>

</body>
</html>
