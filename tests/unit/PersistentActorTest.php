<?php

use StreamOne\API\v3\PersistentActor;

class PersistentActorTest extends PHPUnit_TestCase
{
	private static $session;
	
	/**
	 * Create a config, session store and session
	 */
	public static function setUpBeforeClass()
	{
		$session_store = new \StreamOne\API\v3\MemorySessionStore;
		$config = new \StreamOne\API\v3\Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'user',
				'user_id' => 'user',
				'user_psk' => 'psk',
				'use_session_for_token_cache' => false,
				'session_store' => $session_store
			)
		);
		self::$session = new \StreamOne\API\v3\Session($config);
		$session_store->setSession('id', 'key', 'user_id', 3600);
	}
	
	/**
	 * Test that the actor is persisted when not having any accounts or customers set
	 */
	public function testPersistentActorGlobal()
	{
		// Load first actor, set account to null (i.e. global)
		$persistent_actor_1 = new PersistentActor(self::$session);
		// First set it to something not-null, to test we "re-persist" after setting to null
		$persistent_actor_1->setAccount('abc');
		$persistent_actor_1->setAccount(null);
		
		// Accounts + customer should be empty
		$this->assertNull($persistent_actor_1->getCustomer());
		$this->assertEmpty($persistent_actor_1->getAccounts());
		
		// Load another actor
		$persistent_actor_2 = new PersistentActor(self::$session);
		
		// Accounts + customer should still be empty
		$this->assertNull($persistent_actor_2->getCustomer());
		$this->assertEmpty($persistent_actor_2->getAccounts());
	}
	
	/**
	 * Test that the actor is persisted when an account has been set
	 */
	public function testPersistentActorAccount()
	{
		$account = 'account123';
		
		// Load first actor, set account to null (i.e. global)
		$persistent_actor_1 = new PersistentActor(self::$session);
		$persistent_actor_1->setAccount($account);
		
		// Account should be set correctly
		$this->assertNull($persistent_actor_1->getCustomer());
		$this->assertSame($account, $persistent_actor_1->getAccount());
		
		// Load another actor
		$persistent_actor_2 = new PersistentActor(self::$session);
		
		// Account should still be set correctly
		$this->assertNull($persistent_actor_2->getCustomer());
		$this->assertSame($account, $persistent_actor_2->getAccount());
	}
	
	/**
	 * Test that the actor is persisted when multiple accounts have been set
	 */
	public function testPersistentActorAccounts()
	{
		$accounts = array('A', 'B');
		
		// Load first actor, set accounts to correct value
		$persistent_actor_1 = new PersistentActor(self::$session);
		$persistent_actor_1->setAccounts($accounts);
		
		// Accounts should be set correctly
		$this->assertNull($persistent_actor_1->getCustomer());
		$this->assertSame($accounts, $persistent_actor_1->getAccounts());
		
		// Load another actor
		$persistent_actor_2 = new PersistentActor(self::$session);
		
		// Accounts should still be set correctly
		$this->assertNull($persistent_actor_2->getCustomer());
		$this->assertSame($accounts, $persistent_actor_2->getAccounts());
	}
	
	/**
	 * Test that the actor is persisted when a customer has been set
	 */
	public function testPersistentActorCustomer()
	{
		$customer = 'C';
		
		// Load first actor, set customer to correct value
		$persistent_actor_1 = new PersistentActor(self::$session);
		$persistent_actor_1->setCustomer($customer);
		
		// Customer should be set correctly
		$this->assertNull($persistent_actor_1->getAccount());
		$this->assertSame($customer, $persistent_actor_1->getCustomer());
		
		// Load another actor
		$persistent_actor_2 = new PersistentActor(self::$session);
		
		// Customer should still be set correctly
		$this->assertNull($persistent_actor_2->getAccount());
		$this->assertSame($customer, $persistent_actor_2->getCustomer());
	}
}
