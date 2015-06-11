<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * Execute a request to the StreamOne API
 * 
 * This class represents a request to the StreamOne API. To execute a new request, first construct
 * an instance of this class by specifying the command and action to the constructor. The various
 * arguments and options of the request can then be specified and then the request can be actually
 * sent to the StreamOne API server by executing the request. There are various functions to
 * inspect the retrieved response.
 * 
 * \code
 * use StreamOne\API\v3\Platform as StreamOnePlatform;
 * $platform = new StreamOnePlatform(array(...));
 * $request = $platform->newRequest('item', 'view');
 * $request->setAccount('Mn9mdVb-02mA')
 *         ->setArgument('item', 'vMD_9k1SmkS5')
 *         ->execute();
 * if ($request->success())
 * {
 *     var_dump($request->body());
 * }
 * \endcode
 *
 * This class only supports version 3 of the StreamOne API. All configuration is done using the
 * Config class.
 * 
 * This class inherits from RequestBase, which is a very basic request-class implementing
 * only the basics of setting arguments and parameters, and generic signing of requests. This
 * class adds specific signing for users, applications and sessions, as well as a basic caching
 * mechanism.
 */
class Request extends RequestBase
{
	/**
	 * @var Config $config
	 *   The Config object with information for this request
	 */
	private $config;
	
	/**
	 * @var bool $from_cache
	 *   Whether the response was retrieved from the cache
	 */
	private $from_cache = false;

	/**
	 * @var int|null $cache_age
	 *   If the response was retrieved from the cache, how old it is in seconds; otherwise null
	 */
	private $cache_age = null;

	/**
	 * Construct a new request
	 * 
	 * @see RequestBase::__construct
	 * 
	 * @param string $command
	 *   The API command to call
	 * @param string $action
	 *   The action to perform on the API command
	 * @param Config $config
	 *   The Config object to use for this request
	 * 
	 * @throw \UnexpectedValueException
	 *   The given Config object is not valid for performing requests
	 */
	public function __construct($command, $action, Config $config)
	{
		parent::__construct($command, $action);
		
		$this->config = $config;

		// Check if a default account is specified and set it as a parameter. Can later be overridden
		if ($this->config->hasDefaultAccountId())
		{
			$this->setParameter('account', $this->config->getDefaultAccountId());
		}
		
		// Validate configuration
		if (!$config->validateForRequests())
		{
			throw new \UnexpectedValueException('Invalid Config object');
		}
		
		// Set correct authentication_type parameter
		switch ($this->config->getAuthenticationType())
		{
			case Config::AUTH_USER:
				$this->setParameter('authentication_type', 'user');
				break;
			
			case Config::AUTH_APPLICATION:
				$this->setParameter('authentication_type', 'application');
				break;
		}
	}
	
	/**
	 * Retrieve the config used for this request
	 * 
	 * @return Config
	 *   The config used for this request
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Execute the prepared request
	 *
	 * This method will first check if there is a cached response for this request. If there is,
	 * the cached response is used. Otherwise, the request is signed and sent to the API server.
	 * The response will be stored in this class for inspection, and in the cache if applicable
	 * for this request.
	 * 
	 * To check whether the request was successful, use the success() method. The header and body
	 * of the response can be obtained using the header() and body() methods of this class. A
	 * request can be unsuccessful because either the response was invalid (check using the valid()
	 * method), or because the status in the header was not OK / 0 (check using the status() and
	 * statusMessage() methods.)
	 *
	 * @see RequestBase::execute
	 *
	 * @return Request
	 *   A reference to this object, to allow chaining
	 */
	public function execute()
	{
		// Check cache
		$response = $this->retrieveCache();
		if ($response === false)
		{
			parent::execute();
		}
		else
		{
			$this->handleResponse($response);
		}

		$this->saveCache();
		
		return $this;
	}

	/**
	 * Retrieve whether this response was retrieved from cache
	 *
	 * @return bool
	 *   True if and only if the response was retrieved from cache
	 */
	public function fromCache()
	{
		return $this->from_cache;
	}

