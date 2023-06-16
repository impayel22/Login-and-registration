<?php

require('connection.php');
session_start(); #We can fetch one variable in multiple pages

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

#For sending email
function sendmail($email,$v_code){

    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';
    require 'phpmailer/Exception.php';

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
       
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'payelcse123@gmail.com';                     //SMTP username
        $mail->Password   = 'HelloP';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    

        $mail->setFrom('payelcse123@gmail.com', 'payel');
        $mail->addAddress($email);     //Add a recipient
      
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Email verification from payel';
        $mail->Body    = "Thanks for registration!
        Click the link below to verify the email address
        <a href= 'http://localhost/password recovery system/verify.php?email=$email&v_code=$v_code'>Verify</a>";

    
        $mail->send();
       return true;
    } catch (Exception $e)
     {
        return false;
    }

}

#for login
if(isset($_POST['login']))
{
    $query= "SELECT * FROM `registered_users` WHERE `email`='$_POST[email_username]' OR `username`='$_POST[email_username]'";
    $result=mysqli_query($con,$query);

    if($result)
    {
        if(mysqli_num_rows($result) == 1)
        {
            $result_fetch= mysqli_fetch_assoc($result);
            if($result_fetch['is_verified']==1)
            {
            if(password_verify($_POST['password'], $result_fetch['password']))
            { 
            #If password matched
            $_SESSION['logged_in']=true;
            $_SESSION['username']=$result_fetch['username'];
            header("location : index.php");

            }
            else
            {
            #IF incorrect password
            echo "
            <script>
            alert('Incorrect Password');
            window.location.href='index.php';
            </script>
            ";
            }
            }
            else
            {
            echo "
            <script>
            alert('Email Not Verified');
            window.location.href='index.php';
            </script>
            ";
            }
            
        
        } 

        else
        {   
        echo "
        <script>
        alert('Email or Username not registered');
        window.location.href='index.php';
        </script>
        ";
        }

    }
    else
    {
        echo "
        <script>
        alert('Cannot Run Query');
        window.location.href='index.php';
        </script>
        ";

    }
}


#For registration
if(isset($_POST['register']))
{

    $user_exist_query="SELECT * FROM `registered_users` WHERE `username` = '$_POST[username]' OR `email`= '$_POST[email]'";
    $result=mysqli_query($con,$user_exist_query);

    if($result){
                  #if user has already taken username or the email id

        if(mysqli_num_rows($result)>0) #It will be executed when user name and email is already taken
        {
            $result_fetch= mysqli_fetch_assoc($result);
            if($result_fetch['username']==$_POST['username'])
        {
            #error for users already taken
        echo "
        <script>
        alert('$result_fetch[username] - Username Already Taken');
        window.location.href='index.php';
        </script> 
        "; }

        else{
          #error for email already taken
        echo "
        <script>
        alert('$result_fetch[email] - Email Already Taken');
        window.location.href='index.php';
        </script> 
        "; }
        }

        else #It will be executed when user name and email is Not taken
        {   
            $password= password_hash($_POST['password'], PASSWORD_BCRYPT);  #Encrypting password using blowfish algorithm
            $v_code=bin2hex(random_bytes(16));
            $query= "INSERT INTO `registered_users`(`full_name`, `username`, `email`, `password`, `verification_code`, `is_verified`) VALUES ('$_POST[fullname]','$_POST[username]','$_POST[email]','$password', '$v_code', '0')";
            if(mysqli_query($con,$query) && sendmail($_POST['email'], $v_code))

            {    #IF data is registered successfully
                echo "
                <script>
                alert('Registration Successful');
                window.location.href='index.php';
                </script>
                ";    

            }
            else  #IF data can not be inserted
            {   echo "
                <script>
                alert('Registration is successful! Unable to send Email');
                window.location.href='index.php';
                </script>
                ";         
            }

        }

    }
    else{
        echo "
        <script>
        alert('Cannot Run Query');
        window.location.href='index.php';
        </script>
        ";
    }


}


?>
