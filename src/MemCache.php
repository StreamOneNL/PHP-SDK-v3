<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * A caching implementation using MemCache
 */
class MemCache implements CacheInterface
{
    /**
     * Base directory to store cache files in
     */
    private $memcache;
    
    /**
     * Expiration time for cached objects
     */
    private $expirationTime = 3600;
    
    /**
     * Construct a MemCache
     * 
     * @param string $host The host where memcached is listening for connections
     * @param int $port The port where memcached is listening for connections
     * @param int $expiretime Time (in seconds) before a cache item expires
     */
    public function __construct($host, $port, $expiretime)
    {
        $this->expirationTime = $expiretime;
        
        // Check if memcache is available and try to connect
        $this->memcache = new Memcache;
        @$this->memcache->connect($host, $port);
    }
    
    /**
     * Get the value of a stored key
     * 
     * @param string $key Key to get the cached value of
     * @return mixed Cached value of the key, or false if value not found or expired
     */
    public function get($key)
    {
        $value = $this->memcache->get($key);
        if ($value === false)
		{
			return false;
		}
		return $value['data'];
    }

    /**
     * Get the age of a stored key
     *
     * @param string $key Key to get the age of
     * @return mixed Age of the key in seconds, or false if value not found or expired
     */
    public function age($key)
    {
        $value = $this->memcache->get($key);
        if ($value === false)
		{
			return false;
		}
		return time() - $value['age'];
    }
    
    /**
     * Store a value for the given key
     * 
     * Stored results are available before they expire, unless writing fails.
     * 
     * @param string $key Key to cache the value for
     * @param mixed $value Value to store for the given key
     */
    public function set($key, $value)
    {
		$value = array(
			'data' => $value,
			'age' => time()
		);
        $this->memcache->set($key, $value, 0, $this->expirationTime);
    }
}

/**
 * @}
 */
