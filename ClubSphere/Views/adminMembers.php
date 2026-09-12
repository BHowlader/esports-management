<?php
session_start();

require_once "../Controls/adminControls.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Members List</title>

    <link rel="stylesheet" href="../Css/adminMembers.css">

</head>

<body>

    <div class="members-container">

        <h1>Members List</h1>
        <a href="adminDashboard.php" class="back-button">Back</a>


                            <!-- PENDING MEMBERS -->

        <h2>Pending Membership Requests</h2>

        <?php

        if(mysqli_num_rows($pendingUsers) > 0)
        {
            while($user = mysqli_fetch_assoc($pendingUsers))
            {
        ?>

            <div class="member-card">

                <p>Name: <?php echo htmlspecialchars($user["name"]); ?></p>

                <p>University ID: <?php echo htmlspecialchars($user["uni_id"]); ?></p>

                <p>Email: <?php echo htmlspecialchars($user["email_id"]); ?></p>

                <p>Status: <?php echo htmlspecialchars($user["status"]); ?></p>


                <form action="../Controls/adminControls.php" method="post">

                    <input type="hidden" name="u_id" value="<?php echo $user["u_id"]; ?>">

                    <button type="submit" name="approve" class="approve">Approve</button>

                    <button type="submit" name="reject" class="reject">Reject</button>

                </form>

            </div>

        <?php




            }
        }
        else
        {
            echo "<p class='no-users'>No pending membership requests.</p>";
        }

        ?>


                           <!-- APPROVED MEMBERS -->

        <h2>Approved Members</h2>

        <?php

        if(mysqli_num_rows($approvedUsers) > 0)
        {
            while($user = mysqli_fetch_assoc($approvedUsers))
            {
        ?>

            <div class="member-card">

                <p>Name: <?php echo htmlspecialchars($user["name"]); ?></p>

                <p>University ID: <?php echo htmlspecialchars($user["uni_id"]); ?></p>

                <p>Email: <?php echo htmlspecialchars($user["email_id"]); ?></p>

                <p>Current Role: <?php echo htmlspecialchars($user["role"]); ?></p>


                <form action="../Controls/adminControls.php" method="post">

                    <input type="hidden" name="u_id" value="<?php echo $user["u_id"]; ?>">


                    <select name="role">

                        <option value="Member"
                            <?php if($user["role"] == "Member") echo "selected"; ?>>Member</option>

                        <option value="Moderator"
                            <?php if($user["role"] == "Moderator") echo "selected"; ?>>Moderator</option>

                        <option value="Admin"
                            <?php if($user["role"] == "Admin") echo "selected"; ?>>Admin</option>

                    </select>


                    <button type="submit" name="changeRole" class="change-role">Change Role</button>

                </form>

            </div>

        <?php
            }
        }
        else
        {
            echo "<p class='no-users'>No approved members.</p>";
        }

        ?>

    </div>

</body>

</html>