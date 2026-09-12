<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}


require_once "../Models/teamModels.php";
require_once "../Models/userModels.php";


  /*Nobody reaches the team pages without logging in first*/

if(!isset($_SESSION["u_id"]))
{
    header("Location: ../Views/login.php?message=" . urlencode("Please login first!"));
    exit();
}


$u_id = $_SESSION["u_id"];
$role = $_SESSION["role"];



if($_SERVER["REQUEST_METHOD"] == "POST")
{


                   /* FR6 - Create a new team */

    if(isset($_POST["createTeam"]))
    {
        $team_name = trim($_POST["team_name"]);
        $game_name = trim($_POST["game_name"]);
        $description = trim($_POST["description"]);

        $team_nameErr = "";
        $game_nameErr = "";

        $hasErr = false;


        if(empty($team_name))
        {
            $hasErr = true;
            $team_nameErr = "Team name cannot be empty!";
        }
        else if(strlen($team_name) < 3)
        {
            $hasErr = true;
            $team_nameErr = "Team name must be at least 3 characters!";
        }
        else if(isTeamNameTaken($team_name))
        {
            $hasErr = true;
            $team_nameErr = "This team name already exists! Please choose another.";
        }


        if(empty($game_name))
        {
            $hasErr = true;
            $game_nameErr = "Please select a game!";
        }


        if($hasErr)
        {
            header("Location: ../Views/createTeam.php?team_name=" . urlencode($team_name)
                . "&description=" . urlencode($description)
                . "&team_nameErr=" . urlencode($team_nameErr)
                . "&game_nameErr=" . urlencode($game_nameErr));

            exit();
        }


        $team_id = createTeam($team_name, $game_name, $description, $u_id);

        if($team_id > 0)
        {
            header("Location: ../Views/teamDetails.php?team_id=" . $team_id
                . "&message=" . urlencode("Team created successfully!"));
            exit();
        }
        else
        {
            header("Location: ../Views/createTeam.php?team_nameErr="
                . urlencode("Team could not be created!"));
            exit();
        }
    }



                   /* FR6 - Invite a member */

    else if(isset($_POST["inviteMember"]))
    {
        $team_id = $_POST["team_id"];
        $invite_id = $_POST["invite_id"];

        $team = getTeamById($team_id);

        if(!$team)
        {
            header("Location: ../Views/teams.php?message=" . urlencode("Team not found!"));
            exit();
        }

        /* only the captain of that team may send invitations */

        if($team["captain_id"] != $u_id)
        {
            header("Location: ../Views/teamDetails.php?team_id=" . $team_id
                . "&message=" . urlencode("Only the team captain can invite members!"));
            exit();
        }

        if(empty($invite_id))
        {
            header("Location: ../Views/teamDetails.php?team_id=" . $team_id
                . "&message=" . urlencode("Please select a member to invite!"));
            exit();
        }

        if(inviteMember($team_id, $invite_id))
        {
            header("Location: ../Views/teamDetails.php?team_id=" . $team_id
                . "&message=" . urlencode("Invitation sent!"));
        }
        else
        {
            header("Location: ../Views/teamDetails.php?team_id=" . $team_id
                . "&message=" . urlencode("This member is already invited or already in the team!"));
        }

        exit();
    }



                   /* FR6 - Accept or decline an invitation */

    else if(isset($_POST["respondInvite"]))
    {
        $team_member_id = $_POST["team_member_id"];
        $answer = $_POST["answer"];

        if(respondToInvite($team_member_id, $u_id, $answer))
        {
            if($answer == "accept")
            {
                header("Location: ../Views/myInvites.php?message=" . urlencode("You joined the team!"));
            }
            else
            {
                header("Location: ../Views/myInvites.php?message=" . urlencode("Invitation declined."));
            }
        }
        else
        {
            header("Location: ../Views/myInvites.php?message=" . urlencode("Invitation could not be updated!"));
        }

        exit();
    }



                   /* FR7 - Moderator edits the team structure */

    else if(isset($_POST["updateTeam"]))
    {
        if($role != "Moderator" && $role != "Admin")
        {
            echo "Access Denied!";
            exit();
        }

        $team_id = $_POST["team_id"];
        $team_name = trim($_POST["team_name"]);
        $game_name = trim($_POST["game_name"]);
        $description = trim($_POST["description"]);
        $status = $_POST["status"];
        $captain_id = $_POST["captain_id"];

        if(empty($team_name))
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Team name cannot be empty!"));
            exit();
        }

        if(updateTeam($team_id, $team_name, $game_name, $description, $status, $captain_id))
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Team updated successfully!"));
        }
        else
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Team could not be updated!"));
        }

        exit();
    }



                   /* FR7 - Moderator changes a team role */

    else if(isset($_POST["updateRole"]))
    {
        if($role != "Moderator" && $role != "Admin")
        {
            echo "Access Denied!";
            exit();
        }

        $team_id = $_POST["team_id"];
        $team_member_id = $_POST["team_member_id"];
        $team_role = $_POST["team_role"];

        if(updateMemberRole($team_member_id, $team_role))
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Member role updated!"));
        }
        else
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Role could not be updated!"));
        }

        exit();
    }



                   /* FR7 - Moderator removes a member */

    else if(isset($_POST["removeMember"]))
    {
        if($role != "Moderator" && $role != "Admin")
        {
            echo "Access Denied!";
            exit();
        }

        $team_id = $_POST["team_id"];
        $team_member_id = $_POST["team_member_id"];

        if(removeMemberFromTeam($team_member_id))
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("Member removed from the roster."));
        }
        else
        {
            header("Location: ../Views/editTeam.php?team_id=" . $team_id
                . "&message=" . urlencode("The captain cannot be removed, change the captain first!"));
        }

        exit();
    }
}




        /* data the team pages display */

$allTeams = getAllTeams();

$myTeams = getTeamsOfUser($u_id);

$myInvites = getMyInvites($u_id);

$totalTeams = getTotalTeams();


$team = null;
$roster = array();
$invitableUsers = null;

if(isset($_GET["team_id"]))
{
    $team = getTeamById($_GET["team_id"]);

    if($team)
    {
        $rosterResult = getTeamRoster($_GET["team_id"]);

        while($row = mysqli_fetch_assoc($rosterResult))
        {
            $roster[] = $row;
        }

        $invitableUsers = getInvitableUsers($_GET["team_id"]);
    }
}

?>
