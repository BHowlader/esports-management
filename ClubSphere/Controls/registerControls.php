<?php

require_once "../Models/userModels.php";








if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email_id = trim($_POST["email_id"]);
    $uni_id = trim($_POST["uni_id"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    $nameErr = "";
    $emailErr = "";
    $uni_idErr = "";
    $passwordErr = "";
    $confirmPasswordErr = "";
    $termsErr = "";

    $hasErr = false;






    if (empty($name)) {
        $hasErr = true;
        $nameErr = "Name cannot be empty!";
    }






    if (empty($email_id)) {
        $hasErr = true;
        $emailErr = "Email cannot be empty!";
    } else if (!filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $hasErr = true;
        $emailErr = "Enter a valid email address!";
    }







    if (empty($uni_id)) {
        $hasErr = true;
        $uni_idErr = "University ID cannot be empty!";
    }


    if (empty($password)) {
        $hasErr = true;
        $passwordErr = "Password cannot be empty!";
    } else if (strlen($password) < 8) {
        $hasErr = true;
        $passwordErr = "Password must be at least 8 characters!";
    }







    // Confirm password validation
    if (empty($confirmPassword)) {
        $hasErr = true;
        $confirmPasswordErr = "Please confirm your password!";
    } else if ($password != $confirmPassword) {
        $hasErr = true;
        $confirmPasswordErr = "Passwords do not match!";
    }






    if (!isset($_POST["terms"])) {
        $hasErr = true;
        $termsErr = "You must agree to the terms!";
    }


    if ($hasErr) {
        header("Location:../Views/register.php?name=" . urlencode($name)
            . "&email_id=" . urlencode($email_id)
            . "&uni_id=" . urlencode($uni_id)
            . "&nameErr=" . urlencode($nameErr)
            . "&emailErr=" . urlencode($emailErr)
            . "&uni_idErr=" . urlencode($uni_idErr)
            . "&passwordErr=" . urlencode($passwordErr)
            . "&confirmPasswordErr=" . urlencode($confirmPasswordErr)
            . "&termsErr=" . urlencode($termsErr));

        exit();
    }








    $existingUser = checkUserExists($name, $email_id, $uni_id);

    if (mysqli_num_rows($existingUser) > 0) {
        $user = mysqli_fetch_assoc($existingUser);

        if ($user["name"] == $name) {
            $hasErr = true;
            $nameErr = "Username already exists! Please choose another.";
        }

        if ($user["email_id"] == $email_id) {
            $hasErr = true;
            $emailErr = "Email already exists! Please use another.";
        }

        if ($user["uni_id"] == $uni_id) {
            $hasErr = true;
            $uni_idErr = "University ID already exists! Please use another.";
        }
    }







    if ($hasErr) {
        header("Location:../Views/register.php?name=" . urlencode($name)
            . "&email_id=" . urlencode($email_id)
            . "&uni_id=" . urlencode($uni_id)
            . "&nameErr=" . urlencode($nameErr)
            . "&emailErr=" . urlencode($emailErr)
            . "&uni_idErr=" . urlencode($uni_idErr)
            . "&passwordErr=" . urlencode($passwordErr)
            . "&confirmPasswordErr=" . urlencode($confirmPasswordErr)
            . "&termsErr=" . urlencode($termsErr));

        exit();
    }








    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    // Register user
    $result = registerUser(
        $name,
        $uni_id,
        $email_id,
        $hashedPassword
    );









    // Registration result
    if ($result) {
        header("Location: ../Views/login.php");
        exit();
    } else {
        echo "Registration Failed!";
    }
}

?>