<?php

require_once('CacheInterfaceTest.php');

use StreamOne\API\v3\SessionCache;
use StreamOne\API\v3\MemorySessionStore;
use StreamOne\API\v3\Config;
use StreamOne\API\v3\Session;

/**
 * Test the SessionCache
 */
class SessionCacheTest extends CacheInterfaceTest
{
	/**
	 * Construct a cache for use in functionality tests
	 * 
	 * This always construct a cache using a fresh MemorySessionStore.
	 */
	protected function constructCache()
	{
		return new SessionCache(new MemorySessionStore);
	}
	
	/**
	 * Test constructing a SessionCache with a SessionStore
	 */
	public function testConstructSessionStore()
	{
		$store = new MemorySessionStore;
		$cache = new SessionCache($store);
		$this->assertSame($store, $cache->getSessionStore());
	}
	
	/**
	 * Test construction a SessionCache with a Session
	 */
	public function testConstructSession()
	{
		$config = new Config(array());
		$store = new MemorySessionStore;
		$session = new Session($config, $store);
		$cache = new SessionCache($session);
		$this->assertSame($store, $cache->getSessionStore());
	}
	
	/**
	 * Test constructing a SessionCache with an invalid argument
	 * 
	 * @param mixed $session
	 *   The (invalid) $session parameter for the SessionCache constructor
	 * 
	 * @dataProvider provideConstructInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructInvalid($session)
	{
		new SessionCache($session);
	}
	
	public function provideConstructInvalid()
	{
		return array(
			array('MemorySessionStore'),
			array(5),
			array(array(new MemorySessionStore)),
			array(new stdClass),
		);
	}
	
	/**
	 * Test if the SessionStore is actually used for caching
	 */
	public function testUsingSessionStore()
	{
		// Construct a cache with known session store
		$store = new MemorySessionStore;
		$cache = new SessionCache($store);
		
		$key = 'testUsingSessionStore';
		$value = 'value for testUsingSessionStore';
		
		// Setting a value in the cache must make it set in the session store
		$cache->set($key, $value);
		$this->assertTrue($store->hasCacheKey($key));
		// SessionCache wraps all data to store the cache time with it
		$data = $store->getCacheKey($key);
		$this->assertSame($value, $data['value']);
	}
}
