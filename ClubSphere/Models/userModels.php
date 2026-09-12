<?php
require_once "dbConnect.php";
function registerUser($name,$uni_id, $email_id, $password)
{
    $conn=dbConnection();

    if($conn){
        $sql="INSERT INTO users (name, uni_id, email_id, password) Values (?,?,?,?)";


        $stmt=mysqli_prepare($conn,$sql);
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $name,
            $uni_id,
            $email_id,
            $password
        );

        if(mysqli_stmt_execute($stmt))
        {
            return true;

        }
        else{
            return false;

        }
    }
    else{
        return false;
    }
}
                         //check if existing

function checkUserExists($name, $email_id, $uni_id)
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, email_id, uni_id
            FROM users
            WHERE name = ? OR email_id = ? OR uni_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $name,
        $email_id,
        $uni_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return $result;
}

function loginUser($name)
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, email_id, uni_id, password, role, status
            FROM users
            WHERE name = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $name
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return $result;
}
function updateProfile($u_id, $game_type, $ranking, $social_link)
{
    $conn = dbConnection();

    $sql = "UPDATE users
            SET game_type = ?, ranking = ?, social_link = ?
            WHERE u_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $game_type,
        $ranking,
        $social_link,
        $u_id
    );

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getPendingUsers()
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, uni_id, email_id, status
            FROM users
            WHERE status = 'Pending'";

    $result = mysqli_query($conn, $sql);

    return $result;
}
function updateUserStatus($u_id, $status)
{
    $conn = dbConnection();

    $sql = "UPDATE users
            SET status = ?
            WHERE u_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $status,
        $u_id
    );

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}
function updateUserRole($u_id, $role)
{
    $conn = dbConnection();

    $sql = "UPDATE users
            SET role = ?
            WHERE u_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $role,
        $u_id
    );

    if(mysqli_stmt_execute($stmt))
    {
        return true;
    }
    else
    {
        return false;
    }
}


function getApprovedUsers()
{
    $conn = dbConnection();

    $sql = "SELECT u_id, name, uni_id, email_id, role, status
            FROM users
            WHERE status = 'Approved'";

    $result = mysqli_query($conn, $sql);

    return $result;
}

function getUserProfile($u_id)
{
    $conn = dbConnection();

    $sql = "SELECT game_type, ranking, social_link
            FROM users
            WHERE u_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $u_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

function getTotalApprovedMembers()
{
    $conn = dbConnection();

    $sql = "SELECT COUNT(*) AS total
            FROM users
            WHERE status = 'Approved'";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    return $row["total"];
}
              
            /*AJAX*/

            
function getRegistrationData()
    {
        $conn = dbConnection();
            
        $sql = "SELECT COUNT(*) AS total FROM users WHERE created_at >= NOW() - INTERVAL 3 MINUTE";
            
            $result = mysqli_query($conn, $sql);
            
            $row = mysqli_fetch_assoc($result);
            
            return $row["total"];
    }





