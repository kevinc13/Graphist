<?php

$resource->setRequestMethod("POST")
	->addRequiredParam("migration_id")
	->addRequiredParam("user_id")
	->addRequiredParam("entities")
	->validateRequest();

$migrationId = $_POST["migration_id"];
$migrationLabel = $graph->makeLabel("Migration");
$migrationNodes = $migrationLabel->getNodes("migration_id", $migrationId);

$entities = json_decode($_POST["entities"], true);

$createIndex = ($entities["create_index"] == "on")? true : false;
unset($entities["create_index"]);

$migrationData = json_decode($migrationNodes[0]->getProperty("data"), true);

$migrationData["entities"] = array();
foreach ($entities as $table => $labelName) {
	$migrationData["entities"][$table] = $labelName;
}

$migrationData["create_index"] = $createIndex;

$migrationNodes[0]->setProperty("data", json_encode($migrationData))->save();

Response::create(array("success" => "yes", "data" => $migrationData))->toJSON()->send();