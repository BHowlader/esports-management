<?php

$name = $_GET["name"] ?? "";
$email_id = $_GET["email_id"] ?? "";
$uni_id = $_GET["uni_id"] ?? "";

$nameErr = $_GET["nameErr"] ?? "";
$emailErr = $_GET["emailErr"] ?? "";
$uni_idErr = $_GET["uni_idErr"] ?? "";
$passwordErr = $_GET["passwordErr"] ?? "";
$confirmPasswordErr = $_GET["confirmPasswordErr"] ?? "";
$termsErr = $_GET["termsErr"] ?? "";

?>
<!DOCTYPE html>

<html>

<head>

    <title>ClubSphere Registration</title>

    <link rel="stylesheet" href="../Css/register.css">

</head>

<body>

    <div class="register-container">

        <img src="../Images/logo.png" class="logo">

        <h1>Register</h1>

        <form action="../Controls/registerControls.php" method="post">

            <div class="form-group">

                <label>Username</label>

                <input type="text" name="name" placeholder="Enter your username" value="<?php echo htmlspecialchars($name); ?>">

                <span class="error"><?php echo $nameErr; ?>
                </span>

            </div>


            <div class="form-group">

                <label>Password</label>

                <input type="password" name="password" placeholder="Enter your password">
                

                <span class="error"><?php echo $passwordErr; ?>
                </span>

            </div>


            <div class="form-group">

                <label>Confirm Password</label>

                <input type="password" name="confirmPassword" placeholder="Re-enter your password">

                <span class="error">
                    <?php echo $confirmPasswordErr; ?>
                </span>

            </div>


            <div class="form-group">

                <label>Email</label>

                <input type="email" name="email_id" placeholder="Enter your email" value="<?php echo htmlspecialchars($email_id); ?>">

                <span class="error">
                    <?php echo $emailErr; ?>
                </span>

            </div>


            <div class="form-group">

                <label>University ID</label>

                <input type="text" name="uni_id" placeholder="Enter your university ID" value="<?php echo htmlspecialchars($uni_id); ?>">

                <span class="error">
                    <?php echo $uni_idErr; ?>
                </span>

            </div>


            <div class="terms">

                <input type="checkbox" name="terms" value="yes">
                

                <span>
                    I agree to the terms and conditions of the club
                </span>

            </div>

            <span class="error terms-error">
                <?php echo $termsErr; ?>
            </span>


            <button type="submit" name="submit">Register</button>

        </form>

    </div>


    <div class="footer">ClubSphere</div>

</body>

</html>