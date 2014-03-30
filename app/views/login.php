<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Graphist - Sign In</title>
	<?php View::insert("view_header"); ?>
	<link rel="stylesheet" href="public/css/app.css">
</head>
<body>
	<section class="global-header">
		<ul>
			<h1>
				<a href="<?php echo DOCUMENT_ROOT;?>">Graphist</a>
			</h1>
			<li>
				<a href="SignUp">Sign Up</a>
			</li>
		</ul>
	</section>
	<!-- End Head Wrapper -->
	<section class="content-wrapper">
		<header>Sign In</header>
		<?php 
			if ($_POST) 
			{ 
				if ($errors) 
				{ 
					print $errors;
				} 
			}
		?>
		<form action="SignIn" method="post" class="sign-in-form">
			<ul>
				<li>
					<input type="text" name="email" placeholder="Email" value="<?php echo (isset($_POST['email']))? $_POST['email'] : "";?>" autofocus />
				</li>
				<li>	
					<input type="password" name="password" placeholder="Password" />
				</li>
			</ul>
			<footer>
				<input type="checkbox" name="remember" />
				<label>Remember me</label>
				<input type="submit" class="btn blue-btn" name="login" value="Sign In" />
			</footer>
		</form>
	</section>
	<!-- End Content Wrapper -->
</body>
</html>