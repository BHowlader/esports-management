<?php

session_start();

if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] != "Moderator" && $_SESSION["role"] != "Admin"))
{
    header("Location: login.php");
    exit();
}

require_once "../Models/matchModel.php";
require_once "../Models/resultModel.php";

$scheduledCount = countMatchesByStatus("Scheduled");
$pendingCount   = countMatchesByStatus("Pending");
$awaitingCount  = countPendingResults();

$upcoming = getUpcomingMatches(6);

?>

<!DOCTYPE html>
<html>

<head>

    <title>ClubSphere Moderator Dashboard</title>

    <link rel="stylesheet" href="../Css/moderatorDashboard.css">

</head>

<body>

    <div class="dashboard">

        <div class="sidebar">

            <img src="../Images/logo.png" class="logo">

            <div class="club-name">
                ClubSphere
            </div>

            <div class="welcome">

                <span class="mod-icon">M</span>

                <span>Hello, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>

            </div>


            <div class="menu">

                <a href="matchSchedule.php">Match Scheduling</a>

                <a href="verifyResults.php">Verify Results</a>

                <a href="leaderboard.php">Leaderboard</a>

                <a href="submitResult.php">Submit Result</a>

                <a href="manageTeams.php">Manage Teams</a>

                <a href="tournaments.php">Tournaments</a>

                <a href="news.php">News</a>

                <a href="alerts.php">Alerts</a>

                <a href="profile.php">User Profile</a>

            </div>


            <a href="logout.php" class="logout">Logout</a>

        </div>

        <div class="main-content">

            <div class="top-cards">


                <div class="card">

                    <img src="../Images/trophy.png">

                    <p>Scheduled Matches:</p>

                    <span class="count"><?php echo $scheduledCount; ?></span>

                </div>


                <div class="card">

                    <img src="../Images/members.png">

                    <p>Awaiting Schedule:</p>

                    <span class="count"><?php echo $pendingCount; ?></span>

                </div>


                <div class="card">

                    <img src="../Images/champion.png">

                    <p>Results To Verify:</p>

                    <span class="count <?php if($awaitingCount > 0) echo "alert"; ?>">
                        <?php echo $awaitingCount; ?>
                    </span>

                </div>


            </div>

            <div class="events-card">

                <h2>Upcoming Matches</h2>

                <div class="events-box">

                    <div class="events-top"></div>

                    <?php

                    if(mysqli_num_rows($upcoming) > 0)
                    {
                        while($match = mysqli_fetch_assoc($upcoming))
                        {
                    ?>

                        <div class="match-row">

                            <div class="fixture">

                                <?php echo htmlspecialchars($match["team1_name"]); ?>
                                vs
                                <?php echo htmlspecialchars($match["team2_name"]); ?>

                            </div>

                            <div class="when">

                                <?php echo date("d M Y, g:i A", strtotime($match["match_time"])); ?>

                                <?php if(!empty($match["venue"])) { ?>
                                    <br><?php echo htmlspecialchars($match["venue"] ?? ""); ?>
                                <?php } ?>

                            </div>

                        </div>

                    <?php
                        }
                    }
                    else
                    {
                    ?>

                        <div class="events-message">
                            There is no upcoming match currently
                        </div>

                    <?php
                    }
                    ?>

                </div>

            </div>


        </div>

    </div>

</body>

</html>
