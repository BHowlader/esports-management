<?php

session_start();

require_once "../Controls/teamControls.php";


  /*Only a Moderator manages team structures (FR7)*/

if($role != "Moderator" && $role != "Admin")
{
    echo "Access Denied!";
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Manage Teams</title>

    <link rel="stylesheet" href="../Css/manageTeams.css">

</head>

<body>

    <div class="members-container">

        <h1>Manage Teams</h1>

        <a href="moderatorDashboard.php" class="back-button">Back</a>

        <p class="note">Moderators can edit a team structure and manage its member list.</p>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


        <?php

        if(mysqli_num_rows($allTeams) > 0)
        {
            while($team = mysqli_fetch_assoc($allTeams))
            {
        ?>

            <div class="member-card">

                <p>Team: <?php echo $team["team_name"]; ?></p>

                <p>Game: <?php echo $team["game_name"]; ?></p>

                <p>Captain: <?php echo $team["captain_name"]; ?></p>

                <p>Accepted Members: <?php echo $team["member_count"]; ?></p>

                <p>Created: <?php echo $team["created_date"]; ?></p>

                <p>Status: <?php echo $team["status"]; ?></p>


                <div class="button-area">

                    <a href="editTeam.php?team_id=<?php echo $team["team_id"]; ?>"
                       class="edit-button">Edit Team</a>

                </div>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>No team has been created yet.</p>";
        }

        ?>

    </div>

</body>

</html>
