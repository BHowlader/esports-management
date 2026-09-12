<?php

require_once "../Controls/verifyControls.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Verify Match Results</title>

    <link rel="stylesheet" href="../Css/matchPages.css">

    <script src="../Js/verifyResults.js" defer></script>

</head>

<body>

    <div class="page-container">

        <h1>Verify Match Results</h1>

        <p class="fr-note">
            FR13 &mdash; verifying a result finalizes the match and rebuilds the
            leaderboard in the same database transaction, so the two can never
            disagree. Rejected submissions are kept, not deleted &mdash; that is
            the audit trail.
        </p>

        <a href="moderatorDashboard.php" class="back-button">Back</a>


        <?php if(isset($_GET["okMsg"])) { ?>
            <div class="msg ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
        <?php } ?>

        <?php if(isset($_GET["errMsg"])) { ?>
            <div class="msg err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
        <?php } ?>

        <h2>Waiting For Verification (<?php echo mysqli_num_rows($pendingResults); ?>)</h2>

        <?php

        if(mysqli_num_rows($pendingResults) > 0)
        {
            while($result = mysqli_fetch_assoc($pendingResults))
            {
        ?>

            <div class="item-card">

                <div class="card-split">

                    <div class="details">

                        <p class="sub"><?php echo htmlspecialchars($result["tournament_title"]); ?></p>

                        <p class="fixture-title">

                            <b><?php echo htmlspecialchars($result["team1_name"]); ?></b>
                            &nbsp;<?php echo $result["score_team1"]; ?>
                            &ndash;
                            <?php echo $result["score_team2"]; ?>&nbsp;
                            <b><?php echo htmlspecialchars($result["team2_name"]); ?></b>

                        </p>

                        <p class="sub">

                            Submitted by <?php echo htmlspecialchars($result["submitted_by_name"]); ?>
                            on <?php echo date("d M Y, g:i A", strtotime($result["submitted_at"])); ?>

                            <?php if(!empty($result["match_time"])) { ?>
                                <br>Match played
                                <?php echo date("d M Y, g:i A", strtotime($result["match_time"])); ?>
                            <?php } ?>

                        </p>

                    </div>


                    <div class="proof">

                        <?php if(!empty($result["screenshot"])) { ?>

                            <a href="../<?php echo htmlspecialchars($result["screenshot"] ?? ""); ?>"
                               target="_blank">

                                <img class="shot"
                                     src="../<?php echo htmlspecialchars($result["screenshot"] ?? ""); ?>"
                                     alt="Screenshot submitted as proof">

                            </a>

                            <div class="hint">click to open full size</div>

                        <?php } else { ?>

                            <div class="hint">no screenshot attached</div>

                        <?php } ?>

                    </div>

                </div>


                <form action="../Controls/verifyControls.php" method="post" class="verify-form">

                    <input type="hidden" name="result_id" value="<?php echo $result["result_id"]; ?>">

                    <label for="remarks<?php echo $result["result_id"]; ?>">
                        Remarks (required when rejecting)
                    </label>

                    <textarea name="remarks"
                              id="remarks<?php echo $result["result_id"]; ?>"
                              maxlength="255"
                              placeholder="e.g. screenshot does not show the final round"></textarea>

                    <div class="button-area">

                        <button type="submit" name="verify" class="approve">Verify &amp; Finalize</button>

                        <button type="submit" name="reject" class="reject">Reject</button>

                    </div>

                </form>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>Nothing is waiting for review.</p>";
        }

        ?>

        <h2>Recently Decided</h2>

        <?php

        if(mysqli_num_rows($resultHistory) > 0)
        {
        ?>

        <div class="table-scroll">

        <table>

            <thead>
                <tr>
                    <th>Fixture</th>
                    <th class="num">Score</th>
                    <th>Outcome</th>
                    <th>Winner</th>
                    <th>Decided By</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>

            <?php while($history = mysqli_fetch_assoc($resultHistory)) { ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars($history["team1_name"]) . " vs "
                           . htmlspecialchars($history["team2_name"]);
                        ?>
                    </td>

                    <td class="num">
                        <?php echo $history["score_team1"] . " - " . $history["score_team2"]; ?>
                    </td>

                    <td>
                        <span class="pill <?php echo $history["verification_status"]; ?>">
                            <?php echo $history["verification_status"]; ?>
                        </span>
                    </td>

                    <td>
                        <?php

                        if(empty($history["winner_name"]))
                        {
                            echo "Draw / n-a";
                        }
                        else
                        {
                            echo htmlspecialchars($history["winner_name"] ?? "");
                        }

                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($history["verified_by_name"] ?? ""); ?></td>

                    <td><?php echo htmlspecialchars($history["remarks"] ?? ""); ?></td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

        </div>

        <?php
        }
        else
        {
            echo "<p class='no-users'>Nothing decided yet.</p>";
        }
        ?>

    </div>

</body>

</html>
