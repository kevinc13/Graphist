<?php

class MigrationsResource extends Resource
{
	/**
     * Creates a migration
     * 
     * @param  integer $migrationId
     * @return string
     */
	public function createMigration()
    {
    	$this->setRequestMethod("POST")->validateRequest();

        $userId = $this->getUserId();
        $userLabel = $this->graph->makeLabel("User");
        $userNodes = $userLabel->getNodes("user_id", $this->getUserId());
        
        $migrationLabel = $this->graph->makeLabel("Migration");
        $migrationId = Graph::instance()->getNextId("migration_id");

        // Get current time
        $UTC = new DateTimeZone("UTC");
        $now = new DateTime("now", $UTC);
        $createdAt = $now->format(DateTime::RFC1123);

        $migration = $this->graph->makeNode()
            ->setProperty("migration_id", $migrationId)
            ->setProperty("created_at", $createdAt)
            ->save();
        $migration->addLabels(array($migrationLabel));

        $userNodes[0]->relateTo($migration, "CREATED")->save();

        return Response::create(array("success" => "yes", "migration_id" => $migrationId))->toJSON()->getData();
    }

    public function getMigrations()
    {
        $this->setRequestMethod("GET")->validateRequest();

        $userId = $this->getUserId();
        
        $query = new Everyman\Neo4j\Cypher\Query($this->graph, "MATCH (m:Migration)<-[:CREATED]-(u:User {user_id: {userId}}) RETURN m", 
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

        return Response::create(array("success" => "yes", "migrations" => $migrations))->toJSON()->getData();
    }
}