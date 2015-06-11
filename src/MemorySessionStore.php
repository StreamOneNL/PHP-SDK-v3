<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * In-memory session storage class
 * 
 * Values in instances of  session store are only known for the lifetime of the instance, and
 * will be discarded once the instance is destroyed.
 */
class MemorySessionStore implements SessionStoreInterface
{
	/**
	 * @var string|null $id
	 *   The current session ID; null if no active session
	 */
	private $id = null;
	
	/**
	 * @var string|null $key
	 *   The current session key; null if no active session
	 */
	private $key = null;
	
	/**
	 * @var int|null $timeout
	 *   The current session timeout as absolute timestamp; null if no active session
	 */
	private $timeout = null;
	
	/**
	 * @var string|null $user_id
	 *   The user ID of the user logged in with the current session; null if no active session
	 */
	private $user_id = null;
	
	/**
	 * @var array $cache
	 *   Data store for cached values
	 */
	private $cache = array();
	
	/**
	 * {@inheritDoc}
	 */
	public function hasSession()
	{
		// All variables need to be set to a non-null value for an active session
		if (($this->id === null) || ($this->key === null) || ($this->timeout === null) ||
		    ($this->user_id === null))
		{
			return false;
		}
		
		// The timeout must not have passed yed
		if ($this->timeout < time())
		{
			$this->clearSession();
			return false;
		}
		
		// All checks passed; there is an active session
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clearSession()
	{
		$this->id = null;
		$this->key = null;
		$this->timeout = null;
		$this->user_id = null;
		$this->cache = array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSession($id, $key, $user_id, $timeout)
	{
		$this->id = $id;
		$this->key = $key;
		$this->user_id = $user_id;
		$this->timeout = time() + $timeout;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = time() + $timeout;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getKey()
	{
		return $this->key;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getTimeout()
	{
		// Return difference between timeout moment and now
		return $this->timeout - time();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function hasCacheKey($key)
	{
		return array_key_exists($key, $this->cache);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getCacheKey($key)
	{
		return $this->cache[$key];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function setCacheKey($key, $value)
	{
		$this->cache[$key] = $value;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function unsetCacheKey($key)
	{
		unset($this->cache[$key]);
	}
}

/**
 * @}
 */
