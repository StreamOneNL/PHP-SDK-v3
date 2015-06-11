<?php

use StreamOne\API\v3\Actor;
use StreamOne\API\v3\Config;
use StreamOne\API\v3\MemorySessionStore;
use StreamOne\API\v3\Session;
use StreamOne\API\v3\Request;

/**
 * Class that can reuse everything from a Request and overwrite methods for tests
 */
class TestActorRequest extends Request
{
	private $request;
	
	public function __construct(Request $request)
	{
		$this->request = $request;
		parent::__construct($request->command(), $request->action(), $request->getConfig());
	}
	
	/**
	 * Just pass it to the wrapped request
	 */
	public function getAccount()
	{
		return $this->request->getAccount();
	}

	/**
	 * Just pass it to the wrapped request
	 */
	public function getAccounts()
	{
		return $this->request->getAccounts();
	}

	/**
	 * Just pass it to the wrapped request
	 */
	public function getCustomer()
	{
		return $this->request->getCustomer();
	}

	/**
	 * Just pass it to the wrapped request
	 */
	public function arguments()
	{
		return $this->request->arguments();
	}

	/**
	 * Just pass it to the wrapped request
	 */
	protected function parameters()
	{
		return $this->request->parameters();
	}

	/**
	 * We need the authentication type in tests, so make it publicly available
	 * 
	 * @return string
	 *   The authentication type used
	 */
	public function getAuthenticationType()
	{
		$parameters = $this->parameters();
		return $parameters['authentication_type'];
	}

	/**
	 * We need the parameters for signing in tests, so make it publicly available
	 */
	public function parametersForSigning()
	{
		return $this->request->parametersForSigning();
	}

	/**
	 * We need the signing key in tests, so make it publicly available
	 */
	public function signingKey()
	{
		return $this->request->signingKey();
	}
}

/**
 * Class to overwrite functions that get data from the API
 */
class TestActor extends Actor
{
	/**
	 * Return a predetermined set of roles
	 */
	protected function loadRolesFromApi($actor_type)
	{
		if ($actor_type == 'user')
		{
			return array(
				array(
					// user has tokens a, b, c global
					'role' => array(
						'tokens' => array('a', 'b', 'c')
					),
				),
				array(
					// it has tokens f, g in account A1
					'role' => array(
						'tokens' => array('f', 'g')
					),
					'account' => array('id' => 'A1')
				),
				array(
					// and it has tokens g, h in account A2
					'role' => array(
						'tokens' => array('g', 'h')
					),
					'account' => array('id' => 'A2')
				)
			);
		}
		else
		{
			return array(
				array(
					// application has token z global
					'role' => array(
						'tokens' => array('z')
					),
				),
				array(
					// it has tokens y, p in customer C1
					'role' => array(
						'tokens' => array('y', 'p')
					),
					'customer' => array('id' => 'C1')
				),
				array(
					// it has tokens x, p in customer C2
					'role' => array(
						'tokens' => array('x', 'p')
					),
					'customer' => array('id' => 'C2')
				),
				array(
					// it has tokens w, v in account A1
					'role' => array(
						'tokens' => array('w', 'v')
					),
					'account' => array('id' => 'A1')
				),
				array(
					// and it has tokens v, u in account A3
					'role' => array(
						'tokens' => array('v', 'u')
					),
					'account' => array('id' => 'A3')
				)
			);
		}
	}
	
