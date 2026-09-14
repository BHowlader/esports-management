<!DOCTYPE html>
<html>

<head>
    <title>ClubSphere Login</title>
    <link rel="stylesheet" href="../Css/login.css">
</head>

<body>






    <div class="login-container">

        <img src="../Images/logo.png" class="logo">

        <h1>Login</h1>

        <form action="../Controls/loginControls.php" method="post">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="name" placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password">
            </div>







            <a href="#">Reset Password</a><br>


            <button type="submit" name="submit">Login</button>

            <a href="register.php" class="switch-link">Don't have an account? Register</a>


            <?php

            if (isset($_GET["message"])) {
                echo "<p class='error-message' style='color: red;'>" . htmlspecialchars($_GET["message"]) . "</p>";

            }

            ?>





        </form>

    </div>

    <div class="footer">ClubSphere</div>

</body>

</html>