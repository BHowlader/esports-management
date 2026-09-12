<?php

require_once "../Controls/trackEquipmentControls.php";

?>

<!DOCTYPE html>
<html>
<head>
    <title>ClubSphere - Track Equipment</title>
    <link rel="stylesheet" href="../Css/expensePages.css">
</head>
<body class="page-track">

    <a href="adminDashboard.php" class="back-link">&larr; Back to Dashboard</a>
    <a href="equipment.php" class="back-link">Manage Equipment &rarr;</a>

    <h1>ClubSphere Equipment Tracking</h1>
    <h2>Track Equipment Usage &amp; Assignment</h2>

    <?php if(isset($_GET["okMsg"])) { ?>
        <div class="msg msg-ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
    <?php } ?>

    <?php if(isset($_GET["errMsg"])) { ?>
        <div class="msg msg-err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
    <?php } ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Equipment Name</th>
            <th>Category</th>
            <th>Current Status</th>
            <th>Assigned Member / Team</th>
        </tr>
        <?php
        if(mysqli_num_rows($items) > 0)
        {
            while($item = mysqli_fetch_assoc($items))
            {
        ?>
        <tr>
            <td><?php echo $item["item_id"]; ?></td>
            <td><strong><?php echo htmlspecialchars($item["item_name"]); ?></strong></td>
            <td><?php echo htmlspecialchars($item["category"]); ?></td>
            <td><?php echo htmlspecialchars($item["status"]); ?></td>
            <td>
                <form method="post" action="../Controls/trackEquipmentControls.php" class="inline-form">
                    <input type="hidden" name="equipment_id" value="<?php echo $item["item_id"]; ?>">
                    <input type="text" name="assigned_to" maxlength="100" required
                           value="<?php echo htmlspecialchars($item["assigned_to"] ?? ""); ?>"
                           placeholder="Enter member/team name">
                    <button type="submit" name="assign_equipment" class="btn-assign">Assign / Update</button>
                </form>
            </td>
        </tr>
        <?php
            }
        }
        else
        {
            echo "<tr><td colspan='5' class='empty'>No equipment registered in inventory. Add some on the <a href='equipment.php'>equipment page</a> first.</td></tr>";
        }
        ?>
    </table>

</body>
</html>
