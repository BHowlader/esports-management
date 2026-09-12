<?php

mysqli_report(MYSQLI_REPORT_OFF);

function dbConnection()
{
    $conn = mysqli_connect("localhost", "root", "", "clubsphere");

    if($conn)
    {
        return $conn;
    }
    else
    {
        echo "Connection Failed! " . mysqli_connect_error();
        return false;
    }
}

?>





























