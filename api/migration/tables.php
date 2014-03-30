<?php

	$resource->setRequestMethod("GET")->addRequiredParam("migration_id")->validateRequest();

	$migrationId = $_GET["migration_id"];
	$migrationLabel = $graph->makeLabel("Migration");

	$nodes = $migrationLabel->getNodes("migration_id", $migrationId);
	$migrationNode = $nodes[0];

	if ($migrationNode->getProperty("data") !== null) {
		$data = json_decode($migrationNode->getProperty("data"), true);

		$pdo = DB::connection($data["servers"]["mysql"])->pdo;

		$query = $pdo->query("SHOW TABLES FROM {$data['servers']['mysql']['database']}");

		$tables = array();
		while ($table = $query->fetchColumn()) {
			$foreignKeys = $pdo->query("SELECT concat(table_name, '.', column_name) as 'foreign_key',
									           concat(referenced_table_name, '.', referenced_column_name) as 'references'
									      FROM information_schema.key_column_usage
									     WHERE table_name = '{$table}' 
									       AND referenced_column_name IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

			$tables[] = array(
				"name" => $table,
				"foreign_keys" => $foreignKeys
			);
		}

		Response::create(array("success" => "yes", "tables" => $tables))->toJSON()->send();
	}