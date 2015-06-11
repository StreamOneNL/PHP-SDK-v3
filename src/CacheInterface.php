<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * Interface for a key-based cache 
 * 
 * Note: all cache keys starting with "s1:" are used by the SDK. If you want to use this cache,
 * please use cache keys not starting with "s1:"
 */
interface CacheInterface
{
    /**
     * Get the value of a stored key
     * 
     * @param string $key Key to get the cached value of
     * @return mixed Cached value of the key, or false if value not found or expired
     */
    public function get($key);

    /**
     * Get the age of a stored key in seconds
     *
     * @param string $key Key to get the age of
     * @return mixed Age of the key in seconds, or false if value not found or expired
     */
    public function age($key);
    
    /**
     * Store a value for the given key
     * 
     * Storing a value may not guarantee it being available, so first storing a value and then
     * immediately retrieving it may still not give a valid result. For example, the
     * NoopCache stores nothing so get(...) will never return any value.
     * 
     * @param string $key Key to cache the value for
     * @param mixed $value Value to store for the given key
     */
    public function set($key, $value);
}

/**
 * @}
 */
