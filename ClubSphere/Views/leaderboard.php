<?php

session_start();

if(!isset($_SESSION["u_id"]))
{
    header("Location: login.php");
    exit();
}

require_once "../Models/leaderboardModel.php";
require_once "../Models/matchModel.php";
require_once "../Models/tournamentModels.php";

$tournaments = getAllTournaments();

if(isset($_GET["view"]))
{
    $view = $_GET["view"];
}
else
{
    $view = "overall";
}

if(isset($_GET["tournament_id"]))
{
    $tournament_id = $_GET["tournament_id"];
}
else
{
    $tournament_id = 0;
}

if($view == "tournament" && $tournament_id > 0)
{
    $standings = getTournamentStandings($tournament_id);
}
else
{
    $view = "overall";
    $standings = getOverallStandings();
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Leaderboard</title>

    <link rel="stylesheet" href="../Css/matchPages.css">

    <script src="../Js/leaderboard.js" defer></script>

</head>

<body>

    <div class="page-container">

        <h1>Leaderboard</h1>

        <?php

        if($_SESSION["role"] == "Member")
        {
            echo '<a href="memberDashboard.php" class="back-button">Back</a>';
        }
        else if($_SESSION["role"] == "Admin")
        {
            echo '<a href="adminDashboard.php" class="back-button">Back</a>';
        }
        else
        {
            echo '<a href="moderatorDashboard.php" class="back-button">Back</a>';
        }

        ?>

        <div class="tabs">

            <a href="leaderboard.php?view=overall"
               class="<?php if($view == "overall") echo "active"; ?>">Club Overall</a>

            <?php while($tournament = mysqli_fetch_assoc($tournaments)) { ?>

                <a href="leaderboard.php?view=tournament&amp;tournament_id=<?php echo $tournament["tournament_id"]; ?>"
                   class="<?php if($view == "tournament" && $tournament["tournament_id"] == $tournament_id) echo "active"; ?>">
                    <?php echo htmlspecialchars($tournament["title"]); ?>
                </a>

            <?php } ?>

        </div>

        <h2>
            <?php

            if($view == "overall")
            {
                echo "Club Standings (all tournaments)";
            }
            else
            {
                echo "Tournament Standings";
            }

            ?>
        </h2>

        <?php

        if(mysqli_num_rows($standings) > 0)
        {
        ?>

        <div class="table-scroll">

        <table id="standingsTable">

            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Team</th>
                    <th>Game</th>
                    <th class="num">P</th>
                    <th class="num">W</th>
                    <th class="num">D</th>
                    <th class="num">L</th>
                    <th class="num">RF</th>
                    <th class="num">RA</th>
                    <th class="num">Diff</th>
                    <th class="num">Pts</th>
                </tr>
            </thead>

            <tbody>

            <?php

            $rank = 0;

            while($team = mysqli_fetch_assoc($standings))
            {
                $rank = $rank + 1;
            ?>

                <tr>

                    <td class="rank"><?php echo $rank; ?></td>

                    <td><b><?php echo htmlspecialchars($team["team_name"]); ?></b></td>

                    <td><?php echo htmlspecialchars($team["game_name"]); ?></td>

                    <td class="num"><?php echo $team["played"]; ?></td>
                    <td class="num"><?php echo $team["won"]; ?></td>
                    <td class="num"><?php echo $team["drawn"]; ?></td>
                    <td class="num"><?php echo $team["lost"]; ?></td>
                    <td class="num"><?php echo $team["rounds_for"]; ?></td>
                    <td class="num"><?php echo $team["rounds_against"]; ?></td>

                    <td class="num">
                        <?php

                        if($team["round_diff"] > 0)
                        {
                            echo "+" . $team["round_diff"];
                        }
                        else
                        {
                            echo $team["round_diff"];
                        }

                        ?>
                    </td>

                    <td class="num"><b><?php echo $team["points"]; ?></b></td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

        </div>

        <p class="hint" style="margin-top:14px">
            P played &middot; W won &middot; D drawn &middot; L lost &middot;
            RF rounds for &middot; RA rounds against &middot; Diff round difference &middot; Pts points (win 3, draw 1, loss 0).
            Click a column heading to re-sort.
        </p>

        <?php
        }
        else
        {
            echo "<p class='no-users'>No standings yet.</p>";
        }
        ?>

    </div>

</body>

</html>
