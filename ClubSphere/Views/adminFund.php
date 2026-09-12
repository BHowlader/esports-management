<?php

require_once "../Controls/fundControls.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Club Fund</title>

    <link rel="stylesheet" href="../Css/matchPages.css">

    <script src="../Js/adminFund.js" defer></script>

</head>

<body>

    <div class="page-container">

        <h1>Club Fund &mdash; Income</h1>

        <p class="fr-note">
            FR15 &mdash; writes one row to <b>transaction</b> and one to
            <b>income</b> inside a single database transaction, so the ledger can
            never hold money from an unknown source.
        </p>

        <a href="adminDashboard.php" class="back-button">Back</a>


        <?php if(isset($_GET["okMsg"])) { ?>
            <div class="msg ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
        <?php } ?>

        <?php if(isset($_GET["errMsg"])) { ?>
            <div class="msg err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
        <?php } ?>

        <div class="summary">

            <div class="stat">
                <div class="label">Total Income</div>
                <div class="value"><?php echo number_format($totalIncome, 2); ?> TK</div>
            </div>

            <?php while($summary = mysqli_fetch_assoc($incomeSummary)) { ?>

                <div class="stat">
                    <div class="label"><?php echo htmlspecialchars($summary["source_type"]); ?></div>
                    <div class="value"><?php echo number_format($summary["total"], 2); ?> TK</div>
                    <div class="label"><?php echo $summary["entries"]; ?> entries</div>
                </div>

            <?php } ?>

        </div>

        <h2>Record New Income</h2>

        <form action="../Controls/fundControls.php" method="post" id="incomeForm">


            <div class="form-row">

                <div>
                    <label for="source_type">Source Type</label>
                    <select name="source_type" id="source_type" required>
                        <option value="">-- choose --</option>
                        <option value="Sponsorship">Sponsorship</option>
                        <option value="Donation">Donation</option>
                        <option value="Entry Fee">Entry Fee</option>
                    </select>
                </div>

                <div>
                    <label for="amount">Amount (TK)</label>
                    <input type="number" name="amount" id="amount"
                           min="0.01" step="0.01" required placeholder="0.00">
                </div>

                <div>
                    <label for="transaction_date">Date Received</label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           max="<?php echo date("Y-m-d"); ?>"
                           value="<?php echo date("Y-m-d"); ?>" required>
                </div>

            </div>


            <div class="form-row">

                <div id="sponsorField" hidden>

                    <label for="sponsor_id">Sponsor</label>

                    <select name="sponsor_id" id="sponsor_id">

                        <option value="">-- choose a sponsor --</option>

                        <?php while($sponsor = mysqli_fetch_assoc($sponsors)) { ?>

                            <option value="<?php echo $sponsor["sponsor_id"]; ?>">
                                <?php echo htmlspecialchars($sponsor["sponsor_name"]); ?>
                            </option>

                        <?php } ?>

                    </select>

                    <div class="hint">Sponsors come from the sponsor directory (FR25).</div>

                </div>

                <div>
                    <label for="source_name">Source Name</label>
                    <input type="text" name="source_name" id="source_name"
                           maxlength="120" required
                           placeholder="e.g. Ryans Computers, or Valorant Cup entry fees">
                </div>

                <div>
                    <label for="category">Category (optional)</label>
                    <input type="text" name="category" id="category"
                           maxlength="60" placeholder="e.g. Valorant Cup 2026">
                </div>

            </div>


            <div class="form-row">

                <div class="full">
                    <label for="description">Description (optional)</label>
                    <textarea name="description" id="description" maxlength="255"
                              placeholder="Any detail the treasurer will want later"></textarea>
                </div>

            </div>


            <div class="button-area">
                <button type="submit" class="primary">Record Income</button>
            </div>

        </form>

        <h2>Income Ledger</h2>

        <div class="tabs">

            <a href="adminFund.php" class="<?php if($filterType == "") echo "active"; ?>">All</a>

            <a href="adminFund.php?type=Sponsorship"
               class="<?php if($filterType == "Sponsorship") echo "active"; ?>">Sponsorship</a>

            <a href="adminFund.php?type=Donation"
               class="<?php if($filterType == "Donation") echo "active"; ?>">Donation</a>

            <a href="adminFund.php?type=Entry+Fee"
               class="<?php if($filterType == "Entry Fee") echo "active"; ?>">Entry Fee</a>

        </div>

        <?php

        if(mysqli_num_rows($incomeRecords) > 0)
        {
        ?>

        <div class="table-scroll">

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Category</th>
                    <th class="num">Amount (TK)</th>
                    <th>Recorded By</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

            <?php while($record = mysqli_fetch_assoc($incomeRecords)) { ?>

                <tr>

                    <td><?php echo date("d M Y", strtotime($record["transaction_date"])); ?></td>

                    <td>
                        <span class="pill"><?php echo htmlspecialchars($record["source_type"]); ?></span>
                    </td>

                    <td>

                        <?php echo htmlspecialchars($record["source_name"]); ?>

                        <?php if(!empty($record["sponsor_name"])) { ?>
                            <div class="hint">sponsor: <?php echo htmlspecialchars($record["sponsor_name"]); ?></div>
                        <?php } ?>

                    </td>

                    <td><?php echo htmlspecialchars($record["category"]); ?></td>

                    <td class="num"><?php echo number_format($record["amount"], 2); ?></td>

                    <td><?php echo htmlspecialchars($record["recorded_by_name"]); ?></td>

                    <td>

                        <form action="../Controls/fundControls.php" method="post" class="delete-form">

                            <input type="hidden" name="income_id" value="<?php echo $record["income_id"]; ?>">

                            <button type="submit" name="delete" class="reject">Delete</button>

                        </form>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

        </div>

        <?php
        }
        else
        {
            echo "<p class='no-users'>No income recorded yet.</p>";
        }
        ?>

    </div>

</body>

</html>
