<?php 
	
/*
 | ================================================
 | Graphist - Aliases
 | ------------------------------------------------
 | Aliases for class loading
 | ================================================
 */
 
return array(
    "ServiceProvider" => "Graphist\Foundation\ServiceProvider",
	"Runnable"        => "Graphist\Foundation\Runnable.php",
	
	"Config"          => "Graphist\Config",
	"Application"     => "Graphist\Application",
	"Request"         => "Graphist\Request",
	"Router"          => "Graphist\Routing\Router",
	"Route"           => "Graphist\Routing\Route",

	"Controller"      => "Graphist\Controller",
	"BaseController"  => "Graphist\Controller\BaseController",
	
	"BaseModel"       => "Graphist\Model\BaseModel",

	"View"            => "Graphist\View",
	"Response"        => "Graphist\Response",

	"DB"              => "Graphist\DB",
	"Connection"      => "Graphist\Database\Connection",
	"Query"           => "Graphist\Database\Query",
	"Syntax"          => "Graphist\Database\Syntax",
	
	"Graph"           => "Graphist\Graph",

	"AES"             => "Graphist\AES",
	"AESCtr"          => "Graphist\AESCtr",

	"Log"             => "Graphist\Log"
);