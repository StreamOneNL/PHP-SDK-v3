<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * Interface for session storage
 * 
 * As a part of storing session information, session stores can also be asked to cache certain
 * information for the duration of the session. For example, the tokens that the session user has
 * can be stored in the session. This is subject to the following conditions:
 * 
 * - Data cached in a session will always be cached for exactly the lifetime of the session.
 * 
 * - It is only allowed to store serializable data in the cache.
 */
interface SessionStoreInterface
{
	/**
	 * Determines if there is an active session
	 *
	 * @return bool True if and only if there is an active session
	 */
	public function hasSession();

	/**
	 * Clears the current active session
	 */
	public function clearSession();

	/**
	 * Save a session to this store
	 *
	 * @param string $id
	 *   The ID for this session
	 * @param string $key
	 *   The key for this session
	 * @param string $user_id
	 *   The user ID for this session
	 * @param int $timeout
	 *   The number of seconds before this session becomes invalid when not doing any requests
	 */
	public function setSession($id, $key, $user_id, $timeout);
	
	/**
	 * Update the timeout of a session
	 *
	 * @param int $timeout
	 *   The new timeout for the active session, in seconds from now
	 */
	public function setTimeout($timeout);
	
	/**
	 * Retrieve the current session ID
	 * 
	 * The behavior of this function is undefined if there is no active session.
	 * 
	 * @return string
	 *   The current session ID
	 */
	public function getId();
	
	/**
	 * Retrieve the current session key
	 * 
	 * The behavior of this function is undefined if there is no active session.
	 * 
	 * @return string
	 *   The current session key
	 */
	public function getKey();
	
	/**
	 * Retrieve the ID of the user logged in with the current session
	 * 
	 * The behavior of this function is undefined if there is no active session.
	 * 
	 * @return string
	 *   Retrieve the ID of the user logged in with the current session
	 */
	public function getUserId();
	
	/**
	 * Retrieve the current session timeout
	 * 
	 * The behavior of this function is undefined if there is no active session.
	 * 
	 * @return int
	 *   The number of seconds before this session expires; negative if the session has expired
	 */
	public function getTimeout();
	
	/**
	 * Check if a certain key is stored in the cache
	 * 
	 * @param string $key
	 *   Cache key to check for existence
	 * @return bool
	 *   True if and only if the given key is set in the cache
	 */
	public function hasCacheKey($key);
	
	/**
	 * Retrieve a stored cache key
	 * 
	 * The behavior of this method is undefined if a non-existing cache key is retrieved; always
	 * check for existance of the key using hasCacheKey($key).
	 * 
	 * @param string $key
	 *   Cache key to get the cached value of
	 * @return mixed
	 *   The cached value
	 */
	public function getCacheKey($key);
	
	/**
	 * Store a cache key
	 * 
	 * @param string $key
	 *   Cache key to store a value for
	 * @param mixed $value
	 *   Value to store for the given key
	 */
	public function setCacheKey($key, $value);
	
	/**
	 * Unset a cached value
	 * 
	 * The behavior of this method is undefined if a non-existing cache key is unset; always
	 * check for existance of the key using hasCacheKey($key).
	 * 
	 * @param string $key
	 *   Cache key to unset
	 */
	public function unsetCacheKey($key);
}

/**
 * @}
 */
