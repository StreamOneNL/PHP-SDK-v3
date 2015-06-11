<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * The base class for Request, abstracting authentication details
 * 
 * This abstract class provides the basics for doing requests to the StreamOne API, and abstracts
 * the authentication details. This allows for subclasses that just implement a valid
 * authentication scheme, without having to re-implement all the basics of doing requests. For
 * normal use, the Request class provides authentication using users or applications, and
 * SessionRequest provides authentication for requests executed within a session.
 */
abstract class RequestBase
{
	/**
	 * The API command to call
	 */
	private $command;

	/**
	 * The action to perform on the API command called
	 */
	private $action;

	/**
	 * The parameters to use for the API request
	 *
	 * The parameters are the GET-parameters sent, and include meta-data for the request such
	 * as API-version, output type, and authentication parameters. They cannot directly be set.
	 */
	private $parameters;

	/**
	 * The arguments to use for the API request
	 *
	 * The arguments are the POST-data sent, and represent the arguments for the specific API
	 * command and action called.
	 */
	private $arguments;

	/**
	 * The plain-text response received from the API server
	 *
	 * This is the plain-text response as received from the server, or null if no plain-text
	 * response has been received.
	 */
	private $plain_response;

	/**
	 * The parsed response received from the API
	 *
	 * This is the parsed response as received from the server, or null if no parseable response
	 * has been received.
	 */
	private $response;

	/**
	 * The protocol to use for requests
	 */
	private $protocol = null;

	/**
	 * Construct a new request
	 *
	 * @param string $command
	 *   The API command to call
	 * @param string $action
	 *   The action to perform on the API command
	 */
	public function __construct($command, $action)
	{
		$this->command = $command;
		$this->action = $action;

		// Default parameters
		$this->parameters = array(
			'api' => 3,
			'format' => 'json'
		);

		// Arguments starts as an empty array
		$this->arguments = array();
	}
	
	/**
	 * Get the API command to call
	 * 
	 * @return string
	 *   The API command to call
	 */
	public function command()
	{
		return $this->command;
	}
	
	/**
	 * The action to perform on the API command
	 * 
	 * @return string
	 *   The action to perform on the API command
	 */
	public function action()
	{
		return $this->action;
	}

	/**
	 * Set the account to use for this request
	 *
	 * Most actions require an account to be set, but not all. Refer to the documentation of the
	 * action you are executing to read whether providing an account is required or not.
	 *
	 * @param string|null $account
	 *   ID of the account to use for the request; if null, clear account
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setAccount($account)
	{
		if ($account === null && isset($this->parameters['account']))
		{
			unset($this->parameters['account']);
		}
		elseif ($account !== null)
		{
			$this->parameters['account'] = $account;
		}
		
		// If a customer is set clear it, because account and customer are mutually exclusive
		if (isset($this->parameters['customer']))
		{
			unset($this->parameters['customer']);
		}

		return $this;
	}

	/**
	 * Get the account to use for this request
	 *
	 * If an account is not set, return null
	 *
	 * @return string|null
	 *   The ID of the account to use for the request or null if no account is set. If more than
	 *   one account has been set (with setAccounts), the first one will be returned
	 */
	public function getAccount()
	{
		if (isset($this->parameters['account']))
		{
			$accounts = explode(',', $this->parameters['account']);
			if (!empty($accounts))
			{
				return $accounts[0];
			}
		}

		return null;
	}

	/**
	 * Set the accounts to use for this request
	 *
	 * Some actions allow you to set more than one account at the same time. Refer to the
	 * documentation of the action you are executing to read whether providing more than one
	 * account is allowed or not.
	 *
	 * @param array $accounts
	 *   Array with IDs of the accounts to use for the request; if empty, clear accounts
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setAccounts(array $accounts)
	{
		if (empty($accounts) && isset($this->parameters['account']))
		{
			unset($this->parameters['account']);
		}
		elseif (!empty($accounts))
		{
			$this->parameters['account'] = implode(',', $accounts);
		}

		// If a customer is set clear it, because account and customer are mutually exclusive
		if (isset($this->parameters['customer']))
		{
			unset($this->parameters['customer']);
		}

		return $this;
	}

	/**
	 * Get the accounts to use for this request
	 *
	 * If an accounts are set, return an empty array
	 *
	 * @return array()
	 *   An array with the IDs of the accounts to use for the request
	 */
	public function getAccounts()
	{
		if (isset($this->parameters['account']))
		{
			return explode(',', $this->parameters['account']);
		}

		return array();
	}

