<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * A memory caching implementation, caches everything in memory as long as this object exists
 */
class MemoryCache implements CacheInterface
{
	/**
	 * @var array $cache
	 *   Memory cache. Each element is index by the cache key and contains two values: time and value
	 */
	private $cache = array();

	/**
	 * Get the value of a stored key
	 *
	 * @param string $key Key to get the cached value of
	 * @return mixed Cached value of the key, or false if value not found or expired
	 */
	public function get($key)
	{
		if (isset($this->cache[$key]))
		{
			return $this->cache[$key]['value'];
		}

		return false;
	}

	/**
	 * Get the age of a stored key
	 *
	 * @param string $key Key to get the age of
	 * @return mixed Age of the key in seconds, or false if value not found or expired
	 */
	public function age($key)
	{
		if (isset($this->cache[$key]))
		{
			return time() - $this->cache[$key]['time'];
		}

		return false;
	}

	/**
	 * Store a value for the given key
	 *
	 * Stored values are available until this object is destructed
	 *
	 * @param string $key Key to cache the value for
	 * @param mixed $value Value to store for the given key
	 */
	public function set($key, $value)
	{
		$this->cache[$key] = array(
			'value' => $value,
			'time' => time()
		);
	}
}

/**
 * @}
 */
