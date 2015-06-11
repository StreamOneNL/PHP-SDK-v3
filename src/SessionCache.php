<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * A session caching implementation, storing cached data in a SessionStore
 * 
 * Since SessionStoreInterface does not support storing the age of cached data, and CacheInterface
 * requires this, this class stores an array in every value with the following keys:
 * 
 * - time: time() of the moment when the value was stored
 * - value: the actual value stored
 */
class SessionCache implements CacheInterface
{
	/**
	 * Session store to store cached values in
	 * 
	 * @var SessionStoreInterface $session_store
	 */
	private $session_store;
	
	/**
	 * Construct a SessionCache
	 * 
	 * @param Session|SessionStoreInterface $session
	 *   The session or session store to store the cached values in. If a session is passed, the
	 *   session store used in that session is used for caching. If a session store is passed,
	 *   that session store is used.
	 * 
	 * @throws \InvalidArgumentException
	 *   The given $session was neither a Session nor a SessionStoreInterface
	 */
	public function __construct($session)
	{
		if ($session instanceof Session)
		{
			$this->session_store = $session->getSessionStore();
		}
		else if ($session instanceof SessionStoreInterface)
		{
			$this->session_store = $session;
		}
		else
		{
			throw new \InvalidArgumentException('Given session is instance of neither Session nor SessionStoreInterface');
		}
	}
	
	/**
	 * Retrieve the session store used to store cached values
	 * 
	 * @return SessionStoreInterface
	 *   The session store used to store cached values
	 */
	public function getSessionStore()
	{
		return $this->session_store;
	}
	
	/**
	 * Get the value of a stored key
	 * 
	 * @param string $key
	 *   Key to get the cached value of
	 * @return mixed
	 *   Cached value of the key, or false if value not found or expired
	 */
	public function get($key)
	{
		if ($this->session_store->hasCacheKey($key))
		{
			// Key found; extract value
			$data = $this->session_store->getCacheKey($key);
			return $data['value'];
		}
		
		// Key not found
		return false;
	}
	
	 /**
	 * Get the age of a stored key
	 *
	 * @param string $key
	 *   Key to get the age of
	 * @return mixed
	 *   Age of the key in seconds, or false if value not found or expired
	 */
	public function age($key)
	{
		if ($this->session_store->hasCacheKey($key))
		{
			// Key found; extract set time and calculate age
			$data = $this->session_store->getCacheKey($key);
			$age = time() - $data['time'];
			return $age;
		}
		
		// Key not found
		return false;
	}
	
	/**
	 * Store a value for the given key
	 * 
	 * @param string $key
	 *   Key to cache the value for
	 * @param mixed $value
	 *   Value to store for the given key
	 */
	public function set($key, $value)
	{
		$this->session_store->setCacheKey($key, array(
			'time' => time(),
			'value' => $value,
		));
	}
}

/**
 * @}
 */
