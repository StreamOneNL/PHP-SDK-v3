<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * PHP session storage class
 */
class PhpSessionStore implements SessionStoreInterface
{
	/// Array key for storing the session ID in $_SESSION
	const ID_KEY = 'streamone_session_id';
	/// Array key for storing the session key in $_SESSION
	const KEY_KEY = 'streamone_session_key';
	/// Array key for storing the session timeout in $_SESSION
	const TIMEOUT_KEY = 'streamone_session_timeout';
	/// Array key for storing the session user ID in $_SESSION
	const USER_ID_KEY = 'streamone_session_user_id';
	/// Array key for storing the cached data in $_SESSION
	const CACHE_KEY = 'streamone_session_cache';

	/**
	 * Start the PHP session if not already done so
	 */
	public function __construct()
	{
		if (session_id() == "")
		{
			session_start();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasSession()
	{
		$all_set = (isset($_SESSION[self::ID_KEY]) &&
		            isset($_SESSION[self::KEY_KEY]) &&
		            isset($_SESSION[self::TIMEOUT_KEY]) &&
		            isset($_SESSION[self::USER_ID_KEY]) &&
		            isset($_SESSION[self::CACHE_KEY]) &&
		            is_string($_SESSION[self::ID_KEY]) &&
		            is_string($_SESSION[self::KEY_KEY]) &&
		            is_numeric($_SESSION[self::TIMEOUT_KEY]) &&
		            is_string($_SESSION[self::USER_ID_KEY]) &&
		            is_array($_SESSION[self::CACHE_KEY]));

		if (!$all_set)
		{
			return false;
		}

		if ($_SESSION[self::TIMEOUT_KEY] > time())
		{
			return true;
		}
		else
		{
			$this->clearSession();
		}
		
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function clearSession()
	{
		unset($_SESSION[self::ID_KEY]);
		unset($_SESSION[self::KEY_KEY]);
		unset($_SESSION[self::TIMEOUT_KEY]);
		unset($_SESSION[self::USER_ID_KEY]);
		unset($_SESSION[self::CACHE_KEY]);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function setSession($id, $key, $user_id, $timeout)
	{
		$_SESSION[self::ID_KEY] = $id;
		$_SESSION[self::KEY_KEY] = $key;
		$_SESSION[self::USER_ID_KEY] = $user_id;
		$_SESSION[self::TIMEOUT_KEY] = time() + $timeout;
		$_SESSION[self::CACHE_KEY] = array();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function setTimeout($timeout)
	{
		$_SESSION[self::TIMEOUT_KEY] = time() + $timeout;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getId()
	{
		return $_SESSION[self::ID_KEY];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getKey()
	{
		return $_SESSION[self::KEY_KEY];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getUserId()
	{
		return $_SESSION[self::USER_ID_KEY];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getTimeout()
	{
		// Return difference between timeout moment and now
		return ($_SESSION[self::TIMEOUT_KEY] - time());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function hasCacheKey($key)
	{
		return array_key_exists($key, $_SESSION[self::CACHE_KEY]);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getCacheKey($key)
	{
		return $_SESSION[self::CACHE_KEY][$key];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function setCacheKey($key, $value)
	{
		$_SESSION[self::CACHE_KEY][$key] = $value;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function unsetCacheKey($key)
	{
		unset($_SESSION[self::CACHE_KEY][$key]);
	}
}

/**
 * @}
 */
