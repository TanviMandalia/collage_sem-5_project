<?php
if (!empty($_POST)) {
    extract($_POST);
    $error = array();

    if (empty($fnm)) {
        $error[] = "Please Enter Your First Name";
    }

	if (empty($mnm)) {
        $error[] = "Please Enter Your Middle Name";
    }

	if (empty($lnm)) {
        $error[] = "Please Enter Your Last Name";
    }
   
  if (empty($email)) {
        $error[] = "Please Enter Your Email";
    }

    if (empty($pwd)) {
        $error[] = "Please Enter Your Password";
    }else if ($pwd!=$cpwd){
        $error[] = "Don't Match Password";
    }
    else if (strlen($pwd)<6){
        $error[] = "Please Enter Minimum 6 digit Password";
    }

    if (empty($mno)) {
        $error[] = "Please Enter Your Mobile Number";
    }else if (strlen($mno) != 10 || !is_numeric($mno)) {
        $error[] = "Please Enter Valid Mobile Number";
    }

    
    if (!empty($error)) {
        foreach ($error as $er) {
            echo $er . '<br />';
        }
    } else {
        //echo 'Registration Sucessfully';

        include('include/connection.php');

        $t=time();

		$q = "INSERT INTO users(r_fname, r_mname, r_lname, r_email, r_password, r_mobileno, r_time) VALUES
		('".$fnm."', '".$mnm."', '".$lnm."' ,'".$email."', '".$pwd."', '".$mno."', '".$t."')";

		mysqli_query($conn, $q);

        echo "SuccessFully Register Your Data...";
    }
} else {
    header("location: registration.php");
}
?>