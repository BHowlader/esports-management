<?php
// Include the shared database connection
include 'db.php';

// Handle tracking/assigning equipment usage (FR19)
if (isset($_POST['assign_equipment'])) {
    $id = $_POST['equipment_id'];
    $assigned_to = $conn->real_escape_string($_POST['assigned_to']);
    
    $sql = "UPDATE equipment SET status='In Use', assigned_to='$assigned_to' WHERE id=$id";
    $conn->query($sql);
    
    header("Location: track_equipment.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ClubSphere - Track Equipment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; color: #333; }
        h1 { color: #007bff; }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; color: #333; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #343a40; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        input[type="text"] { padding: 6px; width: 200px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-assign { background-color: #17a2b8; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; }
        .btn-assign:hover { background-color: #138496; }
    </style>
</head>
<body>

    <h1>ClubSphere Equipment Tracking</h1>
    <h2>Track Equipment Usage & Assignment (FR19)</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Equipment Name</th>
            <th>Category</th>
            <th>Current Status</th>
            <th>Assigned Member / Team</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM equipment");
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $current_id = $row['id'];
                $current_name = $row['name'];
                $current_category = $row['category'];
                $current_status = $row['status'];
                $assigned_to = isset($row['assigned_to']) ? $row['assigned_to'] : '';
        ?>
        <tr>
            <td><?php echo $current_id; ?></td>
            <td><strong><?php echo htmlspecialchars($current_name); ?></strong></td>
            <td><?php echo htmlspecialchars($current_category); ?></td>
            <td><?php echo htmlspecialchars($current_status); ?></td>
            <td>
                <form method="POST" action="track_equipment.php" style="margin: 0;">
                    <input type="hidden" name="equipment_id" value="<?php echo $current_id; ?>">
                    <input type="text" name="assigned_to" value="<?php echo htmlspecialchars($assigned_to); ?>" placeholder="Enter member/team name" required>
                    <button type="submit" name="assign_equipment" class="btn-assign">Assign / Update</button>
                </form>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='5' style='text-align: center;'>No equipment registered in inventory. Make sure to add some equipment using equipment.php first.</td></tr>";
        }
        ?>
    </table>

</body>
</html>