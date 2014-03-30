<?php

$resource->setRequestMethod("POST")
	->addRequiredParam("migration_id")
	->addRequiredParam("user_id")
	->addRequiredParam("relationships")
	->validateRequest();

$migrationId = $_POST["migration_id"];
$migrationLabel = $graph->makeLabel("Migration");
$migrationNodes = $migrationLabel->getNodes("migration_id", $migrationId);

$relationships = json_decode($_POST["relationships"], true);

$migrationData = json_decode($migrationNodes[0]->getProperty("data"), true);

$migrationData["relationships"] = array();
foreach ($relationships as $table => $labelName) {
	$migrationData["relationships"][$table] = $labelName;
}

$migrationNodes[0]->setProperty("data", json_encode($migrationData))->save();

Response::create(array("success" => "yes", "data" => $migrationData))->toJSON()->send();