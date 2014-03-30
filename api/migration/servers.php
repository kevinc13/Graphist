<?php

$resource->setRequestMethod("POST")
	->addRequiredParam("host")
	->addRequiredParam("port")
	->addRequiredParam("type")
	->addRequiredParam("username")
	->addRequiredParam("password")
	->addRequiredParam("migration_id")
	->validateRequest();

$host = $_POST["host"];
$port = $_POST["port"];	
$type = $_POST["type"];
$username = $_POST["username"];
$password = $_POST["password"];

$migrationId = $_POST["migration_id"];

// Validation
$validTypes = array("neo4j", "mysql");
if (!in_array($type, $validTypes)) $resource->error("invalid_database_type", "The database type provided was invalid.", 200);

if ($type == "neo4j")
{
	$connectionInfo = array(
		"host" => $host,
		"port" => $port,
		"username" => $username,
		"password" => $password
	);

	// Test Neo4j Connection
	try {
		$client = Graph::testConnection($connectionInfo);
	} catch (Exception $e) {
		$resource->error("invalid_database_connection", "A database connection could not be established.", 200);
	}
}
else
{
	$connectionInfo = array(
		"host" => $host,
		"port" => $port,
		"database" => $_POST["database"],
		"username" => $username,
		"password" => $password
	);

	// Test MySQL connection
	try {
		DB::connection($connectionInfo);
	} catch (PDOException $e) {
		$resource->error("invalid_database_connection", "A database connection could not be established.", 200);
	}
}

// Save connection information
$migrationLabel = $graph->makeLabel("Migration");
$migrationNodes = $migrationLabel->getNodes("migration_id", $migrationId);

if (!is_null($migrationNodes[0]->getProperty("data"))) {
	$migrationDataArray = json_decode($migrationNodes[0]->getProperty("data"), true);

	$migrationDataArray["servers"]["{$type}"] = $connectionInfo;
	$migrationNodes[0]->setProperty("data", json_encode($migrationDataArray))->save();
} else {
	$migrationDataArray = array(
		"servers" => array(
			"{$type}" => $connectionInfo
		)
	);

	$migrationNodes[0]->setProperty("data", json_encode($migrationDataArray))->save();
}

Response::create(array("success" => "yes", "data" => $migrationDataArray))->toJSON()->send();