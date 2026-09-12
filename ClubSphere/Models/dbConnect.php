<?php

/*
    From PHP 8.1 mysqli throws an exception on every SQL error, so a
    duplicate username during registration, or the same team registered
    twice, would end the request with a fatal error page instead of
    showing the friendly message. Turning the report mode off makes
    mysqli_stmt_execute() return false, which is what the models check.
*/
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





























