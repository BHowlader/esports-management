<?php
// Include the shared database connection
include 'db.php';

// Handle adding new equipment (FR18)
if (isset($_POST['add_equipment'])) {
    $name = $conn->real_escape_string($_POST['equipment_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $condition = $_POST['item_condition'];
    $status = $_POST['status'];

    $sql = "INSERT INTO equipment (name, category, `condition`, status) VALUES ('$name', '$category', '$condition', '$status')";
    $conn->query($sql);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle updating equipment condition and status (FR20)
if (isset($_POST['update_equipment'])) {
    $id = $_POST['equipment_id'];
    $condition = $_POST['item_condition'];
    $status = $_POST['status'];

    $sql = "UPDATE equipment SET `condition` = '$condition', status = '$status' WHERE id = $id";
    $conn->query($sql);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ClubSphere - Equipment Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f6f9; color: #333; }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .form-box { background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 350px; margin-bottom: 30px; }
        label { display: block; margin-top: 10px; font-weight: bold; font-size: 14px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; width: 100%; cursor: pointer; margin-top: 15px; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #343a40; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .action-select { padding: 5px; font-size: 13px; margin-right: 5px; }
        .btn-update { background-color: #28a745; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-update:hover { background-color: #218838; }
    </style>
</head>
<body>

    <h1>ClubSphere Equipment Panel</h1>

    <!-- FR18: Add Equipment Form -->
    <h2>Add New Equipment</h2>
    <div class="form-box">
        <form method="POST" action="">
            <label>Equipment Name:</label>
            <input type="text" name="equipment_name" required placeholder="e.g. Football">

            <label>Category:</label>
            <input type="text" name="category" required placeholder="e.g. Sports">

            <label>Condition:</label>
            <select name="item_condition">
                <option value="New">New</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
                <option value="Damaged">Damaged</option>
            </select>

            <label>Status:</label>
            <select name="status">
                <option value="Available">Available</option>
                <option value="In Use">In Use</option>
                <option value="Maintenance">Maintenance</option>
            </select>

            <button type="submit" name="add_equipment">Add Equipment</button>
        </form>
    </div>

    <!-- FR20: Display Equipment Table & Update Options -->
    <h2>Equipment Inventory & Status</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Change Condition & Status</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM equipment");
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $current_id = $row['id'];
                $current_name = $row['name'];
                $current_category = $row['category'];
                $current_condition = $row['condition'];
                $current_status = $row['status'];
        ?>
        <tr>
            <td><?php echo $current_id; ?></td>
            <td><strong><?php echo htmlspecialchars($current_name); ?></strong></td>
            <td><?php echo htmlspecialchars($current_category); ?></td>
            <td>
                <!-- Inline form for updating each row safely -->
                <form method="POST" action="" style="margin: 0;">
                    <input type="hidden" name="equipment_id" value="<?php echo $current_id; ?>">
                    
                    <select name="item_condition" class="action-select">
                        <option value="New" <?php if($current_condition == 'New') echo 'selected'; ?>>New</option>
                        <option value="Good" <?php if($current_condition == 'Good') echo 'selected'; ?>>Good</option>
                        <option value="Fair" <?php if($current_condition == 'Fair') echo 'selected'; ?>>Fair</option>
                        <option value="Damaged" <?php if($current_condition == 'Damaged') echo 'selected'; ?>>Damaged</option>
                    </select>

                    <select name="status" class="action-select">
                        <option value="Available" <?php if($current_status == 'Available') echo 'selected'; ?>>Available</option>
                        <option value="In Use" <?php if($current_status == 'In Use') echo 'selected'; ?>>In Use</option>
                        <option value="Maintenance" <?php if($current_status == 'Maintenance') echo 'selected'; ?>>Maintenance</option>
                    </select>

                    <button type="submit" name="update_equipment" class="btn-update">Update</button>
                </form>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='4' style='text-align: center;'>No equipment found in the database.</td></tr>";
        }
        ?>
    </table>

</body>
</html>