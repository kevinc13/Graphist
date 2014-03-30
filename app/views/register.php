<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Graphist - Sign Up</title>
	<?php View::insert("view_header"); ?>
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
		<header>Sign Up</header>
		<?php 
			if ($_POST) {
				if ($success) {
					print '<div class="message success-message">';
					print "You are now signed up!";
					print '<a href="console" style="display:block;">Go to my console</a>';
					print '</div>';
				} else {
					print $errors;
				}
			}
		?>
		<form action="SignUp" method="post" class="sign-up-form">
			<ul>
				<li>
					<input type="text" name="name" placeholder="Full Name" />
					<span>Enter your first and last name</span>
				</li>
				<li>
					<input type="text" name="email" placeholder="Email" />
					<span>Enter your email</span>
				</li>
				<li>
					<input type="password" name="password" placeholder="Password" />
					<span>At least 6 characters, the trickier the better!</span>
				</li>
			</ul>
			<footer>
				<input type="hidden" name="timezone" id="timezone" value="America/New_York" />
				<input type="submit" name="register" class="btn green-btn" value="Sign Me Up" />
			</footer>
		</form>
	</section>
	<!-- End Content Wrapper -->
	<script src="public/js/require.js"></script>
	<script>
		require.config({
			baseUrl: "public/js",
			paths: {
				jquery: "lib/jquery",
				validation: "lib/validation",
				jstz: "lib/jstz",
			}
		});
		
		require(["jquery", "validation", "jstz"], function() {
		
			$("#timezone").val(jstz.determine().name());

			var form = $('.sign-up-form');

			var fullname = form.find('input[name="name"]'),
			    password = form.find('input[name="password"]'),
			    email = form.find('input[name="email"]');

		    fullname.on('blur', function() {
		    	var $this = $(this),
		    	    tooltip = $this.siblings('span');

		    	if ($this.val().length === 0) {
		    		tooltip.html('Your full name is required');
		    	} else {
		    		tooltip.html('Name looks great!');
		    	}
		    });

		    email.on('blur', function() {

		    	var $this = $(this),
		    	    tooltip = $this.siblings('span');

		    	if (Validation.isBlank( $this.val() )) {

		    		tooltip.html("An email is required");

		    	} else if (!Validation.validEmail( $this.val() )) {

		    		tooltip.html('Email is not valid');

		    	} else {

		    		tooltip.html('We will email you a confirmation');

		    	}

		    });

		    password.on('blur', function() {
		    	var $this = $(this),
		    	    tooltip = $this.siblings('span');

		    	if (Validation.isBlank( $this.val() )) {

		    		tooltip.html('A password is required');

		    	} else if (!Validation.correctPassLen( $this.val() )) {

		    		tooltip.html('Password must be at least 6 characters');

		    	} else {

		    		tooltip.html('Password looks great!');

		    	}
		    });

		});
	</script>
</body>
</html>