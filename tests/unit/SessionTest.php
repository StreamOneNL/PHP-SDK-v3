<?php

use StreamOne\API\v3\Session;
use StreamOne\API\v3\Config;
use StreamOne\API\v3\MemorySessionStore;

/**
 * Unit tests for the Session class
 */
class SessionTest extends PHPUnit_TestCase
{
	/**
	 * Test that providing no session store to the constructor results in the session store of the
	 * config to be used
	 */
	public function testConstructorNoSessionStore()
	{
		$config = new Config(
			array(
				'session_store' => new MemorySessionStore
			)
		);
		$session = new Session($config);
		
		$this->assertSame($config->getSessionStore(), $session->getSessionStore());
	}
	
	/**
	 * Test that providing a session store to the constructor results in that one being used
	 */
	public function testConstructorWithSessionStore()
	{
		$session_store = new MemorySessionStore;
		$config = new Config(
			array(
				'session_store' => new MemorySessionStore
			)
		);
		$session = new Session($config, $session_store);
		
		$this->assertNotSame($config->getSessionStore(), $session->getSessionStore());
		$this->assertSame($session_store, $session->getSessionStore());
	}
	
	/**
	 * Test that retrieving the configuration from the session works
	 */
	public function testGetConfig()
	{
		$config = new Config(
			array(
				'session_store' => new MemorySessionStore
			)
		);
		$session = new Session($config);
		
		$this->assertSame($config, $session->getConfig());
	}
	
	/**
	 * Test that a session is inactive if setSession() has not been called on the session store
	 * 
	 * @expectedException LogicException
	 */
	public function testInactive()
	{
		$config = new Config(
			array(
				'authentication_type' => 'application',
				'application_id' => 'id',
				'application_psk' => 'psk',
				'session_store' => new MemorySessionStore
			)
		);
		$session = new Session($config);
		
		$this->assertFalse($session->isActive());
		
		// This should not be possible
		$session->newRequest('command', 'action');
	}
	
	/**
	 * Test that a session is active if setSession() has been called on the session store and that
	 * a request can be created
	 */
	public function testActive()
	{
		$config = new Config(
			array(
				'authentication_type' => 'application',
				'application_id' => 'id',
				'application_psk' => 'psk',
				'session_store' => new MemorySessionStore
			)
		);
		$session = new Session($config);
		$config->getSessionStore()->setSession('id', 'key', 'user_id', 1234);
		
		$this->assertTrue($session->isActive());
		$request = $session->newRequest('command', 'action');
		$this->assertInstanceOf('\StreamOne\API\v3\SessionRequest', $request);
	}
}
