<?php

/* =====================================================================
   FR11 - Moderators shall be able to set and modify match times
          within a tournament.
   Bibek Howlader (23-54606-3)
   ===================================================================== */

require_once "../Controls/matchControls.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Match Scheduling</title>

    <link rel="stylesheet" href="../Css/matchPages.css">

    <script src="../Js/matchSchedule.js" defer></script>

</head>

<body>

    <div class="page-container">

        <h1>Match Scheduling</h1>

        <p class="fr-note">
            FR11 &mdash; set and modify match times within a tournament.
            A match that is already <b>Completed</b> is locked: its schedule is
            part of the verified record and cannot be rewritten.
        </p>

        <a href="moderatorDashboard.php" class="back-button">Back</a>


        <?php if(isset($_GET["okMsg"])) { ?>
            <div class="msg ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
        <?php } ?>

        <?php if(isset($_GET["errMsg"])) { ?>
            <div class="msg err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
        <?php } ?>


                          <!-- TOURNAMENT TABS -->

        <?php

        if(mysqli_num_rows($tournaments) > 0)
        {
        ?>

            <div class="tabs">

                <?php while($tournament = mysqli_fetch_assoc($tournaments)) { ?>

                    <a href="matchSchedule.php?tournament_id=<?php echo $tournament["tournament_id"]; ?>"
                       class="<?php if($tournament["tournament_id"] == $selectedTournament) echo "active"; ?>">
                        <?php echo htmlspecialchars($tournament["title"]); ?>
                    </a>

                <?php } ?>

            </div>

        <?php
        }
        else
        {
            echo "<p class='no-users'>No tournaments exist yet. They are created in the Tournament module (FR8).</p>";
        }

        ?>


                              <!-- FIXTURES -->

        <h2>Fixtures</h2>

        <?php

        if($matches != false && mysqli_num_rows($matches) > 0)
        {
            while($match = mysqli_fetch_assoc($matches))
            {
        ?>

            <div class="item-card">

                <p class="fixture-title">

                    Round <?php echo $match["round_no"]; ?> &nbsp;&mdash;&nbsp;
                    <b><?php echo htmlspecialchars($match["team1_name"]); ?></b>
                    vs
                    <b><?php echo htmlspecialchars($match["team2_name"]); ?></b>

                    &nbsp;
                    <span class="pill <?php echo $match["status"]; ?>">
                        <?php echo $match["status"]; ?>
                    </span>

                </p>


                <?php if(!empty($match["winner_name"])) { ?>
                    <p class="sub">Winner: <?php echo htmlspecialchars($match["winner_name"]); ?></p>
                <?php } ?>


                <p class="sub">

                    <?php

                    if(empty($match["match_time"]))
                    {
                        echo "Not scheduled yet";
                    }
                    else
                    {
                        echo "Current time: " . date("d M Y, g:i A", strtotime($match["match_time"]));

                        if(!empty($match["venue"]))
                        {
                            echo " &nbsp;&middot;&nbsp; " . htmlspecialchars($match["venue"]);
                        }
                    }

                    ?>

                </p>


                <?php

                if($match["status"] == "Completed" || $match["status"] == "Cancelled")
                {
                    echo "<p class='sub'>This match is locked.</p>";
                }
                else
                {
                ?>

                    <form action="../Controls/matchControls.php" method="post" class="schedule-form">

                        <input type="hidden" name="match_id" value="<?php echo $match["match_id"]; ?>">
                        <input type="hidden" name="tournament_id" value="<?php echo $selectedTournament; ?>">

                        <input type="datetime-local" name="match_time" required
                               value="<?php
                                    if(!empty($match["match_time"]))
                                    {
                                        echo date("Y-m-d\TH:i", strtotime($match["match_time"]));
                                    }
                               ?>">

                        <input type="text" name="venue" maxlength="100" placeholder="Venue"
                               value="<?php echo htmlspecialchars($match["venue"]); ?>">

                        <button type="submit" class="primary">
                            <?php

                            if(empty($match["match_time"]))
                            {
                                echo "Schedule";
                            }
                            else
                            {
                                echo "Update Time";
                            }

                            ?>
                        </button>

                    </form>


                    <form action="../Controls/matchControls.php" method="post" class="cancel-form">

                        <input type="hidden" name="match_id" value="<?php echo $match["match_id"]; ?>">
                        <input type="hidden" name="tournament_id" value="<?php echo $selectedTournament; ?>">

                        <button type="submit" name="cancel" class="reject">Cancel Match</button>

                    </form>

                <?php
                }
                ?>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>No matches in this tournament yet. "
               . "Matches are created by the bracket generator (FR10).</p>";
        }

        ?>

    </div>

</body>

</html>
