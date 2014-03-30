<?php

/*
 | --------------------------------------------------
 | Get request parameters
 | --------------------------------------------------
 */

	// Migrations may take some time!
	set_time_limit(0);

	$resource->setRequestMethod("POST")
		->addRequiredParam("migration_id")
		->addRequiredParam("user_id")
		->validateRequest();

	$migrationId = $_POST["migration_id"];
	$userId = $_POST["user_id"];
	$produceTSV = false;

/*
 | --------------------------------------------------
 | I. Setup / Obtain Migration Configuration
 | --------------------------------------------------
 */

	$logger = Log::instance()->getLogger("migration_{$migrationId}");

	$migrationLabel = $graph->makeLabel("Migration");
	$migrationNode = $migrationLabel->getNodes("migration_id", $migrationId);
	$migrationNode = $migrationNode[0];

	$migrationConfig = json_decode($migrationNode->getProperty("data"), true);

/*
 | --------------------------------------------------
 | II. Connect to migration servers (MySQL & Neo4j)
 | --------------------------------------------------
 */

	$mysqlConnection = DB::pdo($migrationConfig["servers"]["mysql"]);
	$mysqlConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$mysqlConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$graphConnection = Graph::connect($migrationConfig["servers"]["neo4j"]);

/*
 | --------------------------------------------------
 | III. Create entities and labels
 | --------------------------------------------------
 */

	$labels = array();
	$rowCounts = array();
	$foreignKeys = array();
	$nodes = array();

	/*
	 | --------------------------------------------------
	 | a. Retrieve row counts, labels, and foreign keys
	 | --------------------------------------------------
	 */
	foreach ($migrationConfig["entities"] as $table => $label) {
		$labels[$table] = $graphConnection->makeLabel($label);
		$logger->addNotice("Created label :{$label}");

		$rowCounts[$table] = $mysqlConnection->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
	}

	foreach ($mysqlConnection->query("SELECT table_name, concat(table_name, '.', column_name) as 'foreign_key',
								           concat(referenced_table_name, '.', referenced_column_name) as 'references'
								      FROM information_schema.key_column_usage
								     WHERE referenced_table_schema = \"{$migrationConfig['servers']['mysql']['database']}\"
								       AND referenced_column_name IS NOT NULL") as $tableRel)
	{
		if (isset($foreignKeys[$tableRel["table_name"]]))
		{
			$foreignKeys[$tableRel["table_name"]][] = $tableRel;
		}
		else
		{
			$foreignKeys[$tableRel["table_name"]] = array($tableRel);
		}
	}

	/*
	 | --------------------------------------------------
	 | b. Create nodes
	 | --------------------------------------------------
	 */
	foreach ($rowCounts as $table => $rowCount) {
		$cursor = 0;
		$count = 0;
		$nodes[$table] = array();

		if ($produceTSV) $nodesTSV = fopen("/storage/migrations/{$table}_nodes.txt", "w");

		while ($cursor < $rowCount) {
			foreach ($mysqlConnection->query(sprintf("SELECT * FROM %s LIMIT %d, %d", $table, $cursor, 1000)) as $row) {
				$node = $graphConnection->makeNode();
				foreach ($row as $key => $value)
				{
					if ($produceTSV)
					{
						if (is_numeric($value))
						{
							$node->setProperty($key . ":int", (int)$value);
						}
						else
						{
							$node->setProperty($key, utf8_encode($value));
						}
					}
					else
					{
						if (is_numeric($value))
						{
							$node->setProperty($key, (int)$value);
						}
						else
						{
							$node->setProperty($key, utf8_encode($value));
						}
					}
				}

				if ($produceTSV)
				{
					$node->setProperty("l:label", $labels[$table]);

					if ($cursor == 0)
					{
						fputcsv($nodesTSV, array_keys($node->getProperties()));
					}

					fputcsv($nodesTSV, array_values($node->getProperties()));
				}
				else
				{
					$node->save();
					$node->addLabels(array($labels[$table]));
				}

				$nodes[$table][] = $node;
				$count++;
			}

			$cursor += 1000;
		}

		if ($produceTSV) fclose($nodesTSV);
		$logger->addNotice("Created nodes for table: {$table}");
	}

	/*
	 | --------------------------------------------------
	 | c. Create indexes on labels, if required
	 | --------------------------------------------------
	 */
	// if ($migrationConfig["create_index"]) {
	// 	foreach ($labels as $table => $label) {
	// 		//print "CREATE INDEX ON :{$label->getName()}";
	// 		$query = new Everyman\Neo4j\Cypher\Query($graphConnection, "CREATE INDEX ON :{$label->getName()}");
	// 		$result = $query->getResultSet();

	// 		$logger->addNotice("Added index on :{$label->getName()}");
	// 	}
	// }

/*
 | --------------------------------------------------
 | IV. Create Relationships
 | --------------------------------------------------
 | Essentially, turn all types of "relational-model"
 | relationships (many-to-many, one-to-many, one-to-one)
 | into one-to-one graph relationships
 | --------------------------------------------------
 */

	foreach ($migrationConfig["relationships"] as $relationshipConfig) {

		if (array_key_exists("pivot_table", $relationshipConfig)) {

			/*
			 | --------------------------------------------------
			 | a. Many-to-many
			 | --------------------------------------------------
			 */

			if (array_key_exists($relationshipConfig["pivot_table"], $foreignKeys)) {

				$relationshipStartColumn = "";
				$relationshipEndColumn = "";

				foreach ($foreignKeys[$relationshipConfig["pivot_table"]] as $foreignKey)
				{
					if (stripos($foreignKey["references"], $relationshipConfig["start"]) !== false)
					{
						$relationshipStartColumn = $foreignKey["references"];
					}
					else if (stripos($foreignKey["references"], $relationshipConfig["end"]) !== false)
					{
						$relationshipEndColumn = $foreignKey["references"];
					}
				}

				if (empty($relationshipStartColumn) || empty($relationshipEndColumn))
				{
					die(Response::create(array("success" => "no"))
						->setError("migration_error", "Could not find relationship start or end columns.", 200)
						->toJSON()->send());
				}

				foreach ($mysqlConnection->query("SELECT * FROM {$relationshipConfig['pivot_table']}") as $row) {
					$startColumn = explode(".", $relationshipStartColumn)[1]; // PHP 5.4
					$endColumn = explode(".", $relationshipEndColumn)[1]; // PHP 5.4

					$startLabel = $labels[$relationshipConfig["start"]];
					$startNode = $startLabel->getNodes($startColumn, utf8_encode($row[$startColumn]));
					$startNode = $startNode[0];

					$endLabel = $labels[$relationshipConfig["end"]];
					$endNode = $endLabel->getNodes($endColumn, utf8_encode($row[$endColumn]));
					$endNode = $endNode[0];

					$relationship = $startNode->relateTo($endNode, $relationshipConfig["type"]);

					foreach ($row as $key => $value) {
						if ($key != $startColumn and $key != $endColumn)
						{
							$relationship->setProperty($key, $value);
						}
					}

					$relationship->save();

					$logger->addInfo("Created relationship of type {$relationshipConfig['type']}");
				}
			}
		}
		else
		{
			/*
			 | --------------------------------------------------
			 | b. One-to-many, one-to-one
			 | --------------------------------------------------
			 */

			$foreignKeyColumn = "";
			$referenceColumn = "";
			$reverseRelationship = false;

			foreach ($foreignKeys[$relationshipConfig["start"]] as $foreignKey)
			{
				// Find proper foreign key relationship
				if (stripos($foreignKey["references"], $relationshipConfig["end"]) !== false)
				{
					$foreignKeyColumn = $foreignKey["foreign_key"];
					$referenceColumn = $foreignKey["references"];
					break;
				}
			}

			if (empty($foreignKeyColumn) || empty($referenceColumn))
			{
				foreach ($foreignKeys[$relationshipConfig["end"]] as $foreignKey)
				{
					// Find proper foreign key relationship
					if (stripos($foreignKey["references"], $relationshipConfig["start"]) !== false)
					{
						$foreignKeyColumn = $foreignKey["foreign_key"];
						$referenceColumn = $foreignKey["references"];
						$reverseRelationship = true;
						break;
					}
				}
			}

			list($startTable, $foreignKeyColumn) = explode(".", $foreignKeyColumn);

			list($referenceTable, $referenceColumn) = explode(".", $referenceColumn);
			$referenceLabel = ucfirst($referenceTable);

			foreach ($mysqlConnection->query("SELECT * FROM {$startTable}") as $row)
			{
				$primaryKeys = $mysqlConnection->query("SHOW KEYS FROM {$startTable}
					WHERE key_name = 'PRIMARY'")->fetchAll(PDO::FETCH_ASSOC);

				$primaryKey = "";
				foreach ($primaryKeys as $keyRow)
				{
					if ($keyRow["Column_name"] != $foreignKeyColumn)
					{
						$primaryKey = $keyRow["Column_name"];
						break;
					}
				}

				$startLabel = $labels[$startTable];
				$startNode = $startLabel->getNodes($primaryKey, utf8_encode($row[$primaryKey]));
				$startNode = $startNode[0];

				$endLabel = $labels[$referenceTable];
				$endNode = $endLabel->getNodes($referenceColumn, utf8_encode($row[$foreignKeyColumn]));
				$endNode = $endNode[0];

				$relationship = null;
				if ($reverseRelationship)
				{
					$relationship = $endNode->relateTo($startNode, $relationshipConfig["type"])->save();
				}
				else
				{
					$relationship = $startNode->relateTo($endNode, $relationshipConfig["type"])->save();
				}

				$logger->addInfo("Created relationship of type {$relationshipConfig['type']}");
			}
		}

		$logger->addNotice("Created relationships of type {$relationshipConfig['type']}");
	}

/*
 | --------------------------------------------------
 | V. End Migration
 | --------------------------------------------------
 | Finish up migration process
 | --------------------------------------------------
 */

$logger->addNotice("Migration Completed Successfully");
Response::create(array("success" => "yes"))->toJSON()->send();
