<?php

use StreamOne\API\v3\Config;
use StreamOne\API\v3\CacheInterface;
use StreamOne\API\v3\RequestFactory;
use StreamOne\API\v3\NoopCache;
use StreamOne\API\v3\SessionStoreInterface;
use StreamOne\API\v3\MemorySessionStore;
use StreamOne\API\v3\RequestFactoryInterface;

class MyRequestFactory extends RequestFactory
{
	// Add nothing
}

class MyNoopCache extends NoopCache
{
	// Add nothing
}

class MySessionStore extends MemorySessionStore
{
	// Add nothing
}

/**
 * Tests for the Config class
 */
class ConfigTest extends PHPUnit_TestCase
{
	/**
	 * Test the request_factory-option of the constructor with a RequestFactoryInterface object
	 */
	public function testConstructorRequestFactoryObject()
	{
		$factory = new RequestFactory();
		$config = new Config(array(
			'request_factory' => $factory
		));
		$this->assertSame($factory, $config->getRequestFactory());
	}
	
	/**
	 * Test the request_factory-option of the constructor with a factory array
	 */
	public function testConstructorRequestFactoryArray()
	{
		$config = new Config(array(
			'request_factory' => array('RequestFactory')
		));
		$this->assertTrue($config->getRequestFactory() instanceof RequestFactory);
	}
	
	/**
	 * Test the request_factory-option of the constructor with invalid values
	 *
	 * @param mixed $value
	 *   The (invalid) value to use for the cache option
	 *
	 * @dataProvider provideConstructorRequestFactoryInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorRequestFactoryInvalid($value)
	{
		$config = new Config(array(
			'request_factory' => $value
		));
	}
	
	public function provideConstructorRequestFactoryInvalid()
	{
		return array(
			array('strings are invalid'),
			array(8),
			array(true),
			array(array()), // Array must contain arguments
			array(new stdClass()), // Object does not implement CacheInterface
		);
	}
	
	/**
	 * Test the request_cache-option of the constructor with a CacheInterface object
	 */
	public function testConstructorRequestCacheObject()
	{
		$cache = new NoopCache;
		$config = new Config(array(
			'request_cache' => $cache
		));
		$this->assertSame($cache, $config->getRequestCache());
	}
	
	/**
	 * Test the request_cache-option of the constructor with a factory array
	 */
	public function testConstructorRequestCacheArray()
	{
		$config = new Config(array(
			'request_cache' => array('NoopCache')
		));
		$this->assertTrue($config->getRequestCache() instanceof NoopCache);
	}
	
	/**
	 * Test the request_cache-option of the constructor with invalid values
	 * 
	 * @param mixed $value
	 *   The (invalid) value to use for the cache option
	 * 
	 * @dataProvider provideConstructorRequestCacheInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorRequestCacheInvalid($value)
	{
		$config = new Config(array(
			'request_cache' => $value
		));
	}
	
	public function provideConstructorRequestCacheInvalid()
	{
		return array(
			array('strings are invalid'),
			array(8),
			array(true),
			array(array()), // Array must contain arguments
			array(new stdClass()), // Object does not implement CacheInterface
		);
	}
	
	/**
	 * Test the token_cache-option of the constructor with a CacheInterface object
	 */
	public function testConstructorTokenCacheObject()
	{
		$cache = new NoopCache;
		$config = new Config(array(
			'token_cache' => $cache
		));
		$this->assertSame($cache, $config->getTokenCache());
	}
	
	/**
	 * Test the token_cache-option of the constructor with a factory array
	 */
	public function testConstructorTokenCacheArray()
	{
		$config = new Config(array(
			'token_cache' => array('NoopCache')
		));
		$this->assertTrue($config->getTokenCache() instanceof NoopCache);
	}
	
	/**
	 * Test the token_cache-option of the constructor with invalid values
	 *
	 * @param mixed $value
	 *   The (invalid) value to use for the cache option
	 *
	 * @dataProvider provideConstructorTokenCacheInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorTokenCacheInvalid($value)
	{
		$config = new Config(array(
			'token_cache' => $value
		));
	}
	
	public function provideConstructorTokenCacheInvalid()
	{
		return array(
			array('strings are invalid'),
			array(8),
			array(true),
			array(array()), // Array must contain arguments
			array(new stdClass()), // Object does not implement CacheInterface
		);
	}
	
	/**
	 * Test the cache-option of the constructor with a CacheInterface object
	 */
	public function testConstructorCacheObject()
	{
		$cache = new NoopCache;
		$config = new Config(array(
			 'cache' => $cache
		));
		$this->assertSame($cache, $config->getRequestCache());
		$this->assertSame($cache, $config->getTokenCache());
	}
	
