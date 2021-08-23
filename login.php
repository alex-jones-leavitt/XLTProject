<?php
#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

date_default_timezone_set('America/Denver');
$date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="canonical" href="https://getbootstrap.com/docs/3.4/examples/navbar-fixed-top/">

	<title>XLT - CHS</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	
	<!-- Scripts necessary for the weekly date picker -->
	<script src="https://cdn.jsdelivr.net/momentjs/2.10.6/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
	
	<!-- Custom styles for this template -->
	<link href="navbar-fixed-top.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
	<style>
		html,
		body {
		  height: 100%;
		}

		body {
		  display: -ms-flexbox;
		  display: -webkit-box;
		  display: flex;
		  -ms-flex-align: center;
		  -ms-flex-pack: center;
		  -webkit-box-align: center;
		  align-items: center;
		  -webkit-box-pack: center;
		  justify-content: center;
		  padding-top: 40px;
		  padding-bottom: 40px;
		  background-color: #f5f5f5;
		}

		.form-signin {
		  width: 100%;
		  max-width: 330px;
		  padding: 15px;
		  margin: 0 auto;
		}
		.form-signin .checkbox {
		  font-weight: 400;
		}
		.form-signin .form-control {
		  position: relative;
		  box-sizing: border-box;
		  height: auto;
		  padding: 10px;
		  font-size: 16px;
		}
		.form-signin .form-control:focus {
		  z-index: 2;
		}
		.form-signin input[type="email"] {
		  margin-bottom: -1px;
		  border-bottom-right-radius: 0;
		  border-bottom-left-radius: 0;
		}
		.form-signin input[type="password"] {
		  margin-bottom: 10px;
		  border-top-left-radius: 0;
		  border-top-right-radius: 0;
		}
	</style>
  </head>

  <body class="text-center">
    <form class="form-signin" method="post">
      <img class="mb-4" src="reds.png" alt="" height="72">
      <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
      <label for="inputEmail" class="sr-only">Email address</label>
      <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required autofocus>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
      <button class="btn btn-lg btn-primary btn-block" type="submit" style="background-color:#8b2233">Sign in</button>
	  <br>
	  <a href="forgotpassword.php">Forgot Password</a>
	  <br>
	  <a href="resetpass.php">Change Password</a>
    </form>
  </body>
	
</html>
<?php

if(isset($_POST["email"]) && isset($_POST["password"])){
	$email = $_POST["email"];
	$password = $_POST["password"];
	


	// Create connection
	$conn = mysqli_connect($host, $username, $dbpassword, $dbname);

	// Check connection
	if (mysqli_connect_errno()) {
		die("Connection failed: " . mysqli_connect_error());
	}
	//Gather array of data from database
	$loginsql = "SELECT Password, UserType FROM User WHERE Email like '" .$email. "';";
	$loginquery = mysqli_query($conn, $loginsql) or die(mysqli_error($conn));
	if($loginresult = mysqli_fetch_assoc($loginquery)) {
		//Compare login data
		#echo "<script> alert('".password_hash($password, PASSWORD_DEFAULT)." \\n ".$loginresult['Password']."');</script>";
		//$loginSuccess = !strcasecmp($loginresult["Password"], password_hash($password, PASSWORD_DEFAULT));
		$loginSuccess = password_verify($password, $loginresult['Password']); //Successful login
		if($loginSuccess) {
			$_SESSION['email'] = $email;
			if($loginresult["UserType"] == "Teacher") {
				$_SESSION['Type'] = "teacher";
				$_SESSION['CurrentTagDate'] = $date;
				header("Location: teacher.php");
			}
			else if($loginresult["UserType"] == "Student") {
				$_SESSION['Type'] = "student";
				header("Location: student.php");
			}
			else if($loginresult["UserType"] == "Admin") {
				$_SESSION['Type'] = "admin";
				header("Location: admin.php");
			}
			else {
				header("Location: error.php");
			}
		}
		else {
			echo "<script> alert('Username/Password combination incorrect.');</script>";
		}
	}
	else {
		echo "<script> alert('Username/Password combination incorrect.');</script>";
	}
}
?>
