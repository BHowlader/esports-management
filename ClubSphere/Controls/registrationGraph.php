<?php
session_start();

require_once "../Models/userModels.php";

$total = getRegistrationData();





header("Content-Type: application/json");

echo json_encode([
    "total" => $total
]);

?>