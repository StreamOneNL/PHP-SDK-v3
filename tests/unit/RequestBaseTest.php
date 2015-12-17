<?php

use StreamOne\API\v3\RequestBase;

/**
 * Testable implementation of RequestBase
 */
class TestRequestBase extends RequestBase
{
	/// API URL to use
	public $api_url = "http://api.test";
	/// Signing key to use
	public $signing_key = "PSK";
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
	
	
	protected function apiUrl()
	{
		return $this->api_url;
	}
	
	protected function signingKey()
	{
		return $this->signing_key;
	}
	
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
	 * Make getApiProtocolHost() public for testing
	 */
	public function getApiProtocolHost()
	{
		return parent::getApiProtocolHost();
	}
}

/**
 * Test for the abstract RequestBase class
 */
class RequestBaseTest extends PHPUnit_TestCase
{
	/**
	 * Test whether the default parameters are set correctly by default
	 */
	public function testConstructParameters()
	{
		$request = new TestRequestBase('command', 'action');
		$request->execute();
		
		// Test API version (should be 3)
		$this->assertArrayKeySameValue('api', 3, $request->sent_parameters);
		
		// Test output format (should be json)
		$this->assertArrayKeySameValue('format', 'json', $request->sent_parameters);
	}
	
	
	/**
	 * Test if command and action are correctly set in the request path
	 * 
	 * @param string $command
	 *   API command to check
	 * @param string $action
	 *   API action to check
	 * 
	 * @dataProvider provideCommandAction
	 */
	public function testCommandAction($command, $action)
	{
		$path = "/api/" . $command . "/" . $action;
		
		$request = new TestRequestBase($command, $action);
		$request->execute();
		
		$this->assertSame($path, $request->sent_path);
	}
	
	public function provideCommandAction()
	{
		return array(
			array('command', 'action'),
			array('item', 'view'),
		);
	}
	
	/**
	 * Test if setting an account sets the correct parameters
	 * 
	 * @param string $account_id
	 *   Account ID to test
	 * 
	 * @dataProvider provideSetAccount
	 */
	public function testSetAccount($account_id)
	{
		$request = new TestRequestBase('command', 'action');
		// Test that setAccount() returns the request
		$this->assertSame($request, $request->setAccount($account_id));
		$request->execute();
		
		$this->assertArrayKeySameValue('account', $account_id, $request->sent_parameters);
	}
	
	public function provideSetAccount()
	{
		return array(
			array('ACCOUNT'),
			array('a'),
			array('kYxEV4oaRQg2'),
			array('A4pMV-sKDVEy'),
			array('A4pMV_sKDVEy'),
			array('_A4pMVsKDVEy'),
			array('-A4pMVsKDVEy'),
			array('A4pMVsKDVEy_'),
			array('A4pMVsKDVEy-'),
		);
	}
	
	/**
	 * Test if setting multiple accounts works
	 * 
	 * @param array $account_ids
	 *   Array of strings of account IDs to test
	 * 
	 * @dataProvider provideSetAccounts
	 */
	
	public function testSetAccounts(array $account_ids)
	{
		$request = new TestRequestBase('command', 'action');
		// Test that setAccounts() returns the request
		$this->assertSame($request, $request->setAccounts($account_ids));
		$request->execute();
		
		$account_val = implode(',', $account_ids);
		
		$this->assertArrayKeySameValue('account', $account_val, $request->sent_parameters);
	}
	
	public function provideSetAccounts()
	{
		return array(
			array(array('ACCOUNT', 'a')),
			array(array('kYxEV4oaRQg2')),
			array(array('A4pMV-sKDVEy', 'A4pMV_sKDVEy')),
			array(array('-A4pMVsKDVEy', '_A4pMVsKDVEy')),
			array(array('A4pMVsKDVEy-', 'A4pMVsKDVEy_')),
		);
	}
	
