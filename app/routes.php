<?php 

/*
 | ----------------------------------------------------------------------
 | Application Routes
 | ----------------------------------------------------------------------
 */

/*
 | ---------------------------------------------------------------------- 
 | API v2 Routes
 | ----------------------------------------------------------------------
 */
Route::post("/api/v1/migration/(:num)/servers", "MigrationController#setServers");
Route::post("/api/v1/migration/(:num)/entities", "MigrationController#saveEntities");
Route::post("/api/v1/migration/(:num)/relationships", "MigrationController#saveRelationships");
Route::get("/api/v1/migration/(:num)/tables", "MigrationController#getTables");
Route::post("/api/v1/migration/(:num)/destroy", "MigrationController#deleteMigration");

Route::post("/api/v1/migrations", "MigrationsController#createMigration");
Route::get("/api/v1/migrations", "MigrationsController#getMigrations");

/*
 | ---------------------------------------------------------------------- 
 | Index Route
 | ----------------------------------------------------------------------
 */
Route::any("/", "LoginController");

Route::any("/SignIn", "LoginController");
Route::any("/SignUp", "RegisterController");

Route::get("/console", "ConsoleController");
Route::get("/console/connections", "ConnectionsController");
Route::get("/console/migrator", "MigratorController");
Route::get("/console/migrator/(:all)", "MigratorController");

Route::get("/SignOut", function()
{
	if (isset($_COOKIE["_g_u"]) || isset($_COOKIE["_g_p"]))
	{
		setcookie("_g_u", "", time()-3600);
		setcookie("_g_p", "", time()-3600);	
	}

	$_SESSION = array();

	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
	    $params["path"], $params["domain"],
	    $params["secure"], $params["httponly"]
	);

	session_destroy();

	Response::to_route("/");
});

/*
 | ---------------------------------------------------------------------- 
 | "Catch All" Route
 | ----------------------------------------------------------------------
 */
Route::get("*", function()
{
	Response::error("404");
});