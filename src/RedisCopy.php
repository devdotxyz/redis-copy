<?php

use Predis\Client;

/**
 * simple class to copy content of one redis server to another
 * User: David Cap
 * Date: 10/15/15
 */
class RedisCopy
{
    /**
     * @var Client
     */
    private $source;

    /**
     * @var Client
     */
    private $destination;

    /**
     * @var array keys with these prefixes will be ignored and not transferred
     */
    private $ignoredPrefixes = array();

    /**
     * @param $sourceConfig connection URI string (tcp://10.0.0.1:6379)
     * @param $destinationConfig connection URI string (tcp://10.0.0.1:6379)
     * @throws Predis\Connection\ConnectionException
     */
    public function __construct($sourceConfig, $destinationConfig){
        $this->source = new Client($sourceConfig);
        $this->destination = new Client($destinationConfig);

        $this->source->connect();
        $this->destination->connect();
    }

    /**
     * function that takes all keys from source redis, dumps them and imports to the new redis
     */
    public function copy(){
        // retrieve all keys from source redis
        $keys = $this->source->keys('*');

        $this->out("Processing %d REDIS keys ...", count($keys));

        // set initial values for counters
        $hundred = 0;
        $step = 0;

        // loop through all keys to migrate them from source to destination redis
        foreach($keys AS $key){
            // check for ignored keys and skip the key if it should be ignored
            foreach($this->ignoredPrefixes AS $ignoredPrefix){
                if(strpos($key, $ignoredPrefix) !== false){
                    $this->out('-');    // skipped key will print a dash
                    continue 2; // continue with the next key
                };
            }

            //
            if($step++ % 100 == 0){ // add a line break for each 100 transferred keys
                $this->out(PHP_EOL . $hundred++. ': ');
            }


            try {
                $ttl = max(0, (int)$this->source->ttl($key));   // find TTL of the key in the source
                $serializedValue = $this->source->dump($key);   // dump the value from the source
                $this->destination->restore($key, $ttl, $serializedValue);  // and restore it in the destination
                $this->out('o');
            } catch (Exception $e){
                $this->out('X');    // print X on error
            }
        }

        $this->out(PHP_EOL . PHP_EOL);
    }

    private function out($message, $params = array()){
        echo sprintf($message, $params);
    }

    /**
     * @return array
     */
    public function getIgnoredPrefixes()
    {
        return $this->ignoredPrefixes;
    }

    /**
     * @param array $ignoredPrefixes
     */
    public function setIgnoredPrefixes($ignoredPrefixes)
    {
        $this->ignoredPrefixes = $ignoredPrefixes;
    }



}