	/**
	 * Test if setting a customer sets the correct parameters
	 * 
	 * @param string $customer_id
	 *   Customer ID to test
	 * 
	 * @dataProvider provideSetCustomer
	 */
	public function testSetCustomer($customer_id)
	{
		$request = new TestRequestBase('command', 'action');
		// Test that setCustomer() returns the request
		$this->assertSame($request, $request->setCustomer($customer_id));
		$request->execute();
		
		$this->assertArrayKeySameValue('customer', $customer_id, $request->sent_parameters);
	}
	
	public function provideSetCustomer()
	{
		return array(
			array('CUSTOMER'),
			array('c'),
			array('kYxEV4oaRQg2'),
			array('A4pMV-sKDVEy'),
			array('A4pMV_sKDVEy'),
			array('_A4pMVsKDVEy'),
			array('-A4pMVsKDVEy'),
			array('A4pMVsKDVEy_'),
			array('A4pMVsKDVEy-'),
		);
	}
	
	/**
	 * Test if setting a timezone sets the correct parameters
	 * 
	 * @param DateTimeZone $time_zone
	 *   Time zone to set
	 * @param string $time_zone_name
	 *   Name of the time zone as it should be sent in the 'timezone' parameter
	 * 
	 * @dataProvider provideSetTimeZone
	 */
	public function testSetTimeZone(DateTimeZone $time_zone, $time_zone_name)
	{
		$request = new TestRequestBase('command', 'action');
		// Test that setTimeZone() returns the request
		$this->assertSame($request, $request->setTimeZone($time_zone));
		$request->execute();
		
		$this->assertArrayKeySameValue('timezone', $time_zone_name, $request->sent_parameters);
	}
	
	public function provideSetTimeZone()
	{
		return array(
			array(new DateTimeZone('Europe/Amsterdam'), 'Europe/Amsterdam'),
			array(new DateTimeZone('UTC'), 'UTC'),
		);
	}
	
	/**
	 * Test if setting arguments works correctly
	 * 
	 * @param array $arguments
	 *   A (string)key => (string)value array of arguments to set
	 * 
	 * @dataProvider provideSetArgument
	 */
	public function testSetArgument(array $arguments)
	{
		$request = new TestRequestBase('command', 'action');
		foreach ($arguments as $key => $value)
		{
			// Test that setArgument() returns the request
			$this->assertSame($request, $request->setArgument($key, $value));
		}
		
		// Flatten arrays
		$arguments_to_compare = array_map(function($element)
		{
			if (is_array($element))
			{
				return implode(',', $element);
			}
			
			return $element;
		}, $arguments);
		
		// Check arguments before executing
		$this->assertArraysSameUnordered($arguments_to_compare, $request->arguments());
		
		$request->execute();
		
		// Check arguments after executing
		$this->assertArraysSameUnordered($arguments_to_compare, $request->arguments());
		
		// Check sent arguments
		$this->assertArraysSameUnordered($arguments_to_compare, $request->sent_arguments);
	}
	
	public function provideSetArgument()
	{
		return array(
			array(array()),
			array(array(
				'id' => 'WLhMc84KJcAS'
			)),
			array(array(
				'item' => '2qgEU-6Kbdoy',
				'account' => 'SLoMc-OaZNsy'
			)),
			array(array(
					'test' => 'abcde,fghijk,uvwxyz', 
					'account' => 'SLoMc-OaZNsy'
			)),
			array(array(
					'test' => ['abcde', 'fghijk', 'uvwxyz'],
					'account' => 'SLoMc-OaZNsy'
			)),
		);
	}
	
