<?php

require_once "../Controls/equipmentControls.php";

?>

<!DOCTYPE html>

<head>
    <title>ClubSphere - Equipment Management</title>
    <link rel="stylesheet" href="../Css/expensePages.css">
</head>

<body class="page-equipment">

    <a href="adminDashboard.php" class="back-link">&larr; Back to Dashboard</a>
    <a href="trackEquipment.php" class="back-link">Track Equipment &rarr;</a>

    <h1>ClubSphere Equipment Panel</h1>

    <?php if (isset($_GET["okMsg"])) { ?>
        <div class="msg msg-ok"><?php echo htmlspecialchars($_GET["okMsg"]); ?></div>
    <?php } ?>

    <?php if (isset($_GET["errMsg"])) { ?>
        <div class="msg msg-err"><?php echo htmlspecialchars($_GET["errMsg"]); ?></div>
    <?php } ?>

    <h2>Add New Equipment</h2>
    <div class="form-box">
        <form method="post" action="../Controls/equipmentControls.php">
            <label>Equipment Name:</label>
            <input type="text" name="equipment_name" maxlength="100" required placeholder="e.g. Gaming Headset">

            <label>Category:</label>
            <input type="text" name="category" maxlength="50" required placeholder="e.g. Peripherals">

            <label>Condition:</label>
            <select name="item_condition">
                <?php foreach ($validConditions as $condition) { ?>
                    <option value="<?php echo $condition; ?>"><?php echo $condition; ?></option>
                <?php } ?>
            </select>

            <label>Status:</label>
            <select name="status">
                <?php foreach ($validStatuses as $status) { ?>
                    <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                <?php } ?>
            </select>

            <button type="submit" name="add_equipment">Add Equipment</button>
        </form>
    </div>

    <h2>Equipment Inventory &amp; Status</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Change Condition &amp; Status</th>
        </tr>
        <?php
        if (mysqli_num_rows($items) > 0) {
            while ($item = mysqli_fetch_assoc($items)) {
                ?>
                <tr>
                    <td><?php echo $item["item_id"]; ?></td>
                    <td><strong><?php echo htmlspecialchars($item["item_name"]); ?></strong></td>
                    <td><?php echo htmlspecialchars($item["category"]); ?></td>
                    <td>
                        <form method="post" action="../Controls/equipmentControls.php" class="inline-form">
                            <input type="hidden" name="equipment_id" value="<?php echo $item["item_id"]; ?>">

                            <select name="item_condition" class="action-select">
                                <?php foreach ($validConditions as $condition) { ?>
                                    <option value="<?php echo $condition; ?>" <?php if ($item["item_condition"] == $condition)
                                           echo "selected"; ?>><?php echo $condition; ?></option>
                                <?php } ?>
                            </select>

                            <select name="status" class="action-select">
                                <?php foreach ($validStatuses as $status) { ?>
                                    <option value="<?php echo $status; ?>" <?php if ($item["status"] == $status)
                                           echo "selected"; ?>>
                                        <?php echo $status; ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <button type="submit" name="update_equipment" class="btn-update">Update</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='4' class='empty'>No equipment found in the database.</td></tr>";
        }
        ?>
    </table>

</body>

</html>