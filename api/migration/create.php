<?php

	$resource->setRequestMethod("POST")->validateRequest();

	$userId = $resource->getUserId();
	$userLabel = $graph->makeLabel("User");
	$userNodes = $userLabel->getNodes("user_id", $resource->getUserId());
	
	$migrationLabel = $graph->makeLabel("Migration");
	$migrationId = Graph::instance()->getNextId("migration_id");

	// Get current time
	$UTC = new DateTimeZone("UTC");
	$now = new DateTime("now", $UTC);
	$createdAt = $now->format(DateTime::RFC1123);

	$migration = $graph->makeNode()
		->setProperty("migration_id", $migrationId)
		->setProperty("created_at", $createdAt)
		->save();
	$migration->addLabels(array($migrationLabel));

	$userNodes[0]->relateTo($migration, "CREATED")->save();

	Response::create(array("success" => "yes", "migration_id" => $migrationId))->toJSON()->send();