	/**
	 * Test if the protocol can be set correctly
	 * 
	 * @param string $api_url
	 *   Original API url to set
	 * @param string $set_proto
	 *   Protocol set using setProtocol()
	 * @param string $get_proto
	 *   Protocol retrieved via protocol()
	 * @param string $sent_api_url
	 *   API URL given to sendRequest()
	 * 
	 * @dataProvider provideSetProtocol
	 */
	public function testSetProtocol($api_url, $set_proto, $get_proto, $sent_api_url)
	{
		$request = new TestRequestBase('command', 'action');
		$request->api_url = $api_url;
		// Test that setProtocol() returns the request
		$this->assertSame($request, $request->setProtocol($set_proto));
		
		$this->assertSame($get_proto, $request->protocol());
		
		// Check if correct API URL is sent
		$request->execute();
		$this->assertSame($sent_api_url, $request->sent_server);
	}
	
	public function provideSetProtocol()
	{
		return array(
			array('http://api.test', 'http', 'http://', 'http://api.test'),
			array('http://api.test', 'ftp', 'ftp://', 'ftp://api.test'),
			array('api.test', 'http', 'http://', 'http://api.test'),
			array('http://api.test/prefix', 'http', 'http://', 'http://api.test/prefix'),
			array('http://api.test/prefix', 'ssh', 'ssh://', 'ssh://api.test/prefix'),
			array('http://api.test/prefix', 'ssh+ftp', 'ssh+ftp://', 'ssh+ftp://api.test/prefix'),
		);
	}
	
	/**
	 * Test if getApiProtocolHost parses API URLs correctly
	 * 
	 * @param string $api_url
	 *   API URL to set
	 * @param string $protocol
	 *   Protocol of the API URL
	 * @param string $host
	 *   Host of the API URL
	 * @param string $prefix
	 *   Prefix of the API URL
	 * 
	 * @dataProvider provideGetApiProtocolHost
	 */
	public function testGetApiProtocolHost($api_url, $protocol, $host, $prefix)
	{
		$request = new TestRequestBase('command', 'action');
		$request->api_url = $api_url;
		
		$protohost = $request->getApiProtocolHost();
		$this->assertSame($protocol, $protohost['protocol']);
		$this->assertSame($host, $protohost['host']);
		$this->assertSame($prefix, $protohost['prefix']);
	}
	
	public function provideGetApiProtocolHost()
	{
		return array(
			array('http://api.test', 'http', 'api.test', ''),
			array('http://api.test/prefix', 'http', 'api.test', '/prefix'),
			array('http://api.test/long/path', 'http', 'api.test', '/long/path'),
			array('api.streamonecloud.net', null, 'api.streamonecloud.net', ''),
			array('api.streamonecloud.net/prefix', null, 'api.streamonecloud.net', '/prefix'),
			array('ssh://api.streamone.nl', 'ssh', 'api.streamone.nl', ''),
			array('ssh://api.streamone.nl/prefix', 'ssh', 'api.streamone.nl', '/prefix'),
			array('ssl+http://localhost', 'ssl+http', 'localhost', ''),
			array('ssl+http://localhost/long/path', 'ssl+http', 'localhost', '/long/path'),
			array('http://192.168.178.42', 'http', '192.168.178.42', ''),
			array('http://192.168.178.42/prefix', 'http', '192.168.178.42', '/prefix'),
			array('http://192.168.178.42/long/path', 'http', '192.168.178.42', '/long/path'),
		);
	}
	
