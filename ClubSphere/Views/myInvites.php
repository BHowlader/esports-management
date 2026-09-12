<?php

session_start();

require_once "../Controls/teamControls.php";


  /*Only a Member answers invitations (FR6)*/

if($role != "Member")
{
    echo "Access Denied!";
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>My Invitations</title>

    <link rel="stylesheet" href="../Css/teamForm.css">

</head>

<body>

    <div class="form-container wide">

        <h1>My Team Invitations</h1>

        <a href="teams.php" class="back-button">Back</a>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


        <?php

        if(mysqli_num_rows($myInvites) > 0)
        {
            while($invite = mysqli_fetch_assoc($myInvites))
            {
        ?>

            <div class="invite-card">

                <p>Team: <?php echo htmlspecialchars($invite["team_name"] ?? ""); ?></p>

                <p>Game: <?php echo htmlspecialchars($invite["game_name"] ?? ""); ?></p>

                <p>Invited by: <?php echo htmlspecialchars($invite["captain_name"] ?? ""); ?></p>


                <div class="button-area">

                    <form action="../Controls/teamControls.php" method="post">

                        <input type="hidden" name="team_member_id"
                               value="<?php echo htmlspecialchars($invite["team_member_id"] ?? ""); ?>">

                        <input type="hidden" name="answer" value="accept">

                        <button type="submit" name="respondInvite" class="approve">Accept</button>

                    </form>


                    <form action="../Controls/teamControls.php" method="post">

                        <input type="hidden" name="team_member_id"
                               value="<?php echo htmlspecialchars($invite["team_member_id"] ?? ""); ?>">

                        <input type="hidden" name="answer" value="decline">

                        <button type="submit" name="respondInvite" class="reject">Decline</button>

                    </form>

                </div>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>You have no pending invitation.</p>";
        }

        ?>

    </div>

</body>

</html>
