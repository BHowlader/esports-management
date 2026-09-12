<?php

session_start();

require_once "../Controls/bracketControls.php";


if(!$tournament)
{
    header("Location: tournaments.php?message=" . urlencode("Tournament not found!"));
    exit();
}


/* where the back arrow goes for the role that is logged in */

if($role == "Admin")
{
    $backTo = "eventManagement.php";
}
else if($role == "Moderator")
{
    $backTo = "moderatorDashboard.php";
}
else
{
    $backTo = "tournaments.php";
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Bracket - <?php echo $tournament["title"]; ?></title>

    <link rel="stylesheet" href="../Css/bracket.css">

    <script src="../Js/bracket.js" defer></script>

</head>

<body>

    <div class="wrap">

        <div class="top-bar">

            <a href="<?php echo $backTo; ?>" class="back-arrow">&lt;</a>


            <?php if($canManage) { ?>

            <form id="generateForm" action="../Controls/bracketControls.php" method="post"
                  data-teams="<?php echo count($registeredTeams); ?>"
                  data-exists="<?php echo $hasBracket ? "1" : "0"; ?>">

                <input type="hidden" name="tournament_id"
                       value="<?php echo $tournament["tournament_id"]; ?>">

                <button type="submit" name="generateBracket" class="generate-button">
                    <?php echo $hasBracket ? "Regenerate Bracket" : "Generate Bracket"; ?>
                </button>

            </form>

            <?php } ?>

        </div>


        <h1 class="page-title"><?php echo $tournament["title"]; ?></h1>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


        <div class="detail-panel">

            <p class="meta">Game: <?php echo $tournament["game_title"]; ?></p>

            <p class="meta">Registered teams: <?php echo count($registeredTeams); ?></p>

            <p class="meta">Format: single elimination knockout</p>

        </div>


        <?php if($champion != "") { ?>

            <div class="champion">Champion: <strong><?php echo $champion; ?></strong></div>

        <?php } ?>


        <?php if(!$hasBracket) { ?>

            <div class="empty">

                The bracket has not been generated yet.<br><br>

                <?php

                if(count($registeredTeams) < 2)
                {
                    echo "At least 2 registered teams are needed.";
                }
                else if($canManage)
                {
                    echo "Use the Generate Bracket button above.";
                }
                else
                {
                    echo "An Admin or a Moderator will generate it.";
                }

                ?>

            </div>

        <?php } else { ?>


                        <!-- FR10 - THE KNOCKOUT TREE -->

        <div class="bracket">

            <?php for($r = 1; $r <= $totalRounds; $r++) { ?>

            <div class="round">

                <div class="round-title"><?php echo roundName($r, $totalRounds); ?></div>

                <div class="round-body">

                    <?php foreach($rounds[$r] as $match) { ?>

                    <div class="match">

                        <div class="match-tag">

                            Match <?php echo $match["slot_no"]; ?>

                            <?php if($match["status"] == "Bye") echo " &middot; bye"; ?>

                            <?php if($match["status"] == "Completed") echo " &middot; done"; ?>

                        </div>


                        <div class="slot
                            <?php

                            if($match["winner_id"] != null && $match["winner_id"] == $match["team1_id"])
                            {
                                echo "win";
                            }
                            else if($match["team1_name"] == null)
                            {
                                echo "tbd";
                            }

                            ?>">

                            <?php echo $match["team1_name"] == null ? "TBD" : $match["team1_name"]; ?>

                        </div>


                        <div class="slot
                            <?php

                            if($match["winner_id"] != null && $match["winner_id"] == $match["team2_id"])
                            {
                                echo "win";
                            }
                            else if($match["team2_name"] == null)
                            {
                                echo "tbd";
                            }

                            ?>">

                            <?php

                            if($match["team2_name"] == null)
                            {
                                echo $match["status"] == "Bye" ? "BYE" : "TBD";
                            }
                            else
                            {
                                echo $match["team2_name"];
                            }

                            ?>

                        </div>


                        <?php

                        if($canManage && $match["winner_id"] == null
                           && $match["team1_id"] != null && $match["team2_id"] != null)
                        {
                        ?>

                        <form class="winner-form" action="../Controls/bracketControls.php" method="post">

                            <input type="hidden" name="tournament_id"
                                   value="<?php echo $tournament["tournament_id"]; ?>">

                            <input type="hidden" name="match_id"
                                   value="<?php echo $match["match_id"]; ?>">

                            <select name="winner_id">

                                <option value="">winner...</option>

                                <option value="<?php echo $match["team1_id"]; ?>">
                                    <?php echo $match["team1_name"]; ?>
                                </option>

                                <option value="<?php echo $match["team2_id"]; ?>">
                                    <?php echo $match["team2_name"]; ?>
                                </option>

                            </select>

                            <button type="submit" name="setWinner">Set</button>

                        </form>

                        <?php } ?>

                    </div>

                    <?php } ?>

                </div>

            </div>

            <?php } ?>

        </div>


        <?php } ?>

    </div>

</body>

</html>
