<?php

session_start();

require_once "../Controls/adminControls.php";

?>


<!DOCTYPE html>
<html>

<head>

    <title>ClubSphere Admin Dashboard</title>

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

                <span>Admin Panel</span>

            </div>


            <div class="menu">

                <a href="adminMembers.php">Members List</a>

                <a href="eventManagement.php">Event Management</a>

                <a href="#">Recruitment</a>

                <a href="#">Fund</a>

                <a href="#">Inventory</a>

                <a href="#">Settings</a>

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

                    <p>Total Members:</p>

                    <span><?php echo $totalMembers; ?></span>

                </div>


                <div class="card fund-card">

                    <img src="../Images/fund.png">

                    <p>Club Fund:</p>

                    <span>150,000 TK</span>

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
                <div class="registration-card">

                    <h2>New Registrations</h2>

                    <div id="registrationCount">Loading...</div>

                    <p>in the last 3 minutes</p>

                </div>

                </div>

            </div>


        </div>

    </div>

    <script>

function loadRegistrationData()
{
    let ajax = new XMLHttpRequest();

    ajax.open(
        "GET",
        "../Controls/registrationGraph.php",
        true
    );

    ajax.onload = function()
    {
        if(ajax.status == 200)
        {
            let data = JSON.parse(ajax.responseText);

            document.getElementById("registrationCount").innerHTML = data.total;
        }
    };

    ajax.send();
}


loadRegistrationData();

setInterval(loadRegistrationData, 180000);

</script>
</body>

</html>