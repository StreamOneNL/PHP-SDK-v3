<?php

use StreamOne\API\v3\Request;
use StreamOne\API\v3\Config;

/**
 * Testable implementation of Request
 */
class TestRequest extends Request
{
	/// Response to return from sendRequest
	public $response = '{"header":{"status":0,"statusmessage":0},"body":null}';

	/// Server given to sendRequest; initially null
	public $sent_server = null;
	/// Path given to sendRequest; initially null
	public $sent_path = null;
	/// Parameters given to sendRequest; initially null
	public $sent_parameters = null;
	/// Arguments given to sendRequest; initially null
	public $sent_arguments = null;

	/**
	 * A stub for sendRequest to store the give parameters and send a fixed response
	 */
	protected function sendRequest($server, $path, $parameters, $arguments)
	{
		/// Store function arguments for later inspection
		$this->sent_server = $server;
		$this->sent_path = $path;
		$this->sent_parameters = $parameters;
		$this->sent_arguments = $arguments;

		return $this->response;
	}

	/**
	 * Make this function public, to use it in tests
	 */
	public function signingKey()
	{
		return parent::signingKey();
	}

	/**
	 * Make this function public, to use it in tests
	 */
	public function cacheable()
	{
		return parent::cacheable();
	}

	/**
	 * Make this function public, to use it in tests
	 */
	public function cacheKey()
	{
		return parent::cacheKey();
	}
}

/**
 * Test for the Request class
 */
class RequestTest extends PHPUnit_TestCase
{
	private static $configs = array();

