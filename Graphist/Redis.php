<?php namespace Graphist;

use \Predis\Client;

/**
 * Wrapper class for connecting to Redis
 */
class Redis
{
    /**
     * Singleton instance
     * 
     * @var Graphist\Redis
     */
    public static $instance;

    /**
     * Predis client instance
     * 
     * @var Predis\Client
     */
    public $client;

    /**
     * Static method for retrieving stateful instance
     * 
     * @return Graphist\Redis
     */
    public static function instance() 
    {
        if (!isset($instance)) {
            static::$instance = new Redis();
        }

        return static::$instance;
    }

    public function __construct()
    {
        $this->client = new Client(array(
            "scheme" => "tcp",
            "host"   => "162.243.88.42",
            "port"   => 6379
        ));
    }

    public function getClient()
    {
        return $this->client;
    }
}