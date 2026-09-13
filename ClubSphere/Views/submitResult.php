<?php

require_once "../Controls/resultControls.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Submit Match Result</title>

    <link rel="stylesheet" href="../Css/matchPages.css">

    <script src="../Js/submitResult.js" defer></script>

</head>

<body>

    <div class="page-container">

        <h1>Submit Match Result</h1>

        <?php

        if($_SESSION["role"] == "Member")
        {
            echo '<a href="memberDashboard.php" class="back-button">Back</a>';
        }
        else
        {
            echo '<a href="moderatorDashboard.php" class="back-button">Back</a>';
        }

        ?>


        <?php if(isset($_GET["okMsg"])) { ?>
            <div class="msg ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
        <?php } ?>

        <?php if(isset($_GET["errMsg"])) { ?>
            <div class="msg err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
        <?php } ?>


        <?php

        if(mysqli_num_rows($matches) > 0)
        {
        ?>

        <form action="../Controls/resultControls.php" method="post"
              enctype="multipart/form-data" id="resultForm">


            <div class="form-row">

                <div class="full">

                    <label for="match_id">Match</label>

                    <select name="match_id" id="match_id" required>

                        <option value="">-- choose a match --</option>

                        <?php while($match = mysqli_fetch_assoc($matches)) { ?>

                            <option value="<?php echo $match["match_id"]; ?>"
                                    data-team1="<?php echo htmlspecialchars($match["team1_name"]); ?>"
                                    data-team2="<?php echo htmlspecialchars($match["team2_name"]); ?>"
                                    <?php if($match["open_submissions"] > 0) echo "disabled"; ?>>

                                <?php

                                echo htmlspecialchars($match["team1_name"]) . " vs "
                                   . htmlspecialchars($match["team2_name"]);

                                if(!empty($match["match_time"]))
                                {
                                    echo "  -  " . date("d M, g:i A", strtotime($match["match_time"]));
                                }

                                if($match["open_submissions"] > 0)
                                {
                                    echo "   (already reported)";
                                }

                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

            </div>


            <div class="form-row">

                <div>
                    <label for="score_team1" id="label1">Score - team 1</label>
                    <input type="number" name="score_team1" id="score_team1"
                           min="0" max="99" step="1" required>
                </div>

                <div>
                    <label for="score_team2" id="label2">Score - team 2</label>
                    <input type="number" name="score_team2" id="score_team2"
                           min="0" max="99" step="1" required>
                </div>

            </div>


            <div class="form-row">

                <div class="full">

                    <label for="screenshot">Screenshot (JPG, PNG or WEBP, max 2 MB)</label>

                    <input type="file" name="screenshot" id="screenshot"
                           accept="image/png,image/jpeg,image/webp" required>

                    <div class="hint" id="fileNote">Proof of the final scoreboard.</div>

                    <img id="preview" class="shot" alt="" hidden style="margin-top:12px">

                </div>

            </div>


            <div class="button-area">

                <button type="submit" class="primary">Submit Result</button>

            </div>

        </form>

        <?php
        }
        else
        {
            echo "<p class='no-users'>You have no scheduled matches to report right now.</p>";
        }
        ?>

    </div>

</body>

</html>
