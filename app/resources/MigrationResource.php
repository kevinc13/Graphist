<?php

class MigrationResource extends Resource
{
    /**
     * Sets server configuration
     * for a given migration
     * 
     * @param integer $migrationId
     * @return string [description]
     */
    public function setServers($migrationId)
    {
		$this->setRequestMethod("POST")
            ->addRequiredParam("host")
            ->addRequiredParam("port")
            ->addRequiredParam("type")
            ->addRequiredParam("username")
            ->addRequiredParam("password")
            ->validateRequest();

        $host = $_POST["host"];
        $port = $_POST["port"]; 
        $type = $_POST["type"];
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Validation
        $validTypes = array("neo4j", "mysql");
        if (!in_array($type, $validTypes)) $this->error("invalid_database_type", "The database type provided was invalid.", 200);

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
                $this->error("invalid_database_connection", "A database connection could not be established.", 200);
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
                $this->error("invalid_database_connection", "A database connection could not be established.", 200);
            }
        }

        // Save connection information
        $migrationLabel = $this->graph->makeLabel("Migration");
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

        return Response::create(array("success" => "yes", "data" => $migrationDataArray))->toJSON()->getData();		
    }

    /**
     * Saves entities configuration
     * for a given migration
     * 
     * @param  integer $migrationId
     * @return string
     */
    public function saveEntities($migrationId)
    {
    	$this->setRequestMethod("POST")
            ->addRequiredParam("user_id")
            ->addRequiredParam("entities")
            ->validateRequest();

        $migrationLabel = $this->graph->makeLabel("Migration");
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

        return Response::create(array("success" => "yes", "data" => $migrationData))->toJSON()->getData();
    } 

    /**
     * Retrieves associated relational database 
     * tables for a given migration
     * 
     * @param  integer $migrationId
     * @return string
     */
    public function getTables($migrationId)
    {
    	$this->setRequestMethod("GET")->validateRequest();

        $migrationLabel = $this->graph->makeLabel("Migration");

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

            return Response::create(array("success" => "yes", "tables" => $tables))->toJSON()->getData();
        }	
        else
        {
            $this->error("no_migration_config", "Migration does not have any configuration yet", 200);
        }
    }

    /**
     * Saves relationship configuration for a migration
     * 
     * @param  integer $migrationId
     * @return string
     */
    public function saveRelationships($migrationId)
    {
    	$this->setRequestMethod("POST")
            ->addRequiredParam("user_id")
            ->addRequiredParam("relationships")
            ->validateRequest();

        $migrationLabel = $this->graph->makeLabel("Migration");
        $migrationNodes = $migrationLabel->getNodes("migration_id", $migrationId);

        $relationships = json_decode($_POST["relationships"], true);

        $migrationData = json_decode($migrationNodes[0]->getProperty("data"), true);

        $migrationData["relationships"] = array();
        foreach ($relationships as $table => $labelName) {
            $migrationData["relationships"][$table] = $labelName;
        }

        $migrationNodes[0]->setProperty("data", json_encode($migrationData))->save();

        return Response::create(array("success" => "yes", "data" => $migrationData))->toJSON()->getData();
    } 

    /**
     * Performs a migration
     * 
     * @param  integer $migrationId
     * @return string
     */
    public function executeMigration($migrationId)
    {
        /*
         | --------------------------------------------------
         | Get request parameters
         | --------------------------------------------------
         */

            // Migrations may take some time!
            set_time_limit(0);

            $this->setRequestMethod("POST")   
                ->addRequiredParam("user_id")
                ->validateRequest();

            $userId = $_POST["user_id"];
            $produceTSV = false;

        /*
         | --------------------------------------------------
         | I. Setup / Obtain Migration Configuration
         | --------------------------------------------------
         */

            $logger = Log::instance()->getLogger("migration_{$migrationId}");

            $migrationLabel = $this->graph->makeLabel("Migration");
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

            $this->graphConnection = Graph::connect($migrationConfig["servers"]["neo4j"]);

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
                $labels[$table] = $this->graphConnection->makeLabel($label);
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
                        $node = $this->graphConnection->makeNode();
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
            //  foreach ($labels as $table => $label) {
            //      //print "CREATE INDEX ON :{$label->getName()}";
            //      $query = new Everyman\Neo4j\Cypher\Query($this->graphConnection, "CREATE INDEX ON :{$label->getName()}");
            //      $result = $query->getResultSet();

            //      $logger->addNotice("Added index on :{$label->getName()}");
            //  }
            // }

        /*
         | --------------------------------------------------
         | IV. Create Relationships
         | --------------------------------------------------
         | Essentially, turn all types of "relational-model"
         | relationships (many-to-many, one-to-many, one-to-one)
         | into one-to-one this->graph relationships
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
        return Response::create(array("success" => "yes"))->toJSON()->getData();
    }

    /**
     * Deletes a migration
     * 
     * @param  integer $migrationId
     * @return string
     */
    public function deleteMigration($migrationId)
    {
    	$this->setRequestMethod("POST")
            ->addRequiredParam("user_id")
            ->validateRequest();

        $userId = $_POST["user_id"];
     
        $query = new Everyman\Neo4j\Cypher\Query($this->graph, "MATCH (m:Migration {migration_id: {$migrationId}})<-[c:CREATED]-(u:User {user_id: {$userId}}) DELETE c,m RETURN 1");
        
        try {
            $query->getResultSet();
            return Response::create(array("success" => "yes"))->toJSON()->send();
        } catch (Exception $e) {
            $this->error("query_failed", "Your migration could not be deleted.", 200);
        }
    }
}