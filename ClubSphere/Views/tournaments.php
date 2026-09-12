<?php

session_start();

require_once "../Controls/tournamentControls.php";


/* the back arrow goes to whichever dashboard this user came from */

if($role == "Admin")
{
    $backTo = "adminDashboard.php";
}
else if($role == "Moderator")
{
    $backTo = "moderatorDashboard.php";
}
else
{
    $backTo = "memberDashboard.php";
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Tournaments</title>

    <link rel="stylesheet" href="../Css/tournaments.css">

    <script src="../Js/tournamentSearch.js" defer></script>

</head>

<body>

    <div class="wrap">

        <div class="top-bar">

            <a href="<?php echo $backTo; ?>" class="back-arrow">&lt;</a>

            <input type="text" id="searchBox" placeholder="Search events...">

        </div>


        <h1 class="page-title">Tournaments</h1>


        <?php if(isset($_GET["message"])) { ?>

            <p class="message"><?php echo htmlspecialchars($_GET["message"]); ?></p>

        <?php } ?>


        <?php

        if(mysqli_num_rows($allTournaments) > 0)
        {
            while($tour = mysqli_fetch_assoc($allTournaments))
            {
        ?>

            <div class="tour-card"
                 data-search="<?php echo $tour["title"] . " " . $tour["game_title"] . " " . $tour["status"]; ?>">

                <div class="tour-info">

                    <h3><?php echo $tour["title"]; ?></h3>

                    <p class="meta">Game: <?php echo $tour["game_title"]; ?></p>

                    <p class="meta">

                        <?php

                        if($tour["status"] == "Upcoming")
                        {
                            echo "Coming Soon...";
                        }
                        else
                        {
                            echo $tour["start_date"] . " to " . $tour["end_date"];
                        }

                        ?>

                    </p>

                    <p class="meta">Registered teams: <?php echo $tour["team_count"]; ?></p>

                </div>


                <?php if($tour["status"] == "Upcoming") { ?>

                    <span class="details-button off">Coming Soon</span>

                <?php } else { ?>

                    <a class="details-button"
                       href="tournamentDetails.php?tournament_id=<?php echo $tour["tournament_id"]; ?>">Details</a>

                <?php } ?>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='empty'>There is no tournament currently.</p>";
        }

        ?>

    </div>

</body>

</html>
