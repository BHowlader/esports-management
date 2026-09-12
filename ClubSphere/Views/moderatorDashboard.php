<?php

session_start();

require_once "../Controls/teamControls.php";
require_once "../Controls/tournamentControls.php";


  /*Only a Moderator may open the moderator panel*/

if($_SESSION["role"] != "Moderator")
{
    echo "Access Denied!";
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

    <title>ClubSphere Moderator Dashboard</title>

    <link rel="stylesheet" href="../Css/adminDashboard.css">

</head>

<body>

    <div class="dashboard">

                     <!-- SIDEBAR -->

        <div class="sidebar">

            <img src="../Images/logo.png" class="logo">

            <div class="club-name">
                ClubSphere
            </div>

            <div class="welcome">

                <span class="admin-icon">●</span>

                <span>Moderator Panel</span>

            </div>


            <div class="menu">

                <a href="manageTeams.php">Manage Teams</a>

                <a href="tournaments.php">Tournaments</a>

                <a href="#">Match Results</a>

                <a href="#">Announcements</a>

                <a href="#">Reports</a>

            </div>


            <a href="logout.php" class="logout">Logout</a>

        </div>


                           <!-- MAIN CONTENT -->

        <div class="main-content">


            <!-- TOP CARDS -->

            <div class="top-cards">


                <div class="card team-card">

                    <img src="../Images/prx.png">

                    <p>PRX</p>

                </div>


                <div class="card members-card">

                    <img src="../Images/members.png">

                    <p>Total Teams:</p>

                    <span><?php echo $totalTeams; ?></span>

                </div>


                <div class="card fund-card">

                    <img src="../Images/trophy.png">

                    <p>Tournaments:</p>

                    <span><?php echo $totalTournaments; ?></span>

                </div>


            </div>


                        <!-- UPCOMING EVENTS -->

            <div class="events-card">

                <h2>Upcoming Events</h2>

                <div class="events-box">

                    <div class="events-top"></div>

                    <div class="events-message">There is no upcoming event currently
                    </div>

                </div>

            </div>


        </div>

    </div>

</body>

</html>
