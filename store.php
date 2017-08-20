<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'stores');
	
$_SESSION['message'] = '';
$_SESSION['token'] = '';
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		#SIGN UP
		
		if (isset($_POST['sign-up'])){
		
			//check if two passwords are equal to each other
			if($_POST['password'] == $_POST['confirmpassword']) {
				
				//sanitize the users input
				$email = trim($mysqli->real_escape_string($_POST['email']));
				$password = md5($_POST['password']); //md5 hash password security
				
				$email = strip_tags($email);

				//insert password and email into the database
				$sql = "INSERT INTO users (email, password) " . "VALUES ('$email', '$password')";
				
				if ($mysqli->query($sql) === true) {
					$_SESSION['message'] = "Registration succesful! Added $email to the database";#display success message
					$_SESSION['token'] = md5(uniqid(mt_rand(), true));#generate a random token
					setcookie('token', $_SESSION['token'], time()+(60*60*24*7));#save token as cookie
				}
				else {
					echo "<script>alert('User could not be added to the database!');</script>";
				}
			}
			else {
				echo "<script>alert('Passwords do not match!');</script>";
			}
		}

		
		#LOGIN

		if (isset($_POST['login'])){

			if(isset($_POST['password']) && isset($_POST['email'])){
				
				//sanitize the users input
				$email = trim($mysqli->real_escape_string($_POST['email']));
				$password = md5($_POST['password']); //md5 hash password security
				
				$email = strip_tags($email);

				//check if password and email correspond to any in the database
				$sql= "SELECT * FROM users WHERE email = '".$email."' and password = '".$password."'";
				$result = $mysqli->query($sql);
				
				if($check = mysqli_fetch_assoc($result)){
					$_SESSION['message'] = "Logged in as $email";#display success message
					$_SESSION['token'] = md5(uniqid(mt_rand(), true));#generate a random token
					setcookie('token', $_SESSION['token'], time()+(60*60*24*7));#save token as cookie
				}
				else {
					echo "<script>alert('Wrong Email or Password!');</script>";
				}
			}
		}
		
		#ADD STORE

		if (isset($_POST['add-store'])){
			
			if(isset($_POST['storename']) && isset($_POST['address']) && (isset($_POST['csrf']) == $_COOKIE['token'])){
				
				//sanitize the users input
				$storename = trim($mysqli->real_escape_string($_POST['storename']));
				$address = trim($mysqli->real_escape_string($_POST['address']));
				$storename = strip_tags($storename);
				$address = strip_tags($address);
				
				//insert input into the database
				$sql = "INSERT INTO address (storename, address) " . "VALUES ('$storename', '$address')";

				if ($mysqli->query($sql) === true) {
					$_SESSION['message'] = "Added $storename to the list!";#display success messages
				}
				else {
					echo "<script>alert('User could not be added to the list!');</script>";
				}
			}
		}
	}

	#get all storename and address in database
	$sql = 'SELECT storename FROM address';
	$lqs = 'SELECT address FROM address';
	$result = $mysqli->query($sql);
	$response = $mysqli->query($lqs);
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="HandheldFriendly" content="true">

		<title>Welcome</title>

		<link rel="stylesheet" type="text/css" href="style.css" />
		<link rel="stylesheet" type="text/css" href="bootstrap.min.css" />
	</head>
	<body>
		<div class="container">
			<div style="color: green; font-size: 1.5em; margin-top: 1em; font-family: calibri; text-align: center;">
				<?php echo $_SESSION['message'] ?>
			</div>
			<h3 style="text-align: center;">Welcome </h3>

			<div id="registered">
				<span><h2 style="text-align: center; margin-top: 3em; font-family: consolas;">All registered Stores</h2></span>
				<?php
					echo "<div class='container'>
						<div class='row'>
							<div class='col-md-6 col-xs-6'>
								<h3><b>Name Of Store</b></h3>";
								if ($result) {
									//Display all storename in database
									while ($row = $result->fetch_assoc()) {
										echo "<h3><span>$row[storename]</span></h3>";
									}
								}
							echo "</div>
							
							<div class='col-md-6 col-xs-6' align='right'>
								<h3><b>Address</b></h3>";
								if ($response) {
									//Display all address in database
									while ($row = $response->fetch_assoc()) {
										echo "<h3><span>$row[address]</span></h3>";
									}
								}
							echo "</div>
						</div>
					</div>";
				?>
			</div>
			
		</div>
		
		<div class="container">
			<div style="margin: 5em 0em 0em 1em;">
				<?php
					if ( $_SESSION['message']) {
						echo "<button onclick='openAddStore()' class='btn btn-block btn-primary' style='border-radius: 0;'>ADD YOUR OWN STORE NOW!</button>";
					} else {
						echo "<button onclick='openSignUp()' class='btn btn-block btn-primary' style='border-radius: 0;'>SIGN UP/LOGIN TO ADD STORE!</button>";
					}
				?>
			</div>
		</div>
		
		
			<!-- FORMS -->
		
				<!-- Sign up -->
		
		<div class="sign-up" id="sign-up">
			<span onclick="closeSignUp()" class="signUpClose" title="Close" > &times; </span>
		
			<div class="sign-up-content">
				<h2>REGISTER NOW!</h2>
				
				<form action="store.php" method="post" enctype="multipart/form-data" autocomplete="off" style="font-family: consolas;">
					<font size="3"> Sign up to add your own store! </font>
					
					<input type="email" class="form-control" placeholder="Email" name="email" required>
					
					<input type="password" class="form-control" placeholder="Password" name="password" autocomplete="new-password" required>
				
					<input type="password" class="form-control" placeholder="Confirm Password" name="confirmpassword" autocomplete="new-password" required>

					<input type="submit" class="btn btn-block btn-primary" name="sign-up"></input>
					
					<p>already have an account?</p>
					<a onclick="openLogin(), closeSignUp()">LOGIN</a>
				</form>
			</div>
		</div>
		
				<!-- Login -->
		
		<div class="login" id="login">
			<span onclick="closeLogin()" class="loginClose" title="Close" > &times; </span>
			
			<div class="login-content">
				<h2>LOGIN!</h2>
				
				<form action="store.php" method="post" enctype="multipart/form-data" autocomplete="off" style="font-family: consolas;">
					<font size="3"> Login to add your own store! </font>
				
					<input type="email" class="form-control" placeholder="Email" name="email" required>
					
					<input type="password" class="form-control" placeholder="Password" name="password" autocomplete="new-password" required>

					<input type="submit" class="btn btn-block btn-primary" name="login"></input>
				</form>
			</div>
		</div>
		
				<!-- Add Store -->
				
		<div class="add-store" id="add-store">
			<span onclick="closeAddStore()" class="addStoreClose" title="Close" > &times; </span>
			
			<div class="add-store-content">
				<h2>Add Your Store!</h2>
				
				<form action="store.php" method="post" enctype="multipart/form-data" autocomplete="off" style="font-family: consolas;">
					<input type="text" class="form-control" placeholder="Store Name" name="storename" required>
					
					<input type="text" class="form-control" placeholder="Address" name="address" required>
					
					<input type="hidden" name="csrf" value="<?php echo $_SESSION['token']; ?>" required>

					<input type="submit" class="btn btn-block btn-primary" name="add-store"></input>
				</form>
			</div>
		</div>

		<script src="script.js"></script>
        <script src="bootstrap.min.js" ></script>
	</body>
</html>