	/**
	 * Retrieve the age of the response retrieved from cache
	 *
	 * @return int
	 *   The age of the response retrieved from cache in seconds. If the response was not
	 *   retrieved from cache, this will return null instead.
	 */
	public function cacheAge()
	{
		return $this->cache_age;
	}

	/**
	 * Retrieve the URL of the StreamOne API server to use.
	 * 
	 * @see RequestBase::apiUrl
	 */
	protected function apiUrl()
	{
		return $this->config->getApiUrl();
	}

	/**
	 * Retrieve the key to use for signing this request.
	 *
	 * @see RequestBase::signingKey
	 */
	protected function signingKey()
	{
		// Config object returns correct key for authentication type in use
		return $this->config->getAuthenticationActorKey();
	}

	/**
	 * Retrieve the parameters to include for signing this request.
	 *
	 * @see RequestBase::parametersForSigning
	 */
	protected function parametersForSigning()
	{
		$parameters = parent::parametersForSigning();
		
		// Set actor ID parameter
		$actor_id = $this->config->getAuthenticationActorId();
		switch ($this->config->getAuthenticationType())
		{
			case Config::AUTH_USER:
				$parameters['user'] = $actor_id;
				break;
			
			case Config::AUTH_APPLICATION:
				$parameters['application'] = $actor_id;
				break;
		}
		
		return $parameters;
	}
	
	/**
	 * Handle a plain-text response as received from the API
	 * 
	 * If the request was valid and contains one of the status codes set in
	 * Config::getVisibleErrors, a very noticable error message will be shown on the
	 * screen. It is advisable that these errors are handled and logged in a less visible manner,
	 * and that the visible_errors configuration variable is then set to an empty array. This is
	 * not done by default to aid in catching these errors during development.
	 *
	 * @see RequestBase::handleResponse
	 * 
	 * @param mixed $response
	 *   The plain-text response as received from the API
	 */
	protected function handleResponse($response)
	{
		parent::handleResponse($response);

		// Check if the response was valid and the status code is one of the visible errors
		if ($this->valid() && $this->config->isVisibleError($this->status()))
		{
			echo '<div style="position:absolute;top:0;left:0;right:0;background-color:black;color:red;font-weight:bold;padding:5px 10px;border:3px outset #d00;z-index:2147483647;font-size:12pt;font-family:sans-serif;">StreamOne API error ' . $this->status() . ': <em>' . $this->statusMessage() . '</em></div>';
		}
	}
	
	/**
	 * Check whether the response is cacheable
	 * 
	 * @return bool
	 *   True if and only if a successful response was given, which is cacheable
	 */
	protected function cacheable()
	{
		if ($this->success())
		{
			$header = $this->header();
			if (array_key_exists('cacheable', $header) && $header['cacheable'])
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determine the key to use for caching
	 * 
	 * @return string
	 *   A cache-key representing this request
	 */
	protected function cacheKey()
	{
		return 's1:request:' . $this->path() . '?' . http_build_query($this->parameters()) . '#' .
			http_build_query($this->arguments());
	}
	
	/**
	 * Attempt to retrieve the result for the current request from the cache
	 * 
	 * @return string
	 *   The cached plain text response if it was found in the cache; false otherwise
	 */
	protected function retrieveCache()
	{
		// Retrieve cache object from config
		$cache = $this->config->getRequestCache();
		
		// Check for response from cache
		$response = $cache->get($this->cacheKey());
		
		if ($response !== false)
		{
			// Object found; store meta-data and return it
			$this->from_cache = true;
			$this->cache_age = $cache->age($this->cacheKey());
			return $response;
		}
		
		// No cache hit
		return false;
	}
	
	/**
	 * Save the result of the current request to the cache
	 * 
	 * This method only saves to cache if the request is cacheable, and if the request was not
	 * retrieved from the cache.
	 */
	protected function saveCache()
	{
		if ($this->cacheable() && !$this->from_cache)
		{
			$cache = $this->config->getRequestCache();
			$cache->set($this->cacheKey(), $this->plainResponse());
		}
	}
}

/**
 * @}
 */
