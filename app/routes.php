<?php 

/*
 | ----------------------------------------------------------------------
 | Application Routes
 | ----------------------------------------------------------------------
 */

/*
 | ---------------------------------------------------------------------- 
 | Index Route
 | ----------------------------------------------------------------------
 */
Route::any("/", "login");

/*
 | ---------------------------------------------------------------------- 
 | API Route
 | ----------------------------------------------------------------------
 */
Route::any("/api/(:all)", "api");

Route::any("/SignIn", "login");
Route::any("/SignUp", "register");

Route::get("/console", "console");
Route::get("/console/migrator", "migrator");
Route::get("/console/migrator/(:all)", "migrator");

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