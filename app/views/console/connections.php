<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Graphist - Connections</title>
	<?php View::insert("view_header");?>
	<link rel="stylesheet" href="public/css/app.css">
</head>
<body>
	<section class="global-sidebar">
		<?php View::insert("sidebar_header");?>
		<ul>
			<li>
				<a href="console/connections" class="active">Connections</a>
				<a href="console/migrator">Migration Tool</a>
			</li>
		</ul>
		<?php View::insert("sidebar_footer");?>
	</section>
	<!-- End Head Wrapper -->
	<section id="start" class="content-wrapper console-wrapper">
		<header>Connections</header>
		<p>Manage Neo4j Connections</p>
		<div class="connections"></div>
		<button class="btn green-btn" data-action="create">+ New Connection</button>
	</section>
	<!-- End Content Wrapper -->

	<script data-main="public/js/main.js" src="public/js/require.js"></script>
	<script>
		require(["main"], function(Graphist)
		{
			
		});
	</script>
</body>
</html>