	/**
	 * Return a predetermined set of tokens
	 */
	protected function loadMyTokensFromApi()
	{
		// This should be consistent with the above list, otherwise we might get unexpected behaviour
		// That makes this function a big if-statement
		
		// Sort accounts so we can do array comparison easily
		$accounts = $this->getAccounts();
		sort($accounts);
		
		// Extra assumptions: A1 and A2 belong to C1 and A3 belongs to C2
		if ($this->getConfig()->getAuthenticationType() == Config::AUTH_USER || 
			$this->getSession() !== null)
		{
			if ($this->getCustomer() === null && $this->getAccount() === null)
			{
				return array('a', 'b', 'c');
			}
			elseif ($accounts == array('A1'))
			{
				return array('a', 'b', 'c', 'f', 'g');
			}
			elseif ($accounts == array('A2'))
			{
				return array('a', 'b', 'c', 'g', 'h');
			}
			elseif ($accounts == array('A1', 'A2'))
			{
				return array('a', 'b', 'c', 'g');
			}
			else
			{
				// Globally available tokens
				return array('a', 'b', 'c');
			}
		}
		else
		{
			if ($this->getCustomer() === null && $this->getAccount() === null)
			{
				return array('z');
			}
			elseif ($this->getCustomer() === 'C1')
			{
				return array('z', 'y', 'p');
			}
			elseif ($this->getCustomer() === 'C2')
			{
				return array('z', 'x', 'p');
			}
			elseif ($accounts == array('A1'))
			{
				return array('z', 'y', 'p', 'w', 'v');
			}
			elseif ($accounts == array('A3'))
			{
				return array('z', 'y', 'p');
			}
			elseif ($accounts == array('A3'))
			{
				return array('z', 'x', 'p', 'v', 'u');
			}
			elseif ($accounts == array('A1', 'A2'))
			{
				return array('z', 'p', 'y');
			}
			elseif ($accounts == array('A1', 'A3'))
			{
				return array('z', 'p', 'v');
			}
			elseif ($accounts == array('A2', 'A3'))
			{
				return array('z', 'p');
			}
			elseif ($accounts == array('A1', 'A2', 'A3'))
			{
				return array('z', 'p');
			}
			else
			{
				// Globally available tokens
				return array('z');
			}
		}
	}
}

/**
 * Test for the Actor class
 */
class ActorTest extends PHPUnit_TestCase
{
	private static $configs;
	private static $sessions;

