<?php
// Include the shared database connection
include 'db.php';

// Handle adding a new expense (FR16)
if (isset($_POST['add_expense'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $amount = $_POST['amount'];
    $category = $conn->real_escape_string($_POST['category']);
    $date = $_POST['date'];

    $sql = "INSERT INTO expenses (title, amount, category, date) VALUES ('$title', '$amount', '$category', '$date')";
    $conn->query($sql);
    
    header("Location: expenses.php");
    exit();
}

// Calculate total expenses for financial summary (FR17)
$total_result = $conn->query("SELECT SUM(amount) AS total_expense FROM expenses");
$total_row = $total_result->fetch_assoc();
$total_expense = $total_row['total_expense'] ? $total_row['total_expense'] : 0.00;
?>
<!DOCTYPE html>
<html>
<head>
    <title>ClubSphere - Expense Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; color: #333; }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .summary-box { background: #d4edda; border-left: 5px solid #28a745; padding: 15px; border-radius: 5px; font-size: 18px; font-weight: bold; color: #155724; margin-bottom: 20px; }
        .form-box { background: white; padding: 20px; width: 350px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #dc3545; color: white; padding: 10px; width: 100%; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #343a40; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style>
</head>
<body>

    <h1>ClubSphere Financial Panel</h1>

    <!-- FR17: Financial Status Summary -->
    <h2>Club Financial Status Summary</h2>
    <div class="summary-box">
        Total Expenses Logged: $<?php echo number_format($total_expense, 2); ?>
    </div>

    <!-- FR16: Log Expenses Form -->
    <h2>Log New Expense</h2>
    <div class="form-box">
        <form method="POST" action="expenses.php">
            <label>Expense Title:</label>
            <input type="text" name="title" required placeholder="e.g. Equipment Repair">

            <label>Amount ($):</label>
            <input type="number" step="0.01" name="amount" required placeholder="0.00">

            <label>Category:</label>
            <input type="text" name="category" required placeholder="e.g. Logistics">

            <label>Date:</label>
            <input type="date" name="date" required>

            <button type="submit" name="add_expense">Log Expense</button>
        </form>
    </div>

    <!-- Expense History Table -->
    <h2>Expense History Logs</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Date</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM expenses ORDER BY date DESC");
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong></td>";
                echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . $row['date'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align: center;'>No expenses logged yet.</td></tr>";
        }
        ?>
    </table>

</body>
</html>