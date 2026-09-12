<?php

session_start();

require_once "../Controls/teamControls.php";

?>


<!DOCTYPE html>
<html>

<head>

    <title>Registered Teams</title>

    <link rel="stylesheet" href="../Css/teams.css">

</head>

<body>

    <div class="teams-container">

        <a href="<?php echo $role == "Moderator" ? "moderatorDashboard.php" : "memberDashboard.php"; ?>"
           class="back-button">Back</a>

        <img src="../Images/logo.png" class="logo">

        <h1>Registered Teams</h1>


                        <!-- FR6 - member actions -->

        <?php if($role == "Member") { ?>

        <div class="action-bar">

            <a href="createTeam.php" class="action-button">Create a Team</a>

            <a href="myInvites.php" class="action-button">
                My Invitations
                <?php
                    if(mysqli_num_rows($myInvites) > 0)
                    {
                        echo "(" . mysqli_num_rows($myInvites) . ")";
                    }
                ?>
            </a>

        </div>

        <?php } ?>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


                        <!-- THE TEAM GRID -->

        <div class="team-grid">

            <?php

            if(mysqli_num_rows($allTeams) > 0)
            {
                while($team = mysqli_fetch_assoc($allTeams))
                {
            ?>

                <a class="team-tile" href="teamDetails.php?team_id=<?php echo $team["team_id"]; ?>">

                    <div class="team-badge">
                        <?php echo strtoupper(substr($team["team_name"], 0, 2)); ?>
                    </div>

                    <p class="team-name"><?php echo $team["team_name"]; ?></p>

                    <p class="team-game"><?php echo $team["game_name"]; ?></p>

                    <p class="team-count"><?php echo $team["member_count"]; ?> players</p>

                </a>

            <?php
                }
            }
            else
            {
                echo "<p class='no-teams'>No team has been created yet.</p>";
            }

            ?>

        </div>


                        <!-- MY TEAMS -->

        <?php if($role == "Member") { ?>

        <h2>My Teams</h2>

        <?php

        if(mysqli_num_rows($myTeams) > 0)
        {
            while($mine = mysqli_fetch_assoc($myTeams))
            {
        ?>

            <div class="my-team-card">

                <p><strong><?php echo $mine["team_name"]; ?></strong>
                   &nbsp;&middot;&nbsp; <?php echo $mine["game_name"]; ?>
                   &nbsp;&middot;&nbsp; My role: <?php echo $mine["team_role"]; ?>
                   &nbsp;&middot;&nbsp; <?php echo $mine["member_count"]; ?> players</p>

                <a href="teamDetails.php?team_id=<?php echo $mine["team_id"]; ?>"
                   class="open-button">Open</a>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-teams'>You are not in a team yet. Create one above.</p>";
        }

        ?>

        <?php } ?>

    </div>

</body>

</html>
