<?php 
/*
 | ==============================================================
 | File: core.php
 | --------------------------------------------------------------
 | Includes the core foundation elements
 | ==============================================================
 */

/*
 | ==============================================================
 | Load Core Files
 | ==============================================================
 */

require "vendor/autoload.php";
require "Graphist/Foundation/ClassLoader.php";
require "Graphist/Foundation/AliasLoader.php";
require "Graphist/helpers.php";

$aliases = include("app/config/aliases.php");

/*
 | ==============================================================
 | Start autoloading sequence
 | ==============================================================
 */

Graphist\Foundation\ClassLoader::register();
Graphist\Foundation\AliasLoader::getInstance($aliases)->register();

/*
 | ==============================================================
 | Load JSON configuration files
 | ==============================================================
 */

Graphist\Config::load();

/*
 | ==============================================================
 | Set Default Timezone
 | ==============================================================
 */

date_default_timezone_set(Graphist\Config::get("application.default_timezone"));

/*
 | ==============================================================
 | Error Configuration
 | ==============================================================
 */

ini_set('display_errors', Graphist\Config::get("application.display_errors"));

error_reporting(-1);

/*
 | ==============================================================
 | Define Global Constants 
 | ==============================================================
 */

define("DOCUMENT_ROOT", Graphist\Config::get("application.document_root"));
define("EXT", ".php");

/*
 | ==============================================================
 | Register Routes
 | ==============================================================
 */

require "app/routes.php";

/*
 | ==============================================================
 | Sessions
 | ==============================================================
 */

session_start();