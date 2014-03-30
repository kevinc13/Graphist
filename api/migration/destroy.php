<?php

	$resource->setRequestMethod("POST")
		->addRequiredParam("migration_id")
		->addRequiredParam("user_id")
		->validateRequest();

	$userId = $_POST["user_id"];
	$migrationId = $_POST["migration_id"];
 
	$query = new Everyman\Neo4j\Cypher\Query($graph, "MATCH (m:Migration {migration_id: {$migrationId}})<-[c:CREATED]-(u:User {user_id: {$userId}}) DELETE c,m RETURN 1");
	
	try {
		$query->getResultSet();
		Response::create(array("success" => "yes"))->toJSON()->send();
	} catch (Exception $e) {
		Response::create(array("success" => "no"))->setError("query_failed", "Your migration could not be deleted.", 200)->toJSON()->send();
	}