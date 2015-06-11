<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */
 
namespace StreamOne\API\v3;

/**
 * A caching implementation using files on disk
 */
class FileCache implements CacheInterface
{
    /**
     * Base directory to store cache files in
     */
    private $basedir = "/tmp/s1_cache";
    
    /**
     * Expiration time for cached objects
     */
    private $expirationTime = 300;
    
    /**
     * Construct a FileCache
     * 
     * @param string $basedir Base directory to store cache files in, not ending in a /
     * @param int $expiretime Time (in seconds) before a cache item expires
     */
    public function __construct($basedir, $expiretime)
    {
        $this->basedir = $basedir . '/';
        $this->expirationTime = $expiretime;
        
        // Create cache dir, make it user-readable/writable only
        if (!file_exists($this->basedir))
        {
            mkdir($this->basedir, 0700, true);
        }
    }
    
    /**
     * Get the value of a stored key
     * 
     * @param string $key Key to get the cached value of
     * @return mixed Cached value of the key, or false if value not found or expired
     */
    public function get($key)
    {
		$serialized = $this->getFileContents($key);
		if (!$serialized)
		{
			return false;
		}
        
        // Return unserialized contents, or false on failure (unserialize() does this already)
        return unserialize($serialized);
    }

    /**
     * Get the age of a stored key
     *
     * @param string $key Key to get the age of
     * @return mixed Age of the key in seconds, or false if value not found or expired
     */
    public function age($key)
    {
		$filename = $this->filename($key);
		if (!file_exists($filename))
		{
			return false;
		}
		else
		{
			return time() - filemtime($filename);
		}
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
        $filename = $this->filename($key);
        
        // We can ignore the return value, since we do not really care whether it succeeded
        file_put_contents($filename, serialize($value));
    }
    
    /**
     * Calculate the filename to store a given key in
     * 
     * @param string $key Key to calculate the filename for
     * @return string Filename to store the key's value in
     */
    private function filename($key)
    {
        return $this->basedir . sha1($key);
    }

    /**
	 * Retrieve the contents for a file given by a key
	 *
	 * @param string $key Key to get the cached value of
	 * @return string
	 *   The contents of the the file for the given key or false if the file does not exist or is
	 *   expired
	 */
    private function getFileContents($key)
    {
		$filename = $this->filename($key);

        if (!file_exists($filename))
            return false;

        // Check if the file expired
        if ((filemtime($filename) + $this->expirationTime) < time())
        {
            // The cache file is expired: remove it
            @unlink($filename);
            return false;
        }

        $serialized = file_get_contents($filename);
        if ($serialized === false)
        {
            // The cache file is unreadable; (try to) remove it
            @unlink($filename);
            return false;
        }

        return $serialized;
	}
}

/**
 * @}
 */
