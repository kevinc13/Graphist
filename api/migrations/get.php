<?php

	$resource->setRequestMethod("GET")->validateRequest();

	$userId = $resource->getUserId();
	
	$query = new Everyman\Neo4j\Cypher\Query($graph, "MATCH (m:Migration)<-[:CREATED]-(u:User {user_id: {userId}}) RETURN m", 
		array("userId" => $userId));

	$results = $query->getResultSet();

	$migrations = array();

	foreach ($results as $row) {
		$migrationNode = $row["x"];
		$properties = $migrationNode->getProperties();
		
		if (array_key_exists("data", $properties)) {
			$properties["data"] = json_decode($properties["data"], true);
		}

		$migrations[] = $properties;
	}

	Response::create(array("success" => "yes", "migrations" => $migrations))->toJSON()->send();