	/**
	 * Set the customer to use for this request
	 *
	 * Some actions require an account to be set and others have it as an alternative to an account.
	 * Refer to the documentation to check whether it is needed
	 *
	 * @param string|null $customer
	 *   ID of the customer to use for the request; if null clear customer
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setCustomer($customer)
	{
		if ($customer === null && isset($this->parameters['customer']))
		{
			unset($this->parameters['customer']);
		}
		elseif ($customer !== null)
		{
			$this->parameters['customer'] = $customer;
		}

		// If an account is set clear it, because account and customer are mutually exclusive
		if (isset($this->parameters['account']))
		{
			unset($this->parameters['account']);
		}

		return $this;
	}

	/**
	 * Get the customer to use for this request
	 *
	 * If an customer is not set, return null
	 *
	 * @return string|null
	 *   The ID of the customer to use for the request or null if no customer is set
	 */
	public function getCustomer()
	{
		if (isset($this->parameters['customer']))
		{
			return $this->parameters['customer'];
		}

		return null;
	}

	/**
	 * Set the timezone to use for this request.
	 * 
	 * If no timezone is set, the default timezone for the actor (user or application) doing the
	 * request is used.
	 *
	 * @param DateTimeZone $time_zone
	 *   Timezone to use for the request
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setTimeZone(\DateTimeZone $time_zone)
	{
		$this->parameters['timezone'] = $time_zone->getName();

		return $this;
	}
	
	/**
	 * Set the value of a single argument
	 *
	 * @param string $argument
	 *   The name of the argument
	 * @param string $value
	 *   The new value for the argument; null will be translated to an empty string
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setArgument($argument, $value)
	{
		if ($value === null)
		{
			$value = '';
		}
		$this->arguments[$argument] = $value;
		
		return $this;
	}
	
	/**
	 * Retrieve the currently defined arguments
	 *
	 * @return array
	 *   An array containing the currently defined arguments as key=>value pairs
	 */
	public function arguments()
	{
		return $this->arguments;
	}
	
	/**
	 * Set the value of a single parameter
	 *
	 * @param string $parameter
	 *   The name of the parameter
	 * @param string $value
	 *   The new value for the parameter; null will be translated to an empty string
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	protected function setParameter($parameter, $value)
	{
		if ($value === null)
		{
			$value = '';
		}
		$this->parameters[$parameter] = $value;
		
		return $this;
	}
	
	/**
	 * Retrieve the currently defined parameters
	 *
	 * @return array
	 *   An array containing the currently defined parameters as key=>value pairs
	 */
	protected function parameters()
	{
		return $this->parameters;
	}

	/**
	 * Sets the protocol to use for requests, e.g. 'http'
	 * 
	 * Using this method overrides any protocol set in the API URL. The protocol must not
	 * contain trailing '://', even though the protocol() method returns protocols with '://'
	 * appended.
	 *
	 * @param $protocol string
	 *   The protocol to use
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;

		return $this;
	}

	/**
	 * Retrieves the protocol to use for requests, with trailing ://
	 * 
	 * If a protocol has been set using setProtocol(), that protocol is used. Otherwise, if a
	 * protocol is present in the API URL, that protocol is used. If neither gives a valid
	 * protocol, the default of 'http' is used.
	 * 
	 * This method returns the protocol with trailing '://', while setProtocol() requires
	 * a protocol without trailing '://'. For example, when the protocol is set to 'https',
	 * 
	 * 
	 * @return string
	 *   The protocol to use
	 */
	public function protocol()
	{
		if ($this->protocol !== null)
		{
			// Protocol overridden by setProtocol
			return $this->protocol . '://';
		}
		
		// Use protocol from API URL if given
		$protohost = $this->getApiProtocolHost();
		if ($protohost['protocol'] !== null)
		{
			return $protohost['protocol'] . '://';
		}
		
		// No protocol set in any way; default to HTTP
		return 'http://';
	}
	
	/**
	 * Retrieve the API protocol and host, as retrieved from the apiUrl() method
	 * 
	 * The API URL is split into up to 3 parts, the protocol, host and prefix. The following
	 * forms of URLs, as provided by apiUrl(), are supported:
	 * 
	 * - `protocol://host/prefix`
	 * - `protocol://host`
	 * - `host/prefix`
	 * - `host`
	 * 
	 * @return array
	 *   An array with 3 elements:
	 *   - protocol: a string with the protocol specified in the API URL, or null if not present
	 *   - host: a string with the host as specified in the API URL
	 *   - prefix: a possibly empty string with the path prefix of the URL;  contains basically
	 *             everything after the host
	 */
	protected function getApiProtocolHost()
	{
		// a combination of letters, digits, plus ("+"), period ("."), or hyphen ("-")
		$pattern = '@^(?:([a-zA-Z0-9\+\.-]+):/?/?)?([^/]*)(.*)$@';
		$api_url = $this->apiUrl();
		preg_match($pattern, $api_url, $matches);
		return array(
			'protocol' => (strlen($matches[1]) == 0) ? null : $matches[1],
			'host' => $matches[2],
			'prefix' => $matches[3],
		);
	}

	/**
	 * Gather the server, path, parameters, and arguments for the request to execute
	 * 
	 * @return array
	 *   An array with 4 elements:
	 *   - The server (`protocol://host/prefix`) to send the request to
	 *   - The path of the request (`/api/command/action`)
	 *   - The parameters for the request, as a key=>value array, including the parameters
	 *       required for authentication
	 *   - The arguments for the request, as a key=>value array
	 */
	protected function prepareExecute()
	{
		// Gather path, signed parameters and arguments
		$protohost = $this->getApiProtocolHost();
		$server = $this->protocol() . $protohost['host'] . $protohost['prefix'];
		$path = $this->path();
		$parameters = $this->signedParameters();
		$arguments = $this->arguments();

		return array($server, $path, $parameters, $arguments);
	}

	/**
	 * Execute the prepared request
	 *
	 * This will sign the request, send it to the Internal API server, and analyze the response. To
	 * check whether the request was successful and returned no error, use the method success().
	 *
	 * @return RequestBase
	 *   A reference to this object, to allow chaining
	 */
	public function execute()
	{
		list($server, $path, $parameters, $arguments) = $this->prepareExecute();

		// Actually execute the request
		$response = $this->sendRequest($server, $path, $parameters, $arguments);

		// Handle the response
		$this->handleResponse($response);

		return $this;
	}

	/**
	 * Check if the returned response is valid
	 *
	 * A valid response contains a header and a body, and the header contains at least the fields
	 * status and statusmessage with correct types.
	 *
	 * @return bool
	 *   Whether the retrieved response is valid
	 */
	public function valid()
	{
		// The response must be a valid array
		if (($this->response === null) || (!is_array($this->response)))
		{
			return false;
		}

		// The response must have a header and a body
		if (!array_key_exists('header', $this->response) ||
			!array_key_exists('body', $this->response))
		{
			return false;
		}

		// The header must be an array and contain a status and statusmessage
		if (!is_array($this->response['header']) ||
			!array_key_exists('status', $this->response['header']) ||
			!array_key_exists('statusmessage', $this->response['header']))
		{
			return false;
		}

		// The status must be an integer and the statusmessage must be a string
		if (!is_int($this->response['header']['status']) ||
			!is_string($this->response['header']['statusmessage']))
		{
			return false;
		}

		// All is valid
		return true;
	}

	/**
	 * Check if the request was successful
	 *
	 * The request was successful if the response is valid, and the status is 0 (OK).
	 *
	 * @return bool
	 *   Whether the request was successful
	 */
	public function success()
	{
		return ($this->valid() && ($this->response['header']['status'] === 0));
	}

	/**
	 * Retrieve the header as received from the server
	 *
	 * This method returns the response header as received from the server. If the response was
	 * not valid (check with valid()), this method will return null.
	 *
	 * @return array
	 *   The header of the received response; null if the response was not valid
	 */
	public function header()
	{
		if (!$this->valid())
		{
			return null;
		}

		return $this->response['header'];
	}

	/**
	 * Retrieve the body as received from the server
	 *
	 * This method returns the response body as received from the server. If the response was
	 * not valid (check with valid()), this method will return null.
	 *
	 * @return array
	 *   The body of the received response; null if the response was not valid
	 */
	public function body()
	{
		if (!$this->valid())
		{
			return null;
		}

		return $this->response['body'];
	}

	/**
	 * Retrieve the plain-text response as received from the server
	 *
	 * This method returns the entire plain-text response as received from the server. If there was
	 * no valid plain-text response, this method will return null.
	 *
	 * @return string
	 *   The plain-text response; null if no response was received
	 */
	public function plainResponse()
	{
		return $this->plain_response;
	}

	/**
	 * Retrieve the status returned for this request
	 *
	 * @return int
	 *   The status returned for this request, or null if no valid response was received
	 */
	public function status()
	{
		if (!$this->valid())
		{
			return null;
		}
		return $this->response['header']['status'];
	}

	/**
	 * Retrieve the status message returned for this request
	 *
	 * @return string
	 *   The status message returned for this request, or 'invalid response' if no valid response
	 *   was received
	 */
	public function statusMessage()
	{
		if (!$this->valid())
		{
			return 'invalid response';
		}
		return $this->response['header']['statusmessage'];
	}

	/**
	 * This function returns the base URL of the API, with optional protocol and without trailing /
	 *
	 * Subclasses will overwrite this function to get it from the correct configuration
	 *
	 * @return string
	 *   The base URL of the API
	 */
	abstract protected function apiUrl();

	/**
	 * This function should return the key used for signing the request
	 *
	 * Subclasses will overwrite this function to provide the correct key
	 *
	 * @return string
	 *   The key used for signing
	 */
	abstract protected function signingKey();

	/**
	 * Retrieve the path to use for the API request
	 *
	 * @return string
	 *   The path for the API request
	 */
	protected function path()
	{
		return '/api/' . $this->command . '/' . $this->action;
	}

	/**
	 * Retrieve the parameters used for signing
	 *
	 * Subclasses will add the parameters that are used specifically for those classes
	 *
	 * @return array
	 *   An array containing the parameters needed for signing
	 */
	protected function parametersForSigning()
	{
		$parameters = $this->parameters();

		// Store a single timestamp to use for signing
		$ts = time();

		// Add basic authentication parameters
		$parameters['timestamp'] = $ts;

		return $parameters;
	}

	/**
	 * Retrieve the signed parameters for the current request
	 *
	 * This method will lookup the current path, parameters and arguments, calculates the
	 * authentication parameters, and returns the new set of parameters.
	 *
	 * @return array
	 *   An array containing the defined parameters, as well as authentication parameters, both as
	 *   key=>value pairs
	 */
	protected function signedParameters()
	{
		$parameters = $this->parametersForSigning();
		$parameters['signature'] = $this->signature();

		return $parameters;
	}

	/**
	 * Returns the signature for the current request
	 *
	 * @return String
	 *   The signature for the current request
	 */
	protected function signature()
	{
		$parameters = $this->parametersForSigning();
		$path = $this->path();
		$arguments = $this->arguments();

		// Calculate signature
		$url = $path . '?' . http_build_query($parameters) . '&' . http_build_query($arguments);
		$key = $this->signingKey();

		return hash_hmac('sha1', $url, $key);
	}

	/**
	 * Actually send a signed request to the server
	 *
	 * @param string $server
	 *   The API server to use
	 * @param string $path
	 *   The request path
	 * @param array $parameters
	 *   The request parameters as key=>value pairs
	 * @param array $arguments
	 *   The request arguments as key=>value pairs
	 * @return string
	 *   The plain-text response from the server; false if the request failed
	 *
	 * @codeCoverageIgnore
	 *   This function is deliberately not included in unit tests
	 */
	protected function sendRequest($server, $path, $parameters, $arguments)
	{
		// Build the URL (including GET-params)
		$url = $server . $path . '?' . http_build_query($parameters);

		// Create the required stream context for POSTing
		$stream_parameters = array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query($arguments),
				'header' => "Content-Type: application/x-www-form-urlencoded"
			)
		);
		$stream_parameters = array_merge($stream_parameters, $this->extraStreamParameters());
		$context = stream_context_create($stream_parameters);

		// Actually do the request and return the response
		return file_get_contents($url, false, $context);
	}

	/**
	 * Handle a plain-text response as received from the API
	 *
	 * @param mixed $response
	 *   The plain-text response as received from the API; parsing will not be succesful if this is
	 *   not a string.
	 */
	protected function handleResponse($response)
	{
		// Only attempt handling the response if it is a string
		if (is_string($response))
		{
			$this->plain_response = $response;

			// Attempt to decode the (JSON) response; returns null if failed
			$this->response = json_decode($response, true);
		}
	}

	/**
	 * This function returns extra parameters used for stream_context_create in sending requests
	 *
	 * @return array
	 *   Extra parameters to pass to stream_context_create for sending requests
	 */
	protected function extraStreamParameters()
	{
		return array();
	}
}

/**
 * @}
 */