	/**
	 * Test the valid() method
	 * 
	 * @param bool $valid
	 *   True if and only if the given response is valid
	 * @param string $reponse
	 *   The response to test
	 * 
	 * @dataProvider provideValid
	 */
	public function testValid($valid, $response)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		$this->assertSame($valid, $request->valid());
	}
	
	public function provideValid()
	{
		return array(
			array(false, 5),
			array(false, '5'),
			array(false, 'bla'),
			array(false, '"foo"'),
			array(false, '5'),
			array(false, 'null'),
			array(false, 'true'),
			array(false, 'false'),
			array(false, '[1,2,3]'),
			array(false, '{"foo":"bar"}'),
			array(false, '{"header":[]}'),
			array(false, '{"body":[]}'),
			array(false, '{"header":"foo","body":null}'),
			array(false, '{"header":{"status":0},"body":null}'),
			array(false, '{"header":{"statusmessage":"OK"},"body":[]}'),
			array(false, '{"header":{"status":"OK","statusmessage":"OK"},"body":[]}'),
			array(false, '{"header":{"status":0,"statusmessage":0},"body":[]}'),
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":null}'),
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":true}'),
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":false}'),
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":"foobar"}'),
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}'),
			array(true, '{"header":{"status":1,"statusmessage":"Internal error"},"body":5}'),
			array(true, '{"header":{"status":1337,"statusmessage":"OMG NOES"},"body":[1,2,3]}'),
		);
	}
	
	/**
	 * Test the success() method
	 * 
	 * @param bool $success
	 *   True if and only if the given response indicates success
	 * @param string $response
	 *   The response to test
	 * 
	 * @dataProvider provideSuccess
	 */
	public function testSuccess($success, $response)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		$this->assertSame($success, $request->success());
	}
	
	public function provideSuccess()
	{
		return array(
			array(false, '5'), // Invalid
			array(true, '{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}'),
			array(false, '{"header":{"status":1,"statusmessage":"Internal error"},"body":5}'),
			array(false, '{"header":{"status":1337,"statusmessage":"OMG NOES"},"body":[1,2,3]}'),
		);
	}
	
	/**
	 * Test the header() method
	 * 
	 * @param string $response
	 *   The response to test
	 * @param array|null $header
	 *   The header from the response
	 * 
	 * @dataProvider provideHeader
	 */
	public function testHeader($response, $header)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		if ($header === null)
		{
			$this->assertNull($request->header());
		}
		else
		{
			$this->assertArraysSameUnordered($header, $request->header());
		}
	}
	
	public function provideHeader()
	{
		return array(
			array('5', null),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}',
				array(
					'status' => 0,
					'statusmessage' => 'OK'
				)
			),
			array(
				'{"header":{"status":1,"statusmessage":"Internal error"},"body":[1,1,2,3,5,8,13]}',
				array(
					'status' => 1,
					'statusmessage' => 'Internal error'
				)
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK","apiversion":3},"body":null}',
				array(
					'status' => 0,
					'statusmessage' => 'OK',
					'apiversion' => 3
				)
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK","cacheable":true},"body":null}',
				array(
					'status' => 0,
					'statusmessage' => 'OK',
					'cacheable' => true
				)
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK","cacheable":false},"body":null}',
				array(
					'status' => 0,
					'statusmessage' => 'OK',
					'cacheable' => false
				)
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK","timezone":"Europe/Amsterdam"},"body":null}',
				array(
					'status' => 0,
					'statusmessage' => 'OK',
					'timezone' => 'Europe/Amsterdam'
				)
			),
		);
	}
	
	/**
	 * Test the body() method
	 * 
	 * @param string $response
	 *   The response to test
	 * @param array|null $body
	 *   The body from the response
	 * 
	 * @dataProvider provideBody
	 */
	public function testBody($response, $body)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		if (is_array($body))
		{
			$this->assertArraysSameUnordered($body, $request->body());
		}
		else
		{
			$this->assertSame($body, $request->body());
		}
	}
	
	public function provideBody()
	{
		return array(
			array('5', null),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}',
				array('foo' => 'bar')
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":null}',
				null
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":true}',
				true
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":false}',
				false
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":4}',
				4
			),
			array(
				'{"header":{"status":0,"statusmessage":"OK"},"body":"foobar"}',
				"foobar"
			),
		);
	}
	
	/**
	 * Test the plainResponse() method
	 * 
	 * @param bool $expect_null
	 *   True to expect null, false to expect exactly $response
	 * @param string $reponse
	 *   The response to test
	 * 
	 * @dataProvider providePlainResponse
	 */
	public function testPlainResponse($expect_null, $response)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		if ($expect_null)
		{
			$this->assertNull($request->plainResponse());
		}
		else
		{
			$this->assertSame($response, $request->plainResponse());
		}
	}
	
	public function providePlainResponse()
	{
		return array(
			array(true, 5),
			array(true, true),
			array(true, false),
			array(true, null),
			array(true, array()),
			array(true, new stdClass),
			array(false, 'foobar'),
			array(false, '{"header":{"status":0,"statusmessage":"OK"},"body":4}'),
		);
	}
	
	/**
	 * Test the status() method
	 * 
	 * @param int|null $status
	 *   Expected status
	 * @param string $reponse
	 *   The response to test
	 * 
	 * @dataProvider provideStatus
	 */
	public function testStatus($status, $response)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		$this->assertSame($status, $request->status());
	}
	
	public function provideStatus()
	{
		return array(
			array(null, '5'),
			array(0, '{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}'),
			array(1, '{"header":{"status":1,"statusmessage":"Internal error"},"body":5}'),
			array(1337, '{"header":{"status":1337,"statusmessage":"OMG NOES"},"body":[1,2,3]}'),
		);
	}
	
	/**
	 * Test the statusMessage() method
	 * 
	 * @param string $message
	 *   Expected status message
	 * @param string $reponse
	 *   The response to test
	 * 
	 * @dataProvider provideStatusMessage
	 */
	public function testStatusMessage($message, $response)
	{
		$request = new TestRequestBase('command', 'action');
		$request->response = $response;
		$request->execute();
		
		$this->assertSame($message, $request->statusMessage());
	}
	
	public function provideStatusMessage()
	{
		return array(
			array('invalid response', '5'),
			array('OK', '{"header":{"status":0,"statusmessage":"OK"},"body":{"foo":"bar"}}'),
			array('Internal error', '{"header":{"status":1,"statusmessage":"Internal error"},"body":5}'),
			array('OMG NOES', '{"header":{"status":1337,"statusmessage":"OMG NOES"},"body":[1,2,3]}'),
		);
	}
	
	/**
	 * Test the signing of requests, checking parameters and signature
	 * 
	 * @param string $api_url
	 *   API URL to test
	 * @param string $command
	 *   The command to test
	 * @param string $action
	 *   The action to test
	 * @param array $arguments
	 *   A (string)key => (string)value array of arguments
	 * @param array $parameters
	 *   A (string)key => (string)value array of expected parameters excluding timestamp and
	 *   signature; those two will be deduced automatically.
	 * @param string $sign_key
	 *   The signing key to use
	 * 
	 * @dataProvider provideSigning
	 */
	public function testSigning($api_url, $command, $action, $arguments, $parameters, $sign_key)
	{
		$request = new TestRequestBase($command, $action);
		$request->api_url = $api_url;
		$request->signing_key = $sign_key;
		
		foreach ($arguments as $key => $value)
		{
			$request->setArgument($key, $value);
		}
		
		$request->execute();
		
		// Construct values to check
		$path = '/api/' . $command . '/' . $action;
		$parameters['timestamp'] = $request->sent_parameters['timestamp'];
		
		$to_sign = $path . '?' . http_build_query($parameters) . '&' . http_build_query($arguments);
		$parameters['signature'] = hash_hmac('sha1', $to_sign, $sign_key);
		
		// Check whether all things match
		$this->assertSame($api_url, $request->sent_server);
		$this->assertSame($path, $request->sent_path);
		$this->assertSame($parameters['signature'], $request->sent_parameters['signature']);
		$this->assertArraysSameUnordered($parameters, $request->sent_parameters);
		$this->assertArraysSameUnordered($arguments, $request->sent_arguments);
	}
	
	public function provideSigning()
	{
		return array(
			array(
				'http://api.test',
				'command',
				'action',
				array('item' => 'ITEM', 'account' => 'ACCOUNT'),
				array(
					'api' => 3,
					'format' => 'json',
				),
				'PSK'
			),
		);
	}
}