	public static function setUpBeforeClass()
	{
		self::$configs['user'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'user',
				'user_id' => 'user',
				'user_psk' => 'psk'
			)
		);
		self::$configs['user_default_account'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'user',
				'user_id' => 'user',
				'user_psk' => 'psk',
				'default_account_id' => 'account'
			)
		);
		self::$configs['application'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'application',
				'application_id' => 'application',
				'application_psk' => 'apppsk'
			)
		);
		self::$configs['application_default_account'] = new Config(
			array(
				'api_url' => 'api',
				'authentication_type' => 'application',
				'application_id' => 'application',
				'application_psk' => 'apppsk',
				'default_account_id' => 'account'
			)
		);
	}

	/**
	 * Test if setting an account has the intended behaviour
	 *
	 * @param string $config
	 *   The key in self::$configs to use as config
	 * @param bool $set_account
	 *   True if and only if an account should be set
	 * @param string|null $account
	 *   The account to set
	 * @param string|null $expected
	 *   The account that we expect to be set; if null we expect no account
	 *
	 * @dataProvider provideSetAccount
	 */
	public function testSetAccount($config, $set_account, $account, $expected)
	{
		$request = new TestRequest('command', 'action', self::$configs[$config]);

		if ($set_account)
		{
			$request->setAccount($account);
		}

		$request->execute();

		if ($expected === null)
		{
			$this->assertArrayKeyDoesNotExist('account', $request->sent_parameters);
		}
		else
		{
			$this->assertArrayKeySameValue('account', $expected, $request->sent_parameters);
		}
		$this->assertArrayKeyDoesNotExist('customer', $request->sent_parameters);
	}

	public function provideSetAccount()
	{
		return array(
			array('user', true, 'account1', 'account1'),
			array('user_default_account', true, 'account1', 'account1'),
			array('application', false, null, null),
			array('application_default_account', false, null, 'account'),
			array('application', true, null, null),
			array('application_default_account', true, null, null)
		);
	}

	/**
	 * Test if setting a customer has the intended behaviour
	 *
	 * @param string $config
	 *   The key in self::$configs to use as config
	 * @param bool $set_customer
	 *   True if and only if a customer should be set
	 * @param string|null $customer
	 *   The customer to set
	 * @param string|null $expected
	 *   The customer that we expect to be set; if null we expect no customer
	 * @param string $expected_account
	 *   The account that we expect to be set; if null we expect no account
	 *
	 * @dataProvider provideSetCustomer
	 */
	public function testSetCustomer($config, $set_customer, $customer, $expected,
	                                $expected_account = null)
	{
		$request = new TestRequest('command', 'action', self::$configs[$config]);

		if ($set_customer)
		{
			$request->setCustomer($customer);
		}

		$request->execute();

		if ($expected === null)
		{
			$this->assertArrayKeyDoesNotExist('customer', $request->sent_parameters);
		}
		else
		{
			$this->assertArrayKeySameValue('customer', $expected, $request->sent_parameters);
		}
		if ($expected_account === null)
		{
			$this->assertArrayKeyDoesNotExist('account', $request->sent_parameters);
		}
		else
		{
			$this->assertArrayKeySameValue('account', 'account', $request->sent_parameters);
		}
	}

	public function provideSetCustomer()
	{
		return array(
			array('user', true, 'customer1', 'customer1'),
			array('user_default_account', true, 'customer1', 'customer1'),
			array('application', false, null, null),
			array('application_default_account', false, null, null, 'account'),
			array('application', true, null, null),
			array('application_default_account', true, null, null)
		);
	}

	/**
	 * Test if using a config uses the correct authentication type
	 *
	 * @dataProvider provideConfig
	 */
	public function testConfig($config, $expected_type)
	{
		/** @var Config $my_config */
		$my_config = self::$configs[$config];
		$request = new TestRequest('command', 'action', $my_config);
		$request->execute();

		$this->assertArrayKeySameValue('authentication_type', $expected_type,
		                               $request->sent_parameters);

		$this->assertArrayKeySameValue($expected_type, $my_config->getAuthenticationActorId(),
		                               $request->sent_parameters);
		$this->assertEquals($my_config->getAuthenticationActorKey(), $request->signingKey());
	}

	public function provideConfig()
	{
		return array(
			array('user', 'user'),
			array('user_default_account', 'user'),
			array('application', 'application'),
			array('application_default_account', 'application'),
		);
	}

	/**
	 * Test that the cache is used if and only if a response is cacheable
	 *
	 * @param string $response
	 *   The plain response to use
	 * @param bool $should_be_cached
	 *   Whether the response should be cached
	 *
	 * @dataProvider provideCache
	 */
	public function testCache($response, $should_be_cached)
	{
		/** @var Config $my_config */
		$my_config = self::$configs['user'];
		$cache_mock = $this
			->getMockBuilder('\StreamOne\API\v3\MemoryCache')
			->setMethods(array('set', 'get'))
			->enableProxyingToOriginalMethods()
			->getMock();

		// Used to get the cache key
		$request = new TestRequest('command', 'action', $my_config);
		$request->setArgument('test', $response);

		$cache_mock
			->expects($this->exactly(5)) // once for every request (=2) + for every test (=3)
			->method('get')
			->with($request->cacheKey());

		if ($should_be_cached)
		{
			// We know it will only be called once, because it is a memory cache
			// For FileCache it could be called zero times and for NoopCache it will be called
			// twice
			$cache_mock
				->expects($this->once())
				->method('set')
				->with(
					$request->cacheKey(),
					$response
				);
		}
		else
		{
			$cache_mock
				->expects($this->never())
				->method('set');
		}

		$my_config->setCache($cache_mock);

		$this->assertFalse($cache_mock->get($request->cacheKey()));

		$request->response = $response;
		$request->execute();

		$this->assertEquals($should_be_cached, $request->cacheable());
		if ($should_be_cached)
		{
			$this->assertEquals($response, $cache_mock->get($request->cacheKey()));
		}
		else
		{
			$this->assertFalse($cache_mock->get($request->cacheKey()));
		}

		// Rerun the same request, to test cacheable objects do not get called twice
		$request = new TestRequest('command', 'action', $my_config);
		$request->setArgument('test', $response);
		$request->response = $response;
		$request->execute();

		$this->assertEquals($should_be_cached, $request->cacheable());
		if ($should_be_cached)
		{
			$this->assertEquals($response, $cache_mock->get($request->cacheKey()));
		}
		else
		{
			$this->assertFalse($cache_mock->get($request->cacheKey()));
		}

		$my_config->setCache(new \StreamOne\API\v3\NoopCache);
	}

	public function provideCache()
	{
		return array(
			array('{"header":{"status":0,"statusmessage":"ok"},"body":null}', false),
			array('{"header":{"status":0,"cacheable":false,"statusmessage":"ok"},"body":null}', false),
			array('{"header":{"status":0,"cacheable":true,"statusmessage":"ok"},"body":null}', true),
		);
	}
}
