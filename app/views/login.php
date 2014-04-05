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
		<h1>
			<a href="<?php echo DOCUMENT_ROOT;?>">GRAPHIST</a>
		</h1>
	</section>
	<!-- End Head Wrapper -->
	<section class="content-wrapper sign-in-wrapper">
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
					<input type="text" name="username" placeholder="Username" value="<?php echo (isset($_POST['username']))? $_POST['username'] : "";?>" autofocus />
				</li>
				<li>	
					<input type="password" name="password" placeholder="Password" />
				</li>
			</ul>
			<input type="submit" class="btn blue-btn" name="login" value="Sign In">
		</form>
	</section>
	<!-- End Content Wrapper -->
</body>
</html>