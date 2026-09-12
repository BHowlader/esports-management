<?php

require_once "dbConnect.php";

/*
    teamModels.php

    FR6 - Members shall be able to create new teams and invite other members.
    FR7 - Moderators shall be able to edit team structures and manage
          member lists.
*/




                    /* FR6 - create a team */

function createTeam($team_name, $game_name, $description, $captain_id)
{
    $conn = dbConnection();

    if($conn)
    {
        $created_date = date("Y-m-d");

        $sql = "INSERT INTO team (team_name, game_name, description, created_date, captain_id)
                VALUES (?,?,?,?,?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $team_name,
            $game_name,
            $description,
            $created_date,
            $captain_id
        );

        if(mysqli_stmt_execute($stmt))
        {
            $team_id = mysqli_insert_id($conn);


            /* the member who creates the team becomes its captain */

            $sql2 = "INSERT INTO team_member (team_id, u_id, team_role, joined_date, status)
                     VALUES (?,?,'Captain',?,'Accepted')";

            $stmt2 = mysqli_prepare($conn, $sql2);

            mysqli_stmt_bind_param(
                $stmt2,
                "iis",
                $team_id,
                $captain_id,
                $created_date
            );

            mysqli_stmt_execute($stmt2);

            return $team_id;
        }
        else
        {
            return 0;
        }
    }
    else
    {
        return 0;
    }
}


function isTeamNameTaken($team_name)
{
    $conn = dbConnection();

    $sql = "SELECT team_id FROM team WHERE team_name = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $team_name);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}




                    /* FR6 - invite a member */

function inviteMember($team_id, $u_id)
{
    $conn = dbConnection();

    /* a member cannot be invited to the same team twice */

    $sql = "SELECT team_member_id, status FROM team_member
            WHERE team_id = ? AND u_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $team_id, $u_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_assoc($result);

        if($row["status"] == "Invited" || $row["status"] == "Accepted")
        {
            return false;
        }

        /* the member declined or was removed before, so invite again */

        $sql2 = "UPDATE team_member SET status = 'Invited', team_role = 'Player'
                 WHERE team_member_id = ?";

        $stmt2 = mysqli_prepare($conn, $sql2);

        mysqli_stmt_bind_param($stmt2, "i", $row["team_member_id"]);

        return mysqli_stmt_execute($stmt2);
    }

    $sql3 = "INSERT INTO team_member (team_id, u_id, team_role, status)
             VALUES (?,?,'Player','Invited')";

    $stmt3 = mysqli_prepare($conn, $sql3);

    mysqli_stmt_bind_param($stmt3, "ii", $team_id, $u_id);

    return mysqli_stmt_execute($stmt3);
}


function respondToInvite($team_member_id, $u_id, $answer)
{
    $conn = dbConnection();

    $joined_date = date("Y-m-d");

    if($answer == "accept")
    {
        $sql = "UPDATE team_member SET status = 'Accepted', joined_date = ?
                WHERE team_member_id = ? AND u_id = ? AND status = 'Invited'";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "sii", $joined_date, $team_member_id, $u_id);
    }
    else
    {
        $sql = "UPDATE team_member SET status = 'Declined'
                WHERE team_member_id = ? AND u_id = ? AND status = 'Invited'";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "ii", $team_member_id, $u_id);
    }

    return mysqli_stmt_execute($stmt);
}


function getMyInvites($u_id)
{
    $conn = dbConnection();

    $sql = "SELECT tm.team_member_id, t.team_id, t.team_name, t.game_name,
                   u.name AS captain_name
            FROM team_member tm
            JOIN team t  ON t.team_id = tm.team_id
            JOIN users u ON u.u_id = t.captain_id
            WHERE tm.u_id = ? AND tm.status = 'Invited'
            ORDER BY tm.team_member_id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $u_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


/* Approved members who are not already in this team, for the invite box */

function getInvitableUsers($team_id)
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, uni_id, game_type, ranking
            FROM users
            WHERE status = 'Approved'
              AND u_id NOT IN (SELECT u_id FROM team_member
                               WHERE team_id = ? AND status IN ('Invited','Accepted'))
            ORDER BY name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $team_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}




                    /* reading teams */

function getAllTeams()
{
    $conn = dbConnection();

    $sql = "SELECT t.*, u.name AS captain_name,
                   (SELECT COUNT(*) FROM team_member tm
                    WHERE tm.team_id = t.team_id AND tm.status = 'Accepted') AS member_count
            FROM team t
            JOIN users u ON u.u_id = t.captain_id
            ORDER BY t.team_name ASC";

    return mysqli_query($conn, $sql);
}


function getTeamById($team_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.*, u.name AS captain_name
            FROM team t
            JOIN users u ON u.u_id = t.captain_id
            WHERE t.team_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $team_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}


function getTeamRoster($team_id)
{
    $conn = dbConnection();

    $sql = "SELECT tm.team_member_id, tm.u_id, tm.team_role, tm.joined_date, tm.status,
                   u.name, u.uni_id, u.game_type, u.ranking
            FROM team_member tm
            JOIN users u ON u.u_id = tm.u_id
            WHERE tm.team_id = ? AND tm.status IN ('Invited','Accepted')
            ORDER BY FIELD(tm.team_role,'Captain','Player','Substitute','Coach'), u.name";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $team_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


function getTeamsOfUser($u_id)
{
    $conn = dbConnection();

    $sql = "SELECT t.*, tm.team_role,
                   (SELECT COUNT(*) FROM team_member x
                    WHERE x.team_id = t.team_id AND x.status = 'Accepted') AS member_count
            FROM team_member tm
            JOIN team t ON t.team_id = tm.team_id
            WHERE tm.u_id = ? AND tm.status = 'Accepted'
            ORDER BY t.team_name";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $u_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}


/* teams this user captains, used by the tournament registration (FR9) */

function getTeamsCaptainedBy($u_id)
{
    $conn = dbConnection();

    $sql = "SELECT * FROM team
            WHERE captain_id = ? AND status = 'Active'
            ORDER BY team_name";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $u_id);

    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}




                    /* FR7 - Moderator edits the team */

function updateTeam($team_id, $team_name, $game_name, $description, $status, $captain_id)
{
    $conn = dbConnection();

    $sql = "UPDATE team
            SET team_name = ?, game_name = ?, description = ?, status = ?, captain_id = ?
            WHERE team_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssii",
        $team_name,
        $game_name,
        $description,
        $status,
        $captain_id,
        $team_id
    );

    if(mysqli_stmt_execute($stmt))
    {
        /* the new captain must hold the Captain role inside the roster too */

        $sql2 = "UPDATE team_member SET team_role = 'Player'
                 WHERE team_id = ? AND team_role = 'Captain' AND u_id <> ?";

        $stmt2 = mysqli_prepare($conn, $sql2);

        mysqli_stmt_bind_param($stmt2, "ii", $team_id, $captain_id);

        mysqli_stmt_execute($stmt2);


        $sql3 = "UPDATE team_member SET team_role = 'Captain', status = 'Accepted'
                 WHERE team_id = ? AND u_id = ?";

        $stmt3 = mysqli_prepare($conn, $sql3);

        mysqli_stmt_bind_param($stmt3, "ii", $team_id, $captain_id);

        mysqli_stmt_execute($stmt3);

        return true;
    }
    else
    {
        return false;
    }
}


function updateMemberRole($team_member_id, $team_role)
{
    $conn = dbConnection();

    $sql = "UPDATE team_member SET team_role = ? WHERE team_member_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "si", $team_role, $team_member_id);

    return mysqli_stmt_execute($stmt);
}


function removeMemberFromTeam($team_member_id)
{
    $conn = dbConnection();

    /* the captain can never be removed, the captain has to be changed first */

    $sql = "UPDATE team_member SET status = 'Removed'
            WHERE team_member_id = ? AND team_role <> 'Captain'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $team_member_id);

    mysqli_stmt_execute($stmt);

    if(mysqli_stmt_affected_rows($stmt) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}


function getTotalTeams()
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total FROM team WHERE status = 'Active'";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    return $row["total"];
}

?>
