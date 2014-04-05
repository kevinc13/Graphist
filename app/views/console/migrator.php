<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Graphist - Migrator</title>
	<?php View::insert("view_header");?>
	<link rel="stylesheet" href="public/css/app.css">
</head>
<body>
	<section class="global-sidebar">
		<?php View::insert("sidebar_header");?>
		<ul>
			<li>
				<a href="<?php echo DOCUMENT_ROOT;?>console/connections">Connections</a>
				<a href="console/migrator" class="active">Migration Tool</a>
			</li>
		</ul>
		<?php View::insert("sidebar_footer");?>
	</section>
	<!-- End Head Wrapper -->
	<section id="start" class="content-wrapper console-wrapper" style="display:none;">
		<header>Migrator</header>
		<p>This tool provides an easy way to migrate existing relational database data from MySQL over to Neo4j.</p>
		<div class="migrations"></div>
		<button class="btn green-btn" data-action="create">Create New Migration</button>
	</section>
	<section id="mysql" class="content-wrapper console-wrapper" style="display:none;">
		<header>MySQL Connection Information</header>
		<form action="" method="post">
			<ul>
				<li><input type="text" name="host" placeholder="Host"></li>
				<li><input type="text" name="port" placeholder="Port"></li>
				<li><input type="text" name="database" placeholder="Database"></li>
				<li><input type="text" name="username" placeholder="Username"></li>
				<li><input type="password" name="password" placeholder="Password"></li>
				<li>
					<button class="btn green-btn" data-action="save">Save</button>
				</li>
			</ul>
		</form>
		<a href="index" class="btn gray-btn back">Back</a>
	</section>
	<section id="neo4j" class="content-wrapper console-wrapper" style="display:none;">
		<header>Neo4j Connection Information</header>
		<form action="" method="post">
			<ul>
				<li><input type="text" name="host" placeholder="Host"></li>
				<li><input type="text" name="port" placeholder="Port"></li>
				<li><input type="text" name="username" placeholder="Username (if available)"></li>
				<li><input type="password" name="password" placeholder="Password (if available)"></li>
				<li>
					<button class="btn green-btn" data-action="save">Save</button>
				</li>
			</ul>
		</form>
		<a href="mysql" class="btn gray-btn back">Back</a>
	</section>
	<section id="entities" class="content-wrapper console-wrapper" style="display:none;">
		<header>Entities</header>
		<p>Select which tables store <strong>entity data</strong> <em>(ex. User, Actor, Movie, Post etc.)</em></p>
		<div class="tables"></div>
		<form action="" method="post">
			<header>Labels</header>
			<p>Provide labels for your entities. Labels are used to find your data once it's in Neo4j. (ex. "users_table" -> "User")</p>
			<div class="create-index">
				<label style="display:inline-block;">Create Index on Labels</label>
				<input type="checkbox" name="create_index" checked="checked">
			</div>
			<button class="btn green-btn" data-action="save">Save</button>
		</form>
		<a href="neo4j" class="btn gray-btn back">Back</a>
	</section>
	<section id="relationships" class="content-wrapper console-wrapper" style="display:none;">
		<header>Relationships</header>
		<button class="btn gray-btn" data-action="add-relationship">Add Relationship</button>
		<form action="" method="post">
			<button class="btn green-btn" data-action="save">Save</button>
		</form>
		<a href="entities" class="btn gray-btn back">Back</a>
	</section>
	<section id="execute" class="content-wrapper console-wrapper" style="display:none;">
		<header>Execute Migration</header>
		<p>Your migration will be executed with the following configuration:</p>
		<pre class="config"></pre>
		<a href="relationships" class="btn gray-btn back">Back</a>
		<button class="btn green-btn" data-action="execute">Execute</button>
	</section>
	<!-- End Content Wrapper -->
	
	<?php View::insert("templates/migration_template"); ?>
	<?php View::insert("templates/table_template"); ?>

	<script data-main="public/js/main.js" src="public/js/require.js"></script>
	<script>
		require(["main"], function(Graphist)
		{	
			Graphist.globals.user = <?php print json_encode(array("user_id" => $_SESSION["user_id"], "username" => $_SESSION["username"])); ?>;

			var all = $(".content-wrapper"),
				start = $("#start"),
				mysql = $("#mysql"),
				neo4j = $("#neo4j"),
				entities = $("#entities"),
				relationships = $("#relationships"),
				execute = $("#execute"),
				nav = $(".global-sidebar ul");

			var MigratorRouter = Backbone.Router.extend({
				
				routes: {
					"mysql": "mysql",
					"neo4j": "neo4j",
					"entities": "entities",
					"relationships": "relationships",
					"execute": "execute",
					"*action": "index"
				},
				
				index: function()
				{
					all.hide();
					Graphist.globals.migration = {};
					Graphist.deleteCookie("migration");
					Graphist.API.functions.migrations.get().done(function()
					{
						start.fadeIn(400);
					}).fail(function() 
					{
						start.append("Failed retrieving migrations.");
					});
				},
				
				mysql: function()
				{
					all.hide();

					var migrationData;

					if (Graphist.globals.hasOwnProperty("migration")) {
						migrationData = Graphist.globals.migration;
					} else if (Graphist.getCookie("migration")) {
						Graphist.globals.migration = migrationData = JSON.parse(Graphist.getCookie("migration"));
					} else {
						this.navigate("index", {trigger: true});
						return;
					}

					if (migrationData.hasOwnProperty("data")) {
						var data = migrationData.data.servers.mysql;

						mysql.find('input[name="host"]').val(data.host).end()
							.find('input[name="port"]').val(data.port).end()
							.find('input[name="database"]').val(data.database).end()
							.find('input[name="username"]').val(data.username).end()
							.find('input[name="password"]').val(data.password).end();
					} else {
						mysql.find("input").val("").on("focus blur keyup", function()
						{
							if (mysql.find("input").filter(function () {
								    return $.trim($(this).val()).length > 0
								}).length == 5) {
								console.log("yes");
								mysql.find('[data-action="save"]').removeAttr("disabled");
							} else {
								mysql.find('[data-action="save"]').attr("disabled", "disabled");
							}
						});
					}

					mysql.fadeIn(400);
				},

				neo4j: function()
				{
					all.hide();

					var migrationData;

					if (Graphist.globals.hasOwnProperty("migration")) {
						migrationData = Graphist.globals.migration;
					} else if (Graphist.getCookie("migration")) {
						Graphist.globals.migration = migrationData = JSON.parse(Graphist.getCookie("migration"));
					} else {
						this.navigate("index", {trigger: true});
						return;
					}

					if (migrationData.hasOwnProperty("data")) {
						if (migrationData.data.servers.hasOwnProperty("neo4j")) {
							var data = migrationData.data.servers.neo4j;

							neo4j.find('input[name="host"]').val(data.host).end()
								.find('input[name="port"]').val(data.port).end()
								.find('input[name="username"]').val(data.username).end()
								.find('input[name="password"]').val(data.password).end();
						}
					} else {
						neo4j.find('input').val("");
					}

					neo4j.fadeIn(400);
				},

				entities: function()
				{
					all.hide();

					if (Graphist.globals.hasOwnProperty("migration")) {
						migrationData = Graphist.globals.migration;
					} else if (Graphist.getCookie("migration")) {
						migrationData = JSON.parse(Graphist.getCookie("migration"));
					} else {
						this.navigate("index", {trigger: true});
						return;
					}

					Graphist.globals.migration = migrationData;

					$(".label-input").remove();

					Graphist.API.functions.migration.getTables(entities.find(".tables"), false).done(function()
					{
						if (migrationData.data.hasOwnProperty("entities")) {
							for (var key in migrationData.data.entities) {

								entities.find(".table").filter(function() {
								    return $(this).find("header").text() === key;
								}).attr("data-label", migrationData.data.entities[key]).click();
							}
						}

						if (migrationData.data.hasOwnProperty("create_index")) {
							if (migrationData.data.create_index) {
								$(".create-index input:checkbox").attr("checked", "checked");
							} else {
								$(".create-index input:checkbox").removeAttr("checked");
							}
						}

						entities.fadeIn(400);
					}).fail(function()
					{

					});
				},

				relationships: function() {
					all.hide();

					var migrationData;

					if (Graphist.globals.hasOwnProperty("migration")) {
						migrationData = Graphist.globals.migration;
					} else if (Graphist.getCookie("migration")) {
						migrationData = JSON.parse(Graphist.getCookie("migration"));
						Graphist.globals.migration = migrationData;
					} else {
						this.navigate("index", {trigger: true});
						return;
					}

					if (migrationData.data.hasOwnProperty("relationships")) {
						var form = relationships.find("form");

						Graphist.API.functions.migration.getTables(form, true).done(function(tables)
						{
							Graphist.globals.cache.tables = tables;
							form.find(".relationship-input").remove();

							for (var i = migrationData.data.relationships.length - 1; i >= 0; i--) {
								generateRelationshipInput(tables);
								var input = form.find(".relationship-input:last-of-type");

								input.find('[name="start"] option[value="'+migrationData.data.relationships[i].start+'"]')
										.prop("selected", "selected").end()
									.find('[name="end"] option[value="'+migrationData.data.relationships[i].end+'"]')
										.prop("selected", "selected").end()
									.find('[name="type"]').val(migrationData.data.relationships[i].type).end()
									.find('[name="direction"]').val(migrationData.data.relationships[i].direction); 

								if (migrationData.data.relationships[i].hasOwnProperty("pivot_table")) {
									input.find('[data-action="add-pivot-table"]').click();
									input.find('[name="pivot_table"] option[value="'+migrationData.data.relationships[i].pivot_table+'"]').prop("selected", "selected");
								}
							};
						});
					}

					relationships.fadeIn(400);
				},

				execute: function()
				{
					all.hide();

					if (Graphist.globals.hasOwnProperty("migration")) {
						migrationData = Graphist.globals.migration;
					} else if (Graphist.getCookie("migration")) {
						Graphist.globals.migration = migrationData = JSON.parse(Graphist.getCookie("migration"));
					} else {
						this.navigate("index", {trigger: true});
						return;
					}

					Graphist.deleteCookie("migration");
					document.cookie = "migration=" + JSON.stringify(migrationData) + ";";
					execute.find("pre").text(JSON.stringify(migrationData, null, 4));
					execute.fadeIn(400);
				}
			});

			var router = new MigratorRouter(),
			    enablePushState = true,
			    pushState = !!(enablePushState && window.history && window.history.pushState),
			    documentRoot = $("base").attr("href");
			
			if (pushState) {
				Backbone.history.start({ pushState: true, root: documentRoot + "console/migrator/" });

				function route(e)
				{
					var href = $(e.currentTarget).attr("href").replace(/^\//,'').replace('\#\!\/','');
					
					e.preventDefault();
	
					router.navigate(href, { trigger: true });
					
					return false;
				}
				
				$(".content-wrapper").on("click", "a.next", route).on("click", ".back", route);
			} else {
				Backbone.history.start({ pushState: false, root: documentRoot + "console/migrator/" });
			}

			start.on("click", ".migration", function()
			{
				var $this = $(this);

				var migration = {
					"migration_id": $this.attr("data-migration-id")
				}

				if ($this.attr("data-migration-data")) {
					migration.data = JSON.parse($this.attr("data-migration-data"));
				}

				// Cache migration data
				Graphist.globals.migration = migration;

				// Also save the migration data to cookie just in case the user refreshes the page
				Graphist.deleteCookie("migration");
				document.cookie = "migration=" + JSON.stringify(migration) + ";";
				router.navigate("mysql", { trigger: true });
			});

			start.find("[data-action='create']").on("click", function(e)
			{
				var $this = $(this);
				Graphist.API.functions.migration.create().done(function()
				{
					Graphist.API.functions.migrations.get();
				}).fail(function()
				{
					console.log("failed");
				});
			});

			start.on("click", ".migration [data-action='destroy']", function(e)
			{
				e.stopPropagation();

				var parent = $(e.toElement).parents(".migration");

				Graphist.API.functions.migration.destroy(parent.attr("data-migration-id")).done(function()
				{
					parent.remove();
				}).fail(function()
				{
					console.log("failed");
				});
			});

			mysql.find("form").on("submit", function(e)
			{
				e.preventDefault();
				var $this = $(this);

				var formData = $this.serializeObject();
				formData.user_id = Graphist.globals.user.user_id;
				formData.type = "mysql";
				formData.migration_id = Graphist.globals.migration.migration_id;

				Graphist.API.functions.migration.addServer(formData).done(function()
				{
					Graphist.notify("green", "Saved MySQL Server Connection", 2000);
					$this.find("input").prop("disabled", true);
					$this.find("button").hide();
					mysql.append('<a href="neo4j" class="btn green-btn next">Next > Neo4j</a>');
				}).fail(function()
				{
					$this.append("Error saving MySQL server connection");
				});
			});

			neo4j.find("form").on("submit", function(e)
			{
				e.preventDefault();
				var $this = $(this);

				var formData = $this.serializeObject();
				formData.user_id = Graphist.globals.user.user_id;
				formData.type = "neo4j";
				formData.migration_id = Graphist.globals.migration.migration_id;

				Graphist.API.functions.migration.addServer(formData).done(function()
				{
					Graphist.notify("green", "Saved Neo4j Server Connection", 2000);
					$this.find("input").prop("disabled", true);
					$this.find("button").hide();
					neo4j.append('<a href="entities" class="btn green-btn next">Next > Entity Selection</a>');
				}).fail(function()
				{
					$this.append("Error saving Neo4j server connection");
				});
			});

			entities.on("click", ".table", function()
			{
				var $this = $(this),
				    form = entities.find("form"),
				    tableName = $this.find("header").text();

				if ($this.hasClass("selected")) {

					$this.removeClass("selected");

					form.find('label:contains("' + tableName + '")').remove().end()
						.find('input[name="' + tableName + '"]').parent().remove().end()
						.find('input[name="' + tableName + '"]').remove();
				} else {
					$this.addClass("selected");

					var label = ($this.attr("data-label"))? $this.attr("data-label") : "";

					$('<div class="label-input"><label>' + tableName +  '</label><input type="text" name="' + tableName + '" placeholder="Label Name"></div>')
					.find("input:text").val(label).end().insertBefore(form.find('[data-action="save"]'));

					// if (label.length > 0) {
					// 	entities.find(".label-input input").attr("disabled", "disabled");
					// }
				}
			});

			entities.find("form").on("submit", function(e)
			{
				e.preventDefault();
				var $this = $(this);

				var entityData = $this.serializeObject();
				var requestData = {
					user_id: Graphist.globals.user.user_id,
					migration_id: Graphist.globals.migration.migration_id,
					entities: JSON.stringify(entityData)
				};

				Graphist.API.functions.migration.saveEntities(requestData).done(function()
				{
					Graphist.notify("green", "Saved Entities Data", 2000);
					$this.find("input").prop("disabled", true);
					$this.find("button").hide();
					entities.append('<a href="relationships" class="btn green-btn next">Next > Relationships</a>');
				}).fail(function()
				{

				});
			});

			function generateTablesSelect(name, tables)
			{
				var html = '<select name="'+name+'">';

				for (var i = tables.length - 1; i >= 0; i--) {
					html += '<option value="'+tables[i].name+'">'+tables[i].name+'</option>';
				};

				html += '</select>';
				return html;
			}

			function generateRelationshipInput(tables)
			{
				var relationshipInputHTML = '<div class="relationship-input">';
					
				relationshipInputHTML += '<section><label>Start</label>';
				relationshipInputHTML += generateTablesSelect("start", tables);
				relationshipInputHTML += '</section>';

				relationshipInputHTML += '<section><label>Type</label><input type="text" name="type"></section>';
				relationshipInputHTML += '<section><label>Direction</label><input type="text" name="direction" value="->"></section>';
				
				relationshipInputHTML += '<section><label>End</label>';
				relationshipInputHTML += generateTablesSelect("end", tables);
				relationshipInputHTML += '</section>';

				relationshipInputHTML += '<a href="#" class="btn blue-btn" data-action="add-pivot-table">Add Pivot Table</a>';

				relationshipInputHTML += '</div>';
				$(relationshipInputHTML).insertBefore(relationships.find("form").find('[data-action="save"]'));
			}

			relationships.find('[data-action="add-relationship"]').on("click", function(e)
			{
				e.preventDefault();
				var form = relationships.find("form");
			
				if (Graphist.globals.cache.hasOwnProperty("tables")) {
					generateRelationshipInput(Graphist.globals.cache.tables);
				} else {
					Graphist.API.functions.migration.getTables(relationships.find("form"), true).done(function(tables)
					{
						Graphist.globals.cache.tables = tables;
						generateRelationshipInput(tables);	
					});
				}
			});

			relationships.on("click", '[data-action="add-pivot-table"]', function(e)
			{
				e.preventDefault();
				var $this = $(this),
					html = '<section><label>Pivot Table</label>';

				$this.hide();
				$this.parent(".relationship-input").append(html + generateTablesSelect("pivot_table", Graphist.globals.cache.tables) + '</section>');
				$this.parent(".relationship-input").append('<a href="#" class="btn red-btn" data-action="remove-pivot-table">Remove Pivot Table</a>');
				// if (Graphist.globals.cache.hasOwnProperty("tables")) {
					
				// } else {
				// 	Graphist.API.functions.migration.getTables(relationships.find("form"), true).done(function(tables)
				// 	{
				// 		$this.parent(".relationship-input").append(html + generateTablesSelect("pivot_table", tables) + '</section>');
				// 		$this.parent(".relationship-input").append('<a href="#" class="btn red-btn" data-action="remove-join-table">Remove Pivot Table</a>');		
				// 	});
				// }
			});

			relationships.on("click", '[data-action="remove-pivot-table"]', function(e)
			{
				e.preventDefault();
				var $this = $(this);

				$this.parent('.relationship-input').find('[data-action="add-pivot-table"]').show();
				$this.prev('section').remove();
				$this.remove();
			});

			relationships.find("form").on("submit", function(e)
			{
				e.preventDefault();
				var form = $(this),
					relationshipsArray = [];

				form.find("div").each(function()
				{
					relationshipsArray.push($(this).find("input, select").serializeObject());
				});

				Graphist.API.functions.migration.saveRelationships(relationshipsArray).done(function()
				{
					Graphist.notify("green", "Saved Relationships", 2000);
					form.find("input").prop("disabled", true);
					form.find("button").hide();
					relationships.append('<a href="execute" class="btn green-btn next">Next > Execute</a>');
					Graphist.API.functions.migrations.get();
				}).fail(function()
				{
					
				});
			});

			execute.on("click", '[data-action="execute"]', function()
			{
				var $this = $(this);
				$this.prop("disabled", "disabled");
				execute.find("pre").after('<div><div>Performing Migration...</div><div>Please Wait</div></div>');

				Graphist.API.functions.migration.execute(Graphist.globals.migration.migration_id).done(function()
				{
					Graphist.notify("green", "Migration Succeeded", 4000);
				}).fail(function()
				{
					Graphist.notify("red", "Migration Failed", 4000);
				});

				execute.find("pre").next("div").remove();
			});
		});
	</script>
</body>
</html>