<?php

session_start();

require_once "../Controls/teamControls.php";


  /*Only a Moderator edits a team (FR7)*/

if($role != "Moderator" && $role != "Admin")
{
    echo "Access Denied!";
    exit();
}


if(!$team)
{
    header("Location: manageTeams.php?message=" . urlencode("Team not found!"));
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Edit <?php echo $team["team_name"]; ?></title>

    <link rel="stylesheet" href="../Css/manageTeams.css">

</head>

<body>

    <div class="members-container">

        <h1>Edit Team</h1>

        <a href="manageTeams.php" class="back-button">Back</a>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


                        <!-- FR7 - TEAM STRUCTURE -->

        <h2>Team Structure</h2>

        <div class="member-card">

            <form action="../Controls/teamControls.php" method="post">

                <input type="hidden" name="team_id" value="<?php echo $team["team_id"]; ?>">


                <div class="form-group">

                    <label>Team Name</label>

                    <input type="text" name="team_name"
                           value="<?php echo htmlspecialchars($team["team_name"]); ?>">

                </div>


                <div class="form-group">

                    <label>Game</label>

                    <select name="game_name">

                        <?php

                        $games = array("Valorant", "MLBB", "CS2", "PUBG Mobile", "EA FC");

                        foreach($games as $game)
                        {
                            if($game == $team["game_name"])
                            {
                                echo "<option value='" . $game . "' selected>" . $game . "</option>";
                            }
                            else
                            {
                                echo "<option value='" . $game . "'>" . $game . "</option>";
                            }
                        }

                        ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Status</label>

                    <select name="status">

                        <option value="Active"
                            <?php if($team["status"] == "Active") echo "selected"; ?>>Active</option>

                        <option value="Disbanded"
                            <?php if($team["status"] == "Disbanded") echo "selected"; ?>>Disbanded</option>

                    </select>

                </div>


                <div class="form-group">

                    <label>Captain</label>

                    <select name="captain_id">

                        <?php

                        foreach($roster as $player)
                        {
                            if($player["status"] == "Accepted")
                            {
                                if($player["u_id"] == $team["captain_id"])
                                {
                                    echo "<option value='" . $player["u_id"] . "' selected>"
                                       . $player["name"] . "</option>";
                                }
                                else
                                {
                                    echo "<option value='" . $player["u_id"] . "'>"
                                       . $player["name"] . "</option>";
                                }
                            }
                        }

                        ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Description</label>

                    <textarea name="description"><?php echo htmlspecialchars($team["description"]); ?></textarea>

                </div>


                <button type="submit" name="updateTeam" class="approve">Save Changes</button>

            </form>

        </div>


                        <!-- FR7 - MEMBER LIST -->

        <h2>Member List</h2>

        <?php

        if(count($roster) > 0)
        {
            foreach($roster as $player)
            {
        ?>

            <div class="member-card">

                <p>Name: <?php echo $player["name"]; ?></p>

                <p>University ID: <?php echo $player["uni_id"]; ?></p>

                <p>Joined: <?php echo $player["joined_date"] == null ? "-" : $player["joined_date"]; ?></p>

                <p>Status: <?php echo $player["status"]; ?></p>

                <p>Current Team Role: <?php echo $player["team_role"]; ?></p>


                <div class="button-area">

                    <form action="../Controls/teamControls.php" method="post">

                        <input type="hidden" name="team_id" value="<?php echo $team["team_id"]; ?>">

                        <input type="hidden" name="team_member_id"
                               value="<?php echo $player["team_member_id"]; ?>">

                        <select name="team_role">

                            <?php

                            $roles = array("Captain", "Player", "Substitute", "Coach");

                            foreach($roles as $r)
                            {
                                if($r == $player["team_role"])
                                {
                                    echo "<option value='" . $r . "' selected>" . $r . "</option>";
                                }
                                else
                                {
                                    echo "<option value='" . $r . "'>" . $r . "</option>";
                                }
                            }

                            ?>

                        </select>

                        <button type="submit" name="updateRole" class="change-role">Change Role</button>

                    </form>


                    <?php if($player["team_role"] != "Captain") { ?>

                    <form action="../Controls/teamControls.php" method="post">

                        <input type="hidden" name="team_id" value="<?php echo $team["team_id"]; ?>">

                        <input type="hidden" name="team_member_id"
                               value="<?php echo $player["team_member_id"]; ?>">

                        <button type="submit" name="removeMember" class="reject">Remove</button>

                    </form>

                    <?php } ?>

                </div>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>This team has no members.</p>";
        }

        ?>

    </div>

</body>

</html>