	/**
	 * Test the cache-option of the constructor with a factory array
	 */
	public function testConstructorCacheArray()
	{
		$config = new Config(array(
			'cache' => array('NoopCache')
		));
		$this->assertTrue($config->getRequestCache() instanceof NoopCache);
		$this->assertTrue($config->getTokenCache() instanceof NoopCache);
		$this->assertSame($config->getRequestCache(), $config->getTokenCache());
	}
	
	/**
	 * Test the cache-option of the constructor with invalid values
	 *
	 * @param mixed $value
	 *   The (invalid) value to use for the cache option
	 *
	 * @dataProvider provideConstructorCacheInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorCacheInvalid($value)
	{
		$config = new Config(array(
			'cache' => $value
		));
	}
	
	public function provideConstructorCacheInvalid()
	{
		return array(
			array('strings are invalid'),
			array(8),
			array(true),
			array(array()), // Array must contain arguments
			array(new stdClass()), // Object does not implement CacheInterface
		);
	}
	
	/**
	 * Test the use_session_for_token_cache-option of the constructor
	 * 
	 * @param bool $value
	 *   The value to use for the use_session_for_token_cache option
	 * 
	 * @dataProvider provideConstructorUseSessionForTokenCache
	 */
	public function testConstructorUseSessionForTokenCache($value)
	{
		$config = new Config(array(
			'use_session_for_token_cache' => $value
		));
		$this->assertSame($value, $config->getUseSessionForTokenCache());
	}
	
	public function provideConstructorUseSessionForTokenCache()
	{
		return array(
			array(true),
			array(false),
		);
	}
	
	/**
	 * Test the session_store-option of the constructor with a SessionStoreInterface object
	 */
	public function testConstructorSessionStoreObject()
	{
		$session_store = new MemorySessionStore;
		$config = new Config(array(
			'session_store' => $session_store
		));
		$this->assertSame($session_store, $config->getSessionStore());
	}
	
	/**
	 * Test the session_store-option of the constructor with a factory array
	 */
	public function testConstructorSessionStoreArray()
	{
		$config = new Config(array(
			'session_store' => array('MemorySessionStore')
		));
		$this->assertTrue($config->getSessionStore() instanceof MemorySessionStore);
	}
	
	/**
	 * Test the session_store-option of the constructor with invalid values
	 * 
	 * @param mixed $value
	 *   The (invalid) value to use for the session_store option
	 * 
	 * @dataProvider provideConstructorSessionStoreInvalid
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructorSessionStoreInvalid($value)
	{
		$config = new Config(array(
			'session_store' => $value
		));
	}
	
	public function provideConstructorSessionStoreInvalid()
	{
		return array(
			array('strings are invalid'),
			array(8),
			array(true),
			array(array()), // Array must contain arguments
			array(new stdClass()), // Object does not implement SessionStoreInterface
		);
	}
	
	/**
	 * Test the constructor not starting a php session if not asking for it
	 */
	public function testConstructorDoNotStartSession()
	{
		session_destroy();
		
		$this->assertSame(PHP_SESSION_NONE, session_status());
		
		$config = new Config(array(
			'session_store' => array('MemorySessionStore')
		));
		
		$this->assertSame(PHP_SESSION_NONE, session_status());
		
		// Restart session while ignoring errors so the rest of the tests succeed; otherwise,
		// they will generate a 'headers already sent' error
		@session_start();
	}
	
	/**
	 * Test constructRequestFactory() successfully constructing a request factory
	 *
	 * @param array $args
	 *   Function arguments for constructRequestFactory
	 * @param string $class_name
	 *   Fully qualified name of the class constructed on success
	 *
	 * @dataProvider provideConstructRequestFactory
	 */
	public function testConstructRequestFactory(array $args, $class_name)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should not throw
		$request_factory = call_user_func_array(array($config, 'constructRequestFactory'), $args);
		