	public static function setUpBeforeClass()
	{
		self::$configs['user'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'user',
				'user_id' => 'user',
				'user_psk' => 'psk',
				'use_session_for_token_cache' => false
			)
		);
		self::$configs['user_default_account'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'user',
				'user_id' => 'application',
				'user_psk' => 'apppsk',
				'default_account_id' => 'account',
				'use_session_for_token_cache' => false
			)
		);
		self::$configs['application'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'application',
				'application_id' => 'user',
				'application_psk' => 'psk',
				'use_session_for_token_cache' => false
			)
		);
		self::$configs['application_default_account'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'application',
				'application_id' => 'application',
				'application_psk' => 'apppsk',
				'default_account_id' => 'account',
				'use_session_for_token_cache' => false
			)
		);

		foreach (self::$configs as $key => $dummy)
		{
			$session_store = new MemorySessionStore();
			$session_store->setSession('session', 'key', 'user', 100);
			self::$sessions[$key] = new Session(self::$configs[$key], $session_store);
		}
	}

	/**
	 * Test the config-option of the constructor
	 */
	public function testConstructorConfig()
	{
		$actor = new Actor(self::$configs['user']);
		
		$this->assertSame(self::$configs['user'], $actor->getConfig());
	}
	
	/**
	 * Test that the token cache is set from the config if no session is provided
	 */
	public function testTokenCacheWithoutSession()
	{
		$actor = new Actor(self::$configs['user']);
		
		$this->assertSame($actor->getConfig()->getTokenCache(), $actor->getTokenCache());
	}
	
	/**
	 * Test that the token cache is set from the config if a session is provided but not used for
	 * token cache
	 */
	public function testTokenCacheWithSessionNotUsed()
	{
		/** @var Config $config */
		$config = self::$configs['application'];
		$actor = new Actor($config, self::$sessions['application']);
		
		$this->assertSame($actor->getConfig()->getTokenCache(), $actor->getTokenCache());
	}
	
	/**
	 * Test that the token cache is set from the session if a session is provided and used for
	 * token cache
	 */
	public function testTokenCacheWithSessionUsed()
	{
		/** @var Config $config */
		$config = self::$configs['application'];
		$config->setUseSessionForTokenCache(true);
		$actor = new Actor($config, self::$sessions['application']);
		
		$this->assertInstanceOf('\StreamOne\API\v3\SessionCache', $actor->getTokenCache());
		
		$config->setUseSessionForTokenCache(false);
	}
	
	/**
	 * Test the session-option of the constructor
	 *
	 * @param string|null $session
	 *   Name of the session (from self::$sessions) to use
	 *
	 * @dataProvider provideConstructorSession
	 */
	public function testConstructorSession($session)
	{
		$session_to_use = null;
		if ($session !== null)
		{
			$session_to_use = self::$sessions[$session];
		}
		$actor = new Actor(self::$configs['user'], $session_to_use);
		
		$this->assertSame($session_to_use, $actor->getSession());
	}
	
	public function provideConstructorSession()
	{
		return array(
			array(null),
			array('application'),
		);
	}
	
	/**
	 * Test that the constructor sets the default account
	 *
	 * @param string|null $config
	 *   Name of the config (from self::$config) to use
	 *
	 * @dataProvider provideConstructorDefaultAccount
	 */
	public function testConstructorDefaultAccount($config)
	{
		/** @var Config $my_config */
		$my_config = self::$configs[$config];
		$actor = new Actor($my_config);
		
		$this->assertSame($my_config->getDefaultAccountId(), $actor->getAccount());
	}
	
	public function provideConstructorDefaultAccount()
	{
		return array(
			array('user'),
			array('user_default_account'),
		);
	}
	
	/**
	 * Test that setting the account of an actor works as expected
	 *
	 * @param string|null $account
	 *   The ID of the account to set; null to clear the account
	 *
	 * @dataProvider provideSetAccount
	 */
	public function testSetAccount($account)
	{
		$actor = new Actor(self::$configs['user']);
		$actor->setAccount($account);
		
		$this->assertSame($account, $actor->getAccount());
		if ($account == null)
		{
			$this->assertEmpty($actor->getAccounts());
		}
		else
		{
			$this->assertSame(array($account), $actor->getAccounts());
		}
		$this->assertNull($actor->getCustomer());
	}
	
	public function provideSetAccount()
	{
		return array(
			array('account123'),
			array(null)
		);
	}
	
	/**
	 * Test that setting the accounts of an actor works as expected
	 *
	 * @param array $accounts
	 *   An arraw with the IDs of the accounts to set
	 *
	 * @dataProvider provideSetAccounts
	 */
	public function testSetAccounts($accounts)
	{
		$actor = new Actor(self::$configs['user']);
		$actor->setAccounts($accounts);
		
		$this->assertSame($accounts, $actor->getAccounts());
		if (empty($accounts))
		{
			$this->assertNull($actor->getAccount());
		}
		else
		{
			$this->assertSame($accounts[0], $actor->getAccount());
		}
		$this->assertNull($actor->getCustomer());
	}
	
	public function provideSetAccounts()
	{
		return array(
			array(array('account123')),
			array(array('account123', 'anotheraccount')),
			array(array())
		);
	}
	
	/**
	 * Test that setting the customer of an actor works as expected
	 *
	 * @param string|null $customer
	 *   The ID of the customer to set; null to clear the account
	 *
	 * @dataProvider provideSetCustomer
	 */
	public function testSetCustomer($customer)
	{
		$actor = new Actor(self::$configs['user']);
		$actor->setCustomer($customer);
		
		$this->assertSame($customer, $actor->getCustomer());
		$this->assertNull($actor->getAccount());
		$this->assertEmpty($actor->getAccounts());
	}
	
	public function provideSetCustomer()
	{
		return array(
			array('customer123'),
			array(null)
		);
	}
	
	/**
	 * Test if creating a new request with an account has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param string|null $account
	 *   The account to set / test
	 *
	 * @dataProvider provideNewRequestWithAccount
	 */
	public function testNewRequestWithAccount($config, $account)
	{
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use);
		
		$actor->setAccount($account);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\Request', $request);
		$request = new TestActorRequest($request);
		
		$this->assertSame($account, $request->getAccount());
		if ($account === null)
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('account', $request->parametersForSigning());
			$this->assertEmpty($request->getAccounts());
		}
		else
		{
			$this->assertSame(array($account), $request->getAccounts());
		}
		
		$this->assertSame($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$this->assertSame($config_to_use->getAuthenticationActorKey(), $request->signingKey());
		
		// We have set an account, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithAccount()
	{
		return array(
			array('user', 'account123'),
			array('user_default_account', 'account123'),
			array('application', 'account123'),
			array('user', null),
			array('application', null),
			array('application_default_account', null),
		);
	}
	
	/**
	 * Test if creating a new request with the default account has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 *
	 * @dataProvider provideNewRequestWithDefaultAccount
	 */
	public function testNewRequestWithDefaultAccount($config)
	{
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\Request', $request);
		$request = new TestActorRequest($request);
		
		$this->assertSame($config_to_use->getDefaultAccountId(), $request->getAccount());
		if ($config_to_use->getDefaultAccountId() === null)
		{
			$this->assertEmpty($request->getAccounts());
		}
		else
		{
			$this->assertEquals(array($config_to_use->getDefaultAccountId()), $request->getAccounts());
		}
		$this->assertSame($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$this->assertSame($config_to_use->getAuthenticationActorKey(), $request->signingKey());
		
		// We have set an account, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithDefaultAccount()
	{
		return array(
			array('user'),
			array('user_default_account'),
			array('application'),
			array('application_default_account'),
		);
	}
	
	/**
	 * Test if creating a new request with an account in a session has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param string|null $account
	 *   The account to set / test
	 *
	 * @dataProvider provideNewRequestWithAccountInSession
	 */
	public function testNewRequestWithAccountInSession($config, $account)
	{
		/** @var Session $session */
		$session = self::$sessions[$config];
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use, $session);
		
		$actor->setAccount($account);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\SessionRequest', $request);
		$request = new TestActorRequest($request);
		
		$this->assertSame($account, $request->getAccount());
		if ($account === null)
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('account', $request->parametersForSigning());
			$this->assertEmpty($request->getAccounts());
		}
		else
		{
			$this->assertSame(array($account), $request->getAccounts());
		}
		
		$this->assertSame($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$session_id = $session->getSessionStore()->getId();
		$this->assertArrayKeySameValue('session', $session_id, $request->parametersForSigning());
		$session_key = $session->getSessionStore()->getKey();
		$this->assertSame($config_to_use->getAuthenticationActorKey() . $session_key,
		                    $request->signingKey());
		
		// We have set an account, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithAccountInSession()
	{
		return array(
			array('application', 'account123'),
			array('application_default_account', 'account123'),
			array('application', null),
			array('application_default_account', null),
		);
	}
	
	/**
	 * Test if creating a new request with a default account in a session has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 *
	 * @dataProvider provideNewRequestWithDefaultAccountInSession
	 */
	public function testNewRequestWithDefaultAccountInSession($config)
	{
		/** @var Session $session */
		$session = self::$sessions[$config];
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use, $session);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\SessionRequest', $request);
		$request = new TestActorRequest($request);
		
		if ($config_to_use->getDefaultAccountId() === null)
		{
			$this->assertEmpty($request->getAccounts());
		}
		else
		{
			$this->assertEquals(array($config_to_use->getDefaultAccountId()), $request->getAccounts());
		}
		$this->assertSame($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$session_id = $session->getSessionStore()->getId();
		$this->assertArrayKeySameValue('session', $session_id, $request->parametersForSigning());
		$session_key = $session->getSessionStore()->getKey();
		$this->assertSame($config_to_use->getAuthenticationActorKey() . $session_key,
		                    $request->signingKey());
		
		// We have set an account, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithDefaultAccountInSession()
	{
		return array(
			array('application'),
			array('application_default_account'),
		);
	}
	
	/**
	 * Test if creating a new request with accounts has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param array $accounts
	 *   The accounts to set / test
	 *
	 * @dataProvider provideNewRequestWithAccounts
	 */
	public function testNewRequestWithAccounts($config, $accounts)
	{
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use);
		
		$actor->setAccounts($accounts);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\Request', $request);
		$request = new TestActorRequest($request);
		
		$this->assertEquals($accounts, $request->getAccounts());
		if (empty($accounts))
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('account', $request->parametersForSigning());
			$this->assertNull($request->getAccount());
		}
		else
		{
			$this->assertSame($accounts[0], $request->getAccount());
		}
		
		$this->assertEquals($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$this->assertEquals($config_to_use->getAuthenticationActorKey(), $request->signingKey());
		
		// We have set accounts, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithAccounts()
	{
		return array(
			array('user', array('account123')),
			array('user_default_account', array('account123')),
			array('application', array('account123')),
			array('user', array()),
			array('application', array()),
			array('application_default_account', array()),
			array('user', array('account123', 'anotheraccount')),
		);
	}
	
	/**
	 * Test if creating a new request with accounts in a session has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param array $accounts
	 *   The accounts to set / test
	 *
	 * @dataProvider provideNewRequestWithAccountsInSession
	 */
	public function testNewRequestWithAccountsInSession($config, $accounts)
	{
		/** @var Session $session */
		$session = self::$sessions[$config];
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use, $session);
		
		$actor->setAccounts($accounts);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\SessionRequest', $request);
		$request = new TestActorRequest($request);
		
		$this->assertEquals($accounts, $request->getAccounts());
		if (empty($accounts))
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('account', $request->parametersForSigning());
			$this->assertNull($request->getAccount());
		}
		else
		{
			$this->assertSame($accounts[0], $request->getAccount());
		}
		
		$this->assertEquals($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$session_id = $session->getSessionStore()->getId();
		$this->assertArrayKeySameValue('session', $session_id, $request->parametersForSigning());
		$session_key = $session->getSessionStore()->getKey();
		$this->assertEquals($config_to_use->getAuthenticationActorKey() . $session_key,
		                    $request->signingKey());
		
		// We have set accounts, so there should not be a customer now
		$this->assertNull($request->getCustomer());
		$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithAccountsInSession()
	{
		return array(
			array('application', array('account123')),
			array('application', array()),
			array('application', array('account1', 'anotheraccount')),
		);
	}
	
	/**
	 * Test if creating a new request with a customer has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param string|null $customer
	 *   The customer to set / test
	 *
	 * @dataProvider provideNewRequestWithCustomer
	 */
	public function testNewRequestWithCustomer($config, $customer)
	{
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use);
		
		$actor->setCustomer($customer);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\Request', $request);
		$request = new TestActorRequest($request);
		
		$this->assertEquals($customer, $request->getCustomer());
		if ($customer === null)
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
		}
		
		$this->assertEquals($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$this->assertEquals($config_to_use->getAuthenticationActorKey(), $request->signingKey());
		
		// We have set a customer, so there should not be an account now
		$this->assertNull($request->getAccount());
		$this->assertEmpty($request->getAccounts());
		$this->assertArrayNotHasKey('account', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithCustomer()
	{
		return array(
			array('user', 'customer1'),
			array('user_default_account', 'customer1'),
			array('user', null),
			array('user_default_account', null),
		);
	}
	
	/**
	 * Test if creating a new request with a customer in a session has the intended behaaviour
	 *
	 * @param string $config
	 *   The configuration to use
	 * @param string|null $customer
	 *   The customer to set / test
	 *
	 * @dataProvider provideNewRequestWithCustomerInSession
	 */
	public function testNewRequestWithCustomerInSession($config, $customer)
	{
		/** @var Session $session */
		$session = self::$sessions[$config];
		/** @var Config $config_to_use */
		$config_to_use = self::$configs[$config];
		
		$actor = new Actor($config_to_use, $session);
		
		$actor->setCustomer($customer);
		
		$request = $actor->newRequest('command', 'action');
		
		$this->assertInstanceOf('\StreamOne\API\v3\SessionRequest', $request);
		$request = new TestActorRequest($request);
		
		$this->assertEquals($customer, $request->getCustomer());
		if ($customer === null)
		{
			// If the account to set is null, it should not be used as a parameter for signing
			$this->assertArrayNotHasKey('customer', $request->parametersForSigning());
		}
		
		$this->assertEquals($config_to_use->getAuthenticationType(),
		                    $request->getAuthenticationType());
		$this->assertArrayKeySameValue($config_to_use->getAuthenticationType(),
		                               $config_to_use->getAuthenticationActorId(),
		                               $request->parametersForSigning());
		
		$session_id = $session->getSessionStore()->getId();
		$this->assertArrayKeySameValue('session', $session_id, $request->parametersForSigning());
		$session_key = $session->getSessionStore()->getKey();
		$this->assertEquals($config_to_use->getAuthenticationActorKey() . $session_key,
		                    $request->signingKey());
		
		// We have set a customer, so there should not be an account now
		$this->assertNull($request->getAccount());
		$this->assertEmpty($request->getAccounts());
		$this->assertArrayNotHasKey('account', $request->parametersForSigning());
	}
	
	public function provideNewRequestWithCustomerInSession()
	{
		return array(
			array('application', 'customer1'),
			array('application', null),
		);
	}

	/**
	 * Test that sessions do not work when doing them as a user
	 *
	 * @param string $name
	 *   The configuration and session to use
	 *
	 * @dataProvider provideInvalidSession
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidSession($name)
	{
		$config = self::$configs[$name];
		$session = self::$sessions[$name];
		$actor = new Actor($config, $session);
		$actor->newRequest('command', 'action');
	}

	public function provideInvalidSession()
	{
		return array(
			array('user'),
			array('user_default_account'),
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors without an account
	 * or customer
	 * 
	 * @param string $config
	 *   The config to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 * 
	 * @dataProvider provideHasTokenGlobal
	 */
	public function testHasTokenGlobal($config, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$actor = new TestActor($config_to_use);
		// We set the account to null to remove any default account if set
		$actor->setAccount(null);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenGlobal()
	{
		return array(
			array('user', 'a', true),
			array('user', 'b', true),
			array('user_default_account', 'b', true),
			array('user', 'd', false),
			array('user_default_account', 'e', false),
			array('user', 's', false),
			array('user', 'z', false),
			array('application', 'z', true),
			array('application', 'y', false),
			array('application', 't', false),
			array('application', 'a', false),
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors without an account
	 * or customer in a session
	 *
	 * @param string $config
	 *   The config and session to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenGlobalInSession
	 */
	public function testHasTokenGlobalInSession($config, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$session = self::$sessions[$config];
		$actor = new TestActor($config_to_use, $session);
		// We set the account to null to remove any default account if set
		$actor->setAccount(null);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenGlobalInSession()
	{
		// The same tests as for the user* tests from the previous one, but then with application*,
		// as these should now be for the user 
		return array(
			array('application', 'a', true),
			array('application', 'b', true),
			array('application_default_account', 'b', true),
			array('application', 'd', false),
			array('application_default_account', 'e', false),
			array('application', 's', false),
			array('application', 'z', false),
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with an account
	 *
	 * @param string $config
	 *   The config to use
	 * @param string $account
	 *   The account to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenAccount
	 */
	public function testHasTokenAccount($config, $account, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$actor = new TestActor($config_to_use);
		// We set the account to null to remove any default account if set
		$actor->setAccount($account);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenAccount()
	{
		return array(
			array('user', 'A1', 'a', true), // globally, should be OK
			array('user', 'A1', 'f', true), // directly from A1
			array('user', 'A1', 'g', true), // directly from A1
			array('user', 'A1', 'h', false), // h is on A2
			array('user', 'A2', 'h', true), // directly from A2
			array('user', 'A1', 't', false), // unknown token
			array('user', 'A3', 'a', true), // globally
			array('user', 'A4', 'q', false), // unknown token
			// Default account does not matter here anymore, already tested default account stuff
		    array('application', 'A1', 'a', false), // a is for users, not apps
			array('application', 'A1', 'z', true), // globally
			array('application', 'A1', 'w', true), // from A1
			array('application', 'A1', 'p', true), // from C1
			array('application', 'A1', 'y', true), // from C1
			array('application', 'A1', 'x', false), // from C2, so not A1
			array('application', 'A5', 'z', true), // globally
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with an account in a
	 * session
	 *
	 * @param string $config
	 *   The config and session to use
	 * @param string $account
	 *   The account to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenAccountInSession
	 */
	public function testHasTokenAccountInSession($config, $account, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$session = self::$sessions[$config];
		$actor = new TestActor($config_to_use, $session);
		// We set the account to null to remove any default account if set
		$actor->setAccount($account);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenAccountInSession()
	{
		return array(
			// The same tests as for the user* tests from the previous one, but then with application*,
			// as these should now be for the user 
			array('application', 'A1', 'a', true), // globally, should be OK
			array('application', 'A1', 'f', true), // directly from A1
			array('application', 'A1', 'g', true), // directly from A1
			array('application', 'A1', 'h', false), // h is on A2
			array('application', 'A2', 'h', true), // directly from A2
			array('application', 'A1', 't', false), // unknown token
			array('application', 'A3', 'a', true), // globally
			array('application', 'A4', 'q', false), // unknown token
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with a customer
	 *
	 * @param string $config
	 *   The config to use
	 * @param string $customer
	 *   The customer to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenCustomer
	 */
	public function testHasTokenCustomer($config, $customer, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$actor = new TestActor($config_to_use);
		// We set the account to null to remove any default account if set
		$actor->setCustomer($customer);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenCustomer()
	{
		return array(
			array('user', 'C1', 'a', true), // global
			array('user', 'C5', 'a', true), // global, even though customer has nothing itself
			array('user', 'C1', 'f', false), // in A1, not C1
			array('user', 'C1', 'q', false), // unknown token
			array('application', 'C1', 'z', true), // global
			array('application', 'C1', 'y', true), // in C1
			array('application', 'C1', 'p', true), // in C1
			array('application', 'C1', 'x', false), // in C2, not C1
			array('application', 'C1', 'w', false), // in A1, not C1
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with a customer in a
	 * session
	 *
	 * @param string $config
	 *   The config and session to use
	 * @param string $customer
	 *   The customer to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenCustomerInSession
	 */
	public function testHasTokenCustomerInSession($config, $customer, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$session = self::$sessions[$config];
		$actor = new TestActor($config_to_use, $session);
		// We set the account to null to remove any default account if set
		$actor->setCustomer($customer);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenCustomerInSession()
	{
		return array(
			// The same tests as for the user* tests from the previous one, but then with application*,
			// as these should now be for the user
			array('application', 'C1', 'a', true), // global
			array('application', 'C5', 'a', true), // global, even though customer has nothing itself
			array('application', 'C1', 'f', false), // in A1, not C1
			array('application', 'C1', 'q', false), // unknown token
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with multiple accounts
	 *
	 * @param string $config
	 *   The config to use
	 * @param array $accounts
	 *   The accounts to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenAccounts
	 */
	public function testHasTokenAccounts($config, $accounts, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$actor = new TestActor($config_to_use);
		// We set the account to null to remove any default account if set
		$actor->setAccounts($accounts);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenAccounts()
	{
		return array(
			array('user', array('A1', 'A2'), 'g', true), // g is shared between both accounts
			array('user', array('A1', 'A2'), 'f', false), // f is only in A1
			array('user', array('A1', 'A2'), 'h', false), // h is only in A2
			array('user', array('A1', 'A2'), 'a', true), // a is global
			array('user', array('A1', 'A2', 'A3'), 'g', false), // A3 does not have any tokens
			array('user', array('A1', 'A2', 'A3'), 'a', true), // a is still global
			array('user', array('A1', 'A2', 'A3'), 'q', false), // q does not exist
			array('application', array('A1', 'A2', 'A3'), 'z', true), // z is global
			array('application', array('A1', 'A2', 'A3'), 'p', true), // p is shared between both customers
			array('application', array('A1', 'A3'), 'v', true), // v is shared between both accounts
			array('application', array('A1', 'A3'), 'w', false), // w is only in A1
			array('application', array('A1', 'A2', 'A3'), 'v', false), // v is only in A1 and A3
		);
	}
	
	/**
	 * Test that checking if an actor has a token works as expected for actors with multiple accounts
	 * in a session
	 *
	 * @param string $config
	 *   The config and session to use
	 * @param array $accounts
	 *   The accounts to use
	 * @param string $token
	 *   The token to check for
	 * @param bool $has_token
	 *   Whether this actor should have that token
	 *
	 * @dataProvider provideHasTokenAccountsInSession
	 */
	public function testHasTokenAccountsInSession($config, $accounts, $token, $has_token)
	{
		$config_to_use = self::$configs[$config];
		$session = self::$sessions[$config];
		$actor = new TestActor($config_to_use, $session);
		// We set the account to null to remove any default account if set
		$actor->setAccounts($accounts);
		$this->assertSame($has_token, $actor->hasToken($token));
	}
	
	public function provideHasTokenAccountsInSession()
	{
		return array(
			array('application', array('A1', 'A2'), 'g', true), // g is shared between both accounts
			array('application', array('A1', 'A2'), 'f', false), // f is only in A1
			array('application', array('A1', 'A2'), 'h', false), // h is only in A2
			array('application', array('A1', 'A2'), 'a', true), // a is global
			array('application', array('A1', 'A2', 'A3'), 'g', false), // A3 does not have any tokens
			array('application', array('A1', 'A2', 'A3'), 'a', true), // a is still global
			array('application', array('A1', 'A2', 'A3'), 'q', false), // q does not exist
		);
	}
	
	/**
	 * Test that roles are cached
	 */
	public function testTokenCacheRoles()
	{
		/** @var Config $config */
		$config = self::$configs['user'];
		
		// Use a memory cache, so we know what happens
		$config->setTokenCache(new \StreamOne\API\v3\MemoryCache);
		
		$actor = $this
			->getMockBuilder('TestActor')
			->setMethods(array('loadRolesFromApi'))
			->setConstructorArgs(array($config))
			->enableProxyingToOriginalMethods()
			->getMock();
		
		$actor
			->expects($this->once())
			->method('loadRolesFromApi');
		
		/** @var TestActor $actor */
		// Set no account, to be sure to not call loadMyTokensFromApi
		$actor->setAccount(null);
		
		$actor->hasToken('a');
		
		// This second call should not trigger loadRolesFromApi
		$actor->hasToken('a');
		
		$config->setTokenCache(new \StreamOne\API\v3\NoopCache);
	}
	
	/**
	 * Test that tokens are cached
	 */
	public function testTokenCacheTokens()
	{
		// We need an application configuration, because they have roles with customers
		/** @var Config $config */
		$config = self::$configs['application'];
		
		// Use a memory cache, so we know what happens
		$config->setTokenCache(new \StreamOne\API\v3\MemoryCache);
		
		$actor = $this
			->getMockBuilder('TestActor')
			->setMethods(array('loadMyTokensFromApi'))
			->setConstructorArgs(array($config))
			->enableProxyingToOriginalMethods()
			->getMock();
		
		$actor
			->expects($this->once())
			->method('loadMyTokensFromApi');
		
		/** @var TestActor $actor */
		// Set an account, to be sure to call loadMyTokensFromApi
		$actor->setAccount('A1');
		
		$actor->hasToken('a');
		
		// This second call should not trigger loadMyTokensFromApi
		$actor->hasToken('a');
		
		$config->setTokenCache(new \StreamOne\API\v3\NoopCache);
	}
}
