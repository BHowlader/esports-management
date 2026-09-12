<?php

session_start();

require_once "../Controls/teamControls.php";


if(!$team)
{
    header("Location: teams.php?message=" . urlencode("Team not found!"));
    exit();
}


/* only the captain of this team sees the invite box (FR6) */

$isCaptain = false;

if($team["captain_id"] == $u_id)
{
    $isCaptain = true;
}

?>


<!DOCTYPE html>
<html>

<head>

    <title><?php echo $team["team_name"]; ?></title>

    <link rel="stylesheet" href="../Css/teamDetails.css">

</head>

<body>

    <div class="team-header">

        <a href="teams.php" class="back-button">Back</a>

        <div class="team-title">

            <span class="team-badge">
                <?php echo strtoupper(substr($team["team_name"], 0, 2)); ?>
            </span>

            <span><?php echo $team["team_name"]; ?></span>

        </div>

    </div>


    <?php if(isset($_GET["message"])) { ?>

        <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

    <?php } ?>


    <div class="team-body">


                            <!-- LEFT: ROSTER -->

        <div class="roster-side">

            <h2>Roster</h2>

            <div class="roster-box">

                <p class="players-label">PLAYERS</p>

                <div class="player-grid">

                    <?php

                    if(count($roster) > 0)
                    {
                        foreach($roster as $player)
                        {
                    ?>

                        <div class="player-card">

                            <img src="../Images/member.png" class="player-icon">

                            <div class="player-info">

                                <p class="player-name"><?php echo $player["name"]; ?></p>

                                <p class="player-meta"><?php echo $player["uni_id"]; ?></p>

                                <p class="player-meta">
                                    <?php echo $player["game_type"]; ?>
                                    <?php echo $player["ranking"]; ?>
                                </p>

                                <span class="player-role"><?php echo $player["team_role"]; ?></span>

                                <?php if($player["status"] == "Invited") { ?>
                                    <span class="player-pending">Invited</span>
                                <?php } ?>

                            </div>

                        </div>

                    <?php
                        }
                    }
                    else
                    {
                        echo "<p class='no-players'>This team has no players yet.</p>";
                    }

                    ?>

                </div>

            </div>


                            <!-- FR6 - INVITE A MEMBER -->

            <?php if($isCaptain) { ?>

            <h2>Invite a Member</h2>

            <div class="invite-box">

                <p class="invite-note">Only the team captain can send invitations.</p>

                <form action="../Controls/teamControls.php" method="post">

                    <input type="hidden" name="team_id" value="<?php echo $team["team_id"]; ?>">

                    <select name="invite_id" size="6">

                        <option value="">-- select a member --</option>

                        <?php

                        while($person = mysqli_fetch_assoc($invitableUsers))
                        {
                            echo "<option value='" . $person["u_id"] . "'>"
                               . $person["name"] . " (" . $person["uni_id"] . ") - "
                               . $person["game_type"] . " " . $person["ranking"]
                               . "</option>";
                        }

                        ?>

                    </select>

                    <button type="submit" name="inviteMember">Send Invitation</button>

                </form>

            </div>

            <?php } ?>

        </div>


                            <!-- RIGHT: TEAM INFO -->

        <div class="info-side">

            <h2>Team Info</h2>

            <div class="info-box">

                <p class="info-label">CAPTAIN</p>
                <p class="info-value"><?php echo $team["captain_name"]; ?></p>

                <p class="info-label">GAME</p>
                <p class="info-value"><?php echo $team["game_name"]; ?></p>

                <p class="info-label">CREATED</p>
                <p class="info-value"><?php echo $team["created_date"]; ?></p>

                <p class="info-label">STATUS</p>
                <p class="info-value"><?php echo $team["status"]; ?></p>

                <p class="info-label">PLAYERS</p>
                <p class="info-value"><?php echo count($roster); ?></p>

                <p class="info-label">ABOUT</p>
                <p class="info-about">
                    <?php
                        echo $team["description"] == "" ? "No description added." : $team["description"];
                    ?>
                </p>

            </div>

        </div>


    </div>

</body>

</html>
