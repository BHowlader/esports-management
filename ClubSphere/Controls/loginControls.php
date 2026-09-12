<?php

session_start();

require_once "../Models/userModels.php";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $name = $_POST["name"];
    $password = $_POST["password"];

    $result = loginUser($name);

    if(mysqli_num_rows($result) > 0)
    {
        $user = mysqli_fetch_assoc($result);
                                  

                             //Checking credentials
    
        if(password_verify($password, $user["password"]))
        {
            if($user["status"] == "Approved")



            {



               $_SESSION["u_id"] = $user["u_id"];
               $_SESSION["name"] = $user["name"];
               $_SESSION["role"] = $user["role"];

               if($user["role"]=="Admin"){




                header("Location: ../Views/adminDashboard.php");
                exit();
               }
               else if($user["role"]=="Moderator")
               
               
               {
                header("Location: ../Views/moderatorDashboard.php");
                exit();

               }
               else{



                header("Location: ../Views/memberDashboard.php");
                exit();

               }




            }
            else if($user["status"] == "Pending") 
            { 
                header("Location: ../Views/login.php?message=" . urlencode("Your account is waiting for approval."));
                exit();




            } 
            else if($user["status"] == "Rejected") 
            { 
                header("Location: ../Views/login.php?message=" . urlencode("Your account has been rejected."));
                exit();
            }
        }



        
        else 
            { 
                header("Location: ../Views/login.php?message=" . urlencode("Incorrect Password!"));
                exit();
            }
        }
    
        else 
            { 
                header("Location: ../Views/login.php?message=" . urlencode("User not found!"));
                exit();
            }
        
}

?>