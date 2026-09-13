<?php

session_start();

require_once "../Controls/memberDashboardControls.php";

?>

<!DOCTYPE html>
<html>

<head>
    <title>ClubSphere Dashboard</title>
    <link rel="stylesheet" href="../Css/profile.css">
    <link rel="stylesheet" href="../Css/memberDashboard.css">
</head>

<body>

    <div class="dashboard">

                                        <!-- SIDEBAR -->
        <div class="sidebar">

            <img src="../Images/logo.png" class="logo">

            <div class="club-name">ClubSphere</div>

            <div class="welcome">

                <img src="../Images/member.png" class="member-icon">

                <span>Hello, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>

            </div>


            <div class="menu">

                <a href="teams.php">Teams</a>

                <a href="tournaments.php">Tournaments</a>

                <a href="#">Events</a>

                <a href="alerts.php">Notification</a>

                <a href="news.php">News</a>

                <a href="#">Reports</a>

                <a href="profile.php">User Profile</a>

            </div>


            <a href="logout.php" class="logout">Logout</a>

        </div>


                  <!-- MAIN CONTENT -->
        <div class="main-content">


                                      <!-- TOP CARDS -->

    <div class="top-cards">

          <div class="card">

          <img src="../Images/prx.png" alt="Gaming">

              <p>Gaming Preference</p>

        <span>
        <?php echo htmlspecialchars(!empty($userProfile["game_type"]) ? $userProfile["game_type"] : "Not set"); ?>
          </span>

             </div>


                <div class="card">

                <img src="../Images/trophy.png" alt="Ranking">

                 <p>Ranking</p>

               <span>
                <?php echo htmlspecialchars(!empty($userProfile["ranking"]) ? $userProfile["ranking"] : "Not set"); ?>
               </span>

                 </div>


           <div class="card">

           <img src="../Images/champion.png" alt="Social Media">

           <p>Social Media</p>

             <span>
                <?php echo htmlspecialchars(!empty($userProfile["social_link"]) ? $userProfile["social_link"] : "Not set"); ?>
             </span>

          </div>

   </div>


                                   <!-- UPCOMING EVENTS -->

            <div class="events-card">

                <h2>Upcoming Events</h2>

                <div class="events-box">

                    <div class="events-top"></div>

                    <div class="events-message">
                        There is no upcoming events currently
                    </div>

                </div>

            </div>


        </div>

    </div>

</body>

</html>