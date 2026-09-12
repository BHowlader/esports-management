<?php

session_start();

require_once "../Controls/tournamentControls.php";


if(!$tournament)
{
    header("Location: tournaments.php?message=" . urlencode("Tournament not found!"));
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title><?php echo $tournament["title"]; ?></title>

    <link rel="stylesheet" href="../Css/tournaments.css">

</head>

<body>

    <div class="wrap">

        <div class="top-bar">

            <a href="tournaments.php" class="back-arrow">&lt;</a>

        </div>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


        <div class="detail-panel">

            <h2><?php echo $tournament["title"]; ?></h2>

            <p class="description"><?php echo $tournament["description"]; ?></p>

            <p class="meta">Game: <?php echo $tournament["game_title"]; ?></p>

            <p class="meta">
                <?php echo $tournament["start_date"]; ?> to <?php echo $tournament["end_date"]; ?>
            </p>

            <p class="meta">Prize: <?php echo $tournament["prize"]; ?></p>

            <p class="meta">Organizer: <?php echo $tournament["organizer"]; ?></p>

            <p class="meta">Region: <?php echo $tournament["region"]; ?></p>

            <p class="meta">Status: <?php echo $tournament["status"]; ?></p>


            <div class="rules">
                <strong>Rules</strong><br>
                <?php echo $tournament["rules"]; ?>
            </div>


                        <!-- FR9 - REGISTER ONE OF MY TEAMS -->

            <div class="register-box">

                <?php if($role != "Member") { ?>

                    <p class="note">Only Members register teams for a tournament.</p>

                <?php } else if($tournament["status"] != "Registration Open") { ?>

                    <span class="details-button off">Registration Closed</span>

                <?php } else if(count($myCaptainTeams) == 0) { ?>

                    <p class="note">
                        You have no <?php echo $tournament["game_title"]; ?> team available to
                        register. You must be the captain of a <?php echo $tournament["game_title"]; ?>
                        team that is not entered yet.
                    </p>

                <?php } else { ?>

                    <form action="../Controls/tournamentControls.php" method="post">

                        <input type="hidden" name="tournament_id"
                               value="<?php echo $tournament["tournament_id"]; ?>">

                        <label>Register one of your teams</label>

                        <select name="team_id">

                            <option value="">-- select your team --</option>

                            <?php

                            foreach($myCaptainTeams as $myTeam)
                            {
                                echo "<option value='" . $myTeam["team_id"] . "'>"
                                   . $myTeam["team_name"] . "</option>";
                            }

                            ?>

                        </select>

                        <button type="submit" name="registerTeam" class="details-button">
                            Apply for Registration
                        </button>

                    </form>

                <?php } ?>

            </div>

        </div>


                        <!-- REGISTERED TEAMS -->

        <div class="detail-panel">

            <h2>Registered Teams</h2>

            <?php

            if(count($registeredTeams) > 0)
            {
                foreach($registeredTeams as $regTeam)
                {
                    echo "<p class='meta'><strong>" . $regTeam["team_name"] . "</strong>"
                       . " &middot; captain " . $regTeam["captain_name"]
                       . " &middot; registered " . $regTeam["registered_at"] . "</p>";
                }
            ?>

                <br>

                <a class="details-button"
                   href="bracket.php?tournament_id=<?php echo $tournament["tournament_id"]; ?>">View Bracket</a>

            <?php
            }
            else
            {
                echo "<p class='empty'>No team has registered yet.</p>";
            }

            ?>

        </div>

    </div>

</body>

</html>
