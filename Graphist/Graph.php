<?php namespace Graphist;

class Graph {
    
    public static $instance;
    
    public $client;
    
    public static function instance()
    {   
        if (!isset($instance)) {
            static::$instance = new Graph();
        }
        
        return static::$instance; 
    }
    
    public function __construct() 
    {
        $config = Config::get("database.neo4j");
        $this->client = new \Everyman\Neo4j\Client($config["server"], $config["port"]);
    }
    
    public function getClient()
    {
        return $this->client;
    }

    public static function connect($config)
    {   
        $client = new \Everyman\Neo4j\Client($config["host"], $config["port"]);

        if (!empty($config["username"]) and !empty($config["password"])) {
            $client->getTransport()->setAuth($config["username"], $config["password"]);
        }

        return $client;
    }

    public static function testConnection($config)
    {
        return static::connect($config);
    }
    
    public function getNextId($name)
    {
        $types = array("user_id", "migration_id");
	
	    if (in_array($name, $types)) {
        	$queryString = "MATCH (id:Counter {type: \"{$name}\"}) SET id.value = COALESCE(id.value,0) + 1 RETURN id";
            $query = new \Everyman\Neo4j\Cypher\Query($this->client, $queryString);
            $result = $query->getResultSet();
            
            foreach ($result as $node) {
                return $node["x"]->getProperty("value");
            }
        }
    }
    
}