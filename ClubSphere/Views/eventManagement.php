<?php

session_start();

require_once "../Controls/tournamentControls.php";
require_once "../Models/bracketModels.php";


  /*Only an Admin creates tournaments (FR8)*/

if($role != "Admin")
{
    echo "Access Denied!";
    exit();
}


$title = $_GET["title"] ?? "";
$description = $_GET["description"] ?? "";
$rules = $_GET["rules"] ?? "";
$prize = $_GET["prize"] ?? "";

$titleErr = $_GET["titleErr"] ?? "";
$game_titleErr = $_GET["game_titleErr"] ?? "";
$rulesErr = $_GET["rulesErr"] ?? "";
$dateErr = $_GET["dateErr"] ?? "";

?>


<!DOCTYPE html>
<html>

<head>

    <title>Event Management</title>

    <link rel="stylesheet" href="../Css/eventManagement.css">

</head>

<body>

    <div class="members-container">

        <h1>Event Management</h1>

        <a href="adminDashboard.php" class="back-button">Back</a>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


                        <!-- FR8 - CREATE A TOURNAMENT -->

        <h2>Create a Tournament</h2>

        <div class="member-card">

            <form action="../Controls/tournamentControls.php" method="post">


                <div class="form-group">

                    <label>Tournament Title</label>

                    <input type="text" name="title" placeholder="e.g. Valorant Championship 2026"
                           value="<?php echo htmlspecialchars($title); ?>">

                    <span class="error"><?php echo htmlspecialchars($titleErr); ?></span>

                </div>


                <div class="two-columns">

                    <div class="form-group">

                        <label>Game Title</label>

                        <select name="game_title">

                            <option value="">-- select a game --</option>
                            <option value="Valorant">Valorant</option>
                            <option value="MLBB">MLBB</option>
                            <option value="CS2">CS2</option>
                            <option value="PUBG Mobile">PUBG Mobile</option>
                            <option value="EA FC">EA FC</option>

                        </select>

                        <span class="error"><?php echo htmlspecialchars($game_titleErr); ?></span>

                    </div>


                    <div class="form-group">

                        <label>Prize Pool</label>

                        <input type="text" name="prize" placeholder="e.g. $500"
                               value="<?php echo htmlspecialchars($prize); ?>">

                    </div>

                </div>


                <div class="two-columns">

                    <div class="form-group">

                        <label>Start Date</label>

                        <input type="date" name="start_date">

                    </div>


                    <div class="form-group">

                        <label>End Date</label>

                        <input type="date" name="end_date">

                    </div>

                </div>

                <span class="error"><?php echo htmlspecialchars($dateErr); ?></span>


                <div class="form-group">

                    <label>Region</label>

                    <input type="text" name="region" value="Bangladesh">

                </div>


                <div class="form-group">

                    <label>Short Description</label>

                    <textarea name="description"
                              placeholder="Shown on the tournament details page"><?php echo htmlspecialchars($description); ?></textarea>

                </div>


                <div class="form-group">

                    <label>Rules</label>

                    <textarea name="rules"
                              placeholder="Format, match length, roster lock, penalties..."><?php echo htmlspecialchars($rules); ?></textarea>

                    <span class="error"><?php echo htmlspecialchars($rulesErr); ?></span>

                </div>


                <button type="submit" name="createTournament" class="approve">Create Tournament</button>


            </form>

        </div>


                        <!-- THE TOURNAMENT LIST -->

        <h2>All Tournaments</h2>

        <?php

        if(mysqli_num_rows($allTournaments) > 0)
        {
            while($tour = mysqli_fetch_assoc($allTournaments))
            {
        ?>

            <div class="member-card">

                <p>Title: <?php echo $tour["title"]; ?></p>

                <p>Game: <?php echo $tour["game_title"]; ?></p>

                <p>Dates: <?php echo $tour["start_date"]; ?> to <?php echo $tour["end_date"]; ?></p>

                <p>Registered Teams: <?php echo $tour["team_count"]; ?></p>

                <p>Bracket:
                    <?php
                        if(bracketExists($tour["tournament_id"]))
                        {
                            echo "Generated";
                        }
                        else
                        {
                            echo "Not generated yet";
                        }
                    ?>
                </p>

                <p>Current Status: <?php echo $tour["status"]; ?></p>


                <div class="button-area">

                    <form action="../Controls/tournamentControls.php" method="post">

                        <input type="hidden" name="tournament_id"
                               value="<?php echo $tour["tournament_id"]; ?>">

                        <select name="status">

                            <?php

                            $states = array("Upcoming", "Registration Open", "Ongoing",
                                            "Completed", "Cancelled");

                            foreach($states as $s)
                            {
                                if($s == $tour["status"])
                                {
                                    echo "<option value='" . $s . "' selected>" . $s . "</option>";
                                }
                                else
                                {
                                    echo "<option value='" . $s . "'>" . $s . "</option>";
                                }
                            }

                            ?>

                        </select>

                        <button type="submit" name="changeStatus" class="change-role">Set Status</button>

                    </form>


                    <a href="bracket.php?tournament_id=<?php echo $tour["tournament_id"]; ?>"
                       class="edit-button">Open Bracket</a>

                </div>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>No tournament has been created yet.</p>";
        }

        ?>

    </div>

</body>

</html>
