<?php
session_start();

header("Content-Type: application/json");

if(!isset($_SESSION["u_id"]) || ($_SESSION["role"] ?? "") !== "Admin")
{
    echo json_encode([
        "success" => false,
        "message" => "Access denied"
    ]);

    exit();
}

$backupFolder = __DIR__ . "/../backups";

if(!is_dir($backupFolder))
{
    mkdir($backupFolder, 0755, true);
}

$fileName = "clubsphere_backup_" . date("Y-m-d_H-i-s") . ".sql";

$filePath = $backupFolder . "/" . $fileName;

$mysqldump = "mysqldump";

foreach(["C:/xampp/mysql/bin/mysqldump.exe", "/Applications/XAMPP/xamppfiles/bin/mysqldump"] as $path)
{
    if(file_exists($path))
    {
        $mysqldump = $path;
        break;
    }
}

$command = $mysqldump . " -u root clubsphere > " . escapeshellarg($filePath);

exec($command, $output, $result);

if($result === 0 && file_exists($filePath) && filesize($filePath) > 0)
{
    echo json_encode([
        "success" => true,
        "message" => "Database backup created successfully",
        "file" => $fileName
    ]);
}
else
{
    echo json_encode([
        "success" => false,
        "message" => "Backup failed"
    ]);
}