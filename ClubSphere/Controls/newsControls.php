<?php
session_start();

require_once "../Models/newsModel.php";

if(!isset($_SESSION["u_id"]) || !isset($_SESSION["role"]))
{
    header("Location: ../Views/login.php");
    exit();
}

$role = $_SESSION["role"];

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $action = $_POST["action"] ?? "";

    if($action === "add")
    {
        if($role !== "Admin" && $role !== "Moderator")
        {
            echo json_encode([
                "success" => false,
                "message" => "Access denied"
            ]);
            exit();
        }

        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");
        $posted_by = $_SESSION["u_id"];

        if($title === "" || $content === "")
        {
            echo json_encode([
                "success" => false,
                "message" => "All fields are required"
            ]);
            exit();
        }

        if(addNews($title, $content, $posted_by))
        {
            echo json_encode([
                "success" => true,
                "message" => "News posted successfully"
            ]);
        }
        else
        {
            echo json_encode([
                "success" => false,
                "message" => "Failed to post news"
            ]);
        }

        exit();
    }

    if($action === "delete")
    {
        if($role !== "Admin" && $role !== "Moderator")
        {
            echo json_encode([
                "success" => false,
                "message" => "Access denied"
            ]);
            exit();
        }

        $id = intval($_POST["id"] ?? 0);

        if(deleteNews($id))
        {
            echo json_encode([
                "success" => true,
                "message" => "News deleted successfully"
            ]);
        }
        else
        {
            echo json_encode([
                "success" => false,
                "message" => "Failed to delete news"
            ]);
        }

        exit();
    }
}

$result = getAllNews();

$news = [];

while($row = mysqli_fetch_assoc($result))
{
    $news[] = $row;
}

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "news" => $news
]);