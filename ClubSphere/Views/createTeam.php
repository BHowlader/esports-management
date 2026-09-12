<?php

session_start();

require_once "../Controls/teamControls.php";


  /*Only a Member creates a team (FR6)*/

if($role != "Member")
{
    echo "Access Denied!";
    exit();
}


$team_name = $_GET["team_name"] ?? "";
$description = $_GET["description"] ?? "";

$team_nameErr = $_GET["team_nameErr"] ?? "";
$game_nameErr = $_GET["game_nameErr"] ?? "";

?>


<!DOCTYPE html>
<html>

<head>

    <title>Create a Team</title>

    <link rel="stylesheet" href="../Css/teamForm.css">

</head>

<body>

    <div class="form-container">

        <h1>Create a New Team</h1>

        <a href="teams.php" class="back-button">Back</a>

        <p class="note">You become the captain of the team you create.</p>


        <form action="../Controls/teamControls.php" method="post">


            <div class="form-group">

                <label>Team Name</label>

                <input type="text" name="team_name" placeholder="e.g. Asterisk"
                       value="<?php echo htmlspecialchars($team_name); ?>">

                <span class="error"><?php echo htmlspecialchars($team_nameErr); ?></span>

            </div>


            <div class="form-group">

                <label>Game</label>

                <select name="game_name">

                    <option value="">-- select a game --</option>
                    <option value="Valorant">Valorant</option>
                    <option value="MLBB">MLBB</option>
                    <option value="CS2">CS2</option>
                    <option value="PUBG Mobile">PUBG Mobile</option>
                    <option value="EA FC">EA FC</option>

                </select>

                <span class="error"><?php echo htmlspecialchars($game_nameErr); ?></span>

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea name="description"
                          placeholder="Short description of the roster"><?php echo htmlspecialchars($description); ?></textarea>

            </div>


            <button type="submit" name="createTeam">Create Team</button>


        </form>

    </div>

</body>

</html>