		// Constructed session store must be an instance of RequestFactoryInterface and of
		// the correct class
		$this->assertTrue($request_factory instanceof RequestFactoryInterface);
		$this->assertTrue($request_factory instanceof $class_name);
	}
	
	public function provideConstructRequestFactory()
	{
		return array(
			array(array('RequestFactory'), "StreamOne\\API\\v3\\RequestFactory"),
			array(array('MyRequestFactory'), "MyRequestFactory"),
		);
	}
	
	/**
	 * Test constructRequestFactory() failing with InvalidArgumentException
	 *
	 * @param array $args
	 *   Function arguments for constructRequestFactory
	 *
	 * @dataProvider provideConstructRequestFactoryInvalidArgument
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructRequestFactoryInvalidArgument(array $args)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should throw InvalidArgumentException
		$request_factory = call_user_func_array(array($config, 'constructRequestFactory'), $args);
	}
	
	public function provideConstructRequestFactoryInvalidArgument()
	{
		return array(
			array(array('NonExistingRequestFactory')),
			array(array('stdClass')), // Does not implement RequestFactoryInterface
		);
	}
	
	/**
	 * Test constructCache() successfully constructing a cache
	 * 
	 * @param array $args
	 *   Function arguments for constructCache
	 * @param string $class_name
	 *   Fully qualified name of the class constructed on success
	 * 
	 * @dataProvider provideConstructCache
	 */
	public function testConstructCache(array $args, $class_name)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should not throw
		$cache = call_user_func_array(array($config, 'constructCache'), $args);
		
		// Constructed cache must be an instance of CacheInterface and of the correct class
		$this->assertTrue($cache instanceof CacheInterface);
		$this->assertTrue($cache instanceof $class_name);
	}
	
	public function provideConstructCache()
	{
		return array(
			array(array('NoopCache'), "StreamOne\\API\\v3\\NoopCache"),
			array(array('MyNoopCache'), "MyNoopCache"),
			array(array('FileCache', '/tmp/s1-cache', 300), "StreamOne\\API\\v3\\FileCache"),
		);
	}
	
	/**
	 * Test constructCache() failing with InvalidArgumentException
	 * 
	 * @param array $args
	 *   Function arguments for constructCache
	 * 
	 * @dataProvider provideConstructCacheInvalidArgument
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructCacheInvalidArgument(array $args)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should throw InvalidArgumentException
		$cache = call_user_func_array(array($config, 'constructCache'), $args);
	}
	
	public function provideConstructCacheInvalidArgument()
	{
		return array(
			array(array('NonExistingCache')),
			array(array('stdClass')), // Does not implement CacheInterface
		);
	}
	
	/**
	 * Test constructSessionStore() successfully constructing a session store
	 * 
	 * @param array $args
	 *   Function arguments for constructSessionStore
	 * @param string $class_name
	 *   Fully qualified name of the class constructed on success
	 * 
	 * @dataProvider provideConstructSessionStore
	 */
	public function testConstructSessionStore(array $args, $class_name)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should not throw
		$session_store = call_user_func_array(array($config, 'constructSessionStore'), $args);
		
		// Constructed session store must be an instance of SessionStoreInterface and of
		// the correct class
		$this->assertTrue($session_store instanceof SessionStoreInterface);
		$this->assertTrue($session_store instanceof $class_name);
	}
	
	public function provideConstructSessionStore()
	{
		return array(
			array(array('MemorySessionStore'), "StreamOne\\API\\v3\\MemorySessionStore"),
			array(array('MySessionStore'), "MySessionStore"),
		);
	}
	
	/**
	 * Test constructSessionStore() failing with InvalidArgumentException
	 * 
	 * @param array $args
	 *   Function arguments for constructSessionStore
	 * 
	 * @dataProvider provideConstructSessionStoreInvalidArgument
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructSessionStoreInvalidArgument(array $args)
	{
		// Construct a clean Config to work with
		$config = new Config(array());
		
		// Attempt construction; should throw InvalidArgumentException
		$session_store = call_user_func_array(array($config, 'constructSessionStore'), $args);
	}
	
	public function provideConstructSessionStoreInvalidArgument()
	{
		return array(
			array(array('NonExistingSessionStore')),
			array(array('stdClass')), // Does not implement SessionStoreInterface
		);
	}
}
