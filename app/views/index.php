<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo $title;?></title>
	<?php View::insert("view_header");?>

	<link rel="stylesheet" href="public/css/base.css">
	<link rel="stylesheet" href="public/css/app.css">
</head>
<body>
	<header class="global-header">
		<ul>
			<h1><a href="<?php echo DOCUMENT_ROOT;?>">Graphist</a></h1>
			<li>
				<a href="SignIn">Sign In</a>
			</li>
			<li>
				<a href="SignUp">Sign Up</a>
			</li>
		</ul>
	</header>
	<section class="content-wrapper">
		<header>Hello There</header>
	</section>
</body>
</html>