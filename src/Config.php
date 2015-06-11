<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * The configuration for the StreamOne SDK.
 * 
 * This class is used internally by the SDK to get the correct configuration values to the
 * correct places. It is not instantiated correctly, but the constructor is called with an
 * array of options when a Platform is constructed.
 */
class Config
{
	/// Unknown authentication type
	const AUTH_UNKNOWN = '-';
	/// User authentication type
	const AUTH_USER = 'user';
	/// Application authentication type
	const AUTH_APPLICATION = 'application';
	
	/**
	 * @var string $api_url
	 *   API URL
	 */
	private $api_url = "http://api.streamoneonecloud.net";
	
	/**
	 * @var string $authentication_type
	 *   Authentication type
	 */
	private $authentication_type = self::AUTH_UNKNOWN;
	
	/**
	 * @var string $auth_actor_id
	 *   Actor ID for authentication
	 */
	private $auth_actor_id = '';
	
	/**
	 * @var string $auth_actor_psk
	 *   Actor pre-shared key for authentication
	 */
	private $auth_actor_psk = '';
	
	/**
	 * @var string|null $default_account_id
	 *   Default account ID
	 */
	private $default_account_id = null;
	
	/**
	 * @var array $visible_errors
	 *   Array of status codes to show a very visible error for
	 */
	private $visible_errors = array(2,3,4,5,7);
	
	/**
	 * @var RequestFactoryInterface $request_factory
	 *   Request factory to use to instantiate new requests
	 */
	private $request_factory = null;
	
	/**
	 * @var CacheInterface $request_cache
	 *   Caching object to use for requests; must implement CacheInterface
	 */
	private $request_cache = null;
	
	/**
	 * @var CacheInterface $token_cache
	 *   Caching object to use for roles and tokens; must implement CacheInterface
	 */
	private $token_cache = null;
	
	/**
	 * @var bool $use_session_for_token_cache
	 *   Whether to use the session for roles and tokens cache if using a session. If true, the
	 *   above cache will never be used and can thus be null
	 */
	private $use_session_for_token_cache = true;
	
	/**
	 * @var SessionStoreInterface $session_store
	 *   Default session store to use; must implement SessionStoreInterface
	 */
	private $session_store = null;
	
	
	/**
	 * Construct an instance of the Config class
	 * 
	 * The $options argument can be used to set the following options:
	 * 
	 * - api_url: URL of the API
	 *            - see setApiUrl()
	 * - authentication_type: authentication type to use either 'user' or 'application'
	 *                        - see setUserAuthentication() and setApplicationAuthentication()
	 * - application_id: application ID to use for application authentication; only used if
	 *                   authentication_type is 'application'
	 *                   - see setApplicationAuthentication()
	 * - application_psk: application pre-shared key to use for application authentication; only
	 *                    used if authentication_type is 'application'
	 *                    - see setApplicationAuthentication()
	 * - user_id: user ID to use for user authentication; only used if authentication_type
	 *            is 'user'
	 *            - see setUserAuthentication()
	 * - user_psk: user pre-shared key to use for user authentication; only used if
	 *             authentication_type is 'user'
	 *             - see setUserAuthentication()
	 * - default_account_id: account ID of the default account to use for all requests; leave
	 *                       empty to use no account by default
	 *                       - see setDefaultAccountId()
	 * - visible_errors: an array of status codes that result in a very visible error bar
	 *                   - see setVisibleErrors()
	 * - request_factory: request factory object to use for creating requests, implementing
	 *                    RequestFactoryInterface, or an array with the correct arguments to
	 *                    constructRequestFactory() to create one.
	 *                    - see setRequestFactory() and constructRequestFactory()
	 * - cache:         caching object to use for both requests and tokens and roles, implementing
	 *                  CacheInterface, or an array with the correct arguments to constructCache()
	 *                  to create one
	 *                  - see setCache() and constructCache()
	 * - request_cache: caching object to use for requests, implementing CacheInterface, or an
	 *                  array with the correct arguments to constructCache() to create one. This
	 *                  will overwrite any cache set with the 'cache' option
	 *                  - see setRequestCache() and constructCache()
	 * - token_cache: tokens and roles caching object to use, implementing CacheInterface, or an
	 *                array with the correct arguments to constructCache() to create one. This
	 *                  will overwrite any cache set with the 'cache' option
	 *                - see setTokenCache() and constructCache()
	 * - use_session_for_token_cache: whether to use the session to store tokens and roles if using
	 *                                a session. If false, the cache object returned by
	 *                                getTokenCache() will be used
	 *                                - see setUseSessionForTokenCache()
	 * - session_store: session store to use, implementing SessionStoreInterface, or an array with
	 *                  the correct arguments to constructSessionStore() to create one
	 *                  - see setSessionStore() and constructSessionStore()
	 * 
	 * @param array $options
	 *   A key=>value array of options to use
	 * 
	 * @throws \InvalidArgumentException
	 *   An authentication type is provided, but the necessary fields for that authentication type
	 *   are not provided. For user authentication, user_id and user_psk must be provided. For
	 *   application authentication, application_id and application_psk must be provided.
	 * @throws \InvalidArgumentException
	 *   The provided cache or session_store was either an incorrect class, or not an array with
	 *   the correct options for the corresponding factory method.
	 */
	public function __construct(array $options)
	{
		// Resolve options from options array
		$allowed_options = array(
			'api_url' => 'setApiUrl',
			'default_account_id' => 'setDefaultAccountId',
			'visible_errors' => 'setVisibleErrors',
			'use_session_for_token_cache' => 'setUseSessionForTokenCache',
		);
		
		foreach ($allowed_options as $key => $method)
		{
			if (array_key_exists($key, $options))
			{
				call_user_func(array($this, $method), $options[$key]);
			}
		}
		
		// Check authentication options
		if (array_key_exists('authentication_type', $options))
		{
			switch ($options['authentication_type'])
			{
				case 'user':
					if (!array_key_exists('user_id', $options) ||
						!array_key_exists('user_psk', $options))
					{
						throw new \InvalidArgumentException("Missing user_id or user_psk");
					}
					$this->setUserAuthentication($options['user_id'], $options['user_psk']);
					break;
				
				case 'application':
					if (!array_key_exists('application_id', $options) ||
						!array_key_exists('application_psk', $options))
					{
						throw new \InvalidArgumentException("Missing application_id or application_psk");
					}
					$this->setApplicationAuthentication($options['application_id'],
					                                    $options['application_psk']);
					break;
				
				default:
					throw new \InvalidArgumentException("Unknown authentication type '" .
					                                   $options['authentication_type'] . "'");
			}
		}
		
		// Check possibly to be constructed objects
		$object_options = array(
			'request_factory' => array(
				'class' => 'StreamOne\\API\\v3\\RequestFactoryInterface',
				'factory' => 'constructRequestFactory',
				'setter' => 'setRequestFactory',
			),
			'cache' => array(
				'class' => 'StreamOne\\API\\v3\\CacheInterface',
				'factory' => 'constructCache',
				'setter' => 'setCache',
			),
			'request_cache' => array(
				'class' => 'StreamOne\\API\\v3\\CacheInterface',
				'factory' => 'constructCache',
				'setter' => 'setRequestCache',
			),
			'token_cache' => array(
				'class' => 'StreamOne\\API\\v3\\CacheInterface',
				'factory' => 'constructCache',
				'setter' => 'setTokenCache',
			),
			'session_store' => array(
				'class' => 'StreamOne\\API\\v3\\SessionStoreInterface',
				'factory' => 'constructSessionStore',
				'setter' => 'setSessionStore',
			),
		);
		
		foreach ($object_options as $key => $data)
		{
			if (array_key_exists($key, $options))
			{
				$object = $options[$key];
				
				// Call factory method if the option is set as an array
				if (is_array($object))
				{
					// Check that a class name is given
					if ((count($object) < 1) || !is_string($object[0]))
					{
						throw new \InvalidArgumentException("No class name given for " . $key .
						                                    " constructor");
					}
					// This will throw InvalidArgumentException on errors
					$object = call_user_func_array(array($this, $data['factory']), $object);
				}
				
				// Check if we have an object of the correct type
				if (!($object instanceof $data['class']))
				{
					throw new \InvalidArgumentException("Given " . $key . " does not implement " .
					                                    $data['class']);
				}
				
				// Set the correct option with the constructed object
				call_user_func(array($this, $data['setter']), $object);
			}
		}
		
		// Instantiate default factory, caches and session store if none was provided
		if ($this->request_factory === null)
		{
			$this->request_factory = new RequestFactory();
		}
		if ($this->request_cache === null)
		{
			$this->request_cache = new NoopCache;
		}
		if ($this->token_cache === null)
		{
			$this->token_cache = new NoopCache;
		}
		if ($this->session_store === null)
		{
			$this->session_store = new PhpSessionStore;
		}
	}
	
	
	/**
	 * Resolve a class name to a ReflectionClass
	 * 
	 * This method attemps to resolve a class name to a ReflectionClass, but first looking for
	 * the given class name in the StreamOne\API\v3 namespace, and if not found there, it looks
	 * in the global scope.
	 * 
	 * @param string $name
	 *   Name of the class to resolve
	 * @return \ReflectionClass
	 *   A ReflectionClass instance for the given class name
	 * 
	 * @throws \InvalidArgumentException
	 *   The given class name does not resolve to a valid class
	 */
	protected function resolveReflectionClass($name)
	{
		// Attempt to resolve class in the SDK namespace first
		$class_name = "StreamOne\\API\\v3\\" . $name;
		if (class_exists($class_name))
		{
			return new \ReflectionClass($class_name);
		}
		
		// Attempt to resolve class in the global namespace
		if (class_exists($name))
		{
			return new \ReflectionClass($name);
		}
		
		// Class not found
		throw new \InvalidArgumentException("Class '" . $name . "' not found");
	}
	
	/**
	 * Construct a request factory
	 *
	 * @param string $name
	 *   Name of the factory object to construct; can either be a class within the StreamOne\API\v3
	 *   namespace, or one in the global namespace. Will be resolved in that order. The referenced
	 *   class must implement RequestFactoryInterface.
	 * @param mixed $name,...
	 *   Constructor arguments for the specified factory
	 * @return RequestFactoryInterface
	 *   The constructed factory
	 *
	 * @throws \InvalidArgumentException
	 *   There is no class named $name, it does not implement RequestFactoryInterface, or the passed
	 *   constructor arguments are invalid.
	 */
	public function constructRequestFactory($name)
	{
		// Will throw InvalidArgumentException if class does not exist
		$class = $this->resolveReflectionClass($name);
		
		// Check if class implements CacheInterface
		if (!$class->implementsInterface('StreamOne\\API\\v3\\RequestFactoryInterface'))
		{
			throw new \InvalidArgumentException("Class " . $name . " does not implement RequestFactoryInterface");
		}
		
		// Construct cache from arguments and return it
		$args = func_get_args();
		array_shift($args);
		
		return $class->newInstanceArgs($args);
	}
	
	/**
	 * Construct a cache
	 * 
	 * @param string $name
	 *   Name of the cache object to construct; can either be a class within the StreamOne\API\v3
	 *   namespace, or one in the global namespace. Will be resolved in that order. The referenced
	 *   class must implement CacheInterface.
	 * @param mixed $name,...
	 *   Constructor arguments for the specified cache
	 * @return CacheInterface
	 *   The constructed cache
	 * 
	 * @throws \InvalidArgumentException
	 *   There is no class named $name, it does not implement CacheInterface, or the passed
	 *   constructor arguments are invalid.
	 */
	public function constructCache($name)
	{
		// Will throw InvalidArgumentException if class does not exist
		$class = $this->resolveReflectionClass($name);
		
		// Check if class implements CacheInterface
		if (!$class->implementsInterface('StreamOne\\API\\v3\\CacheInterface'))
		{
			throw new \InvalidArgumentException("Class " . $name . " does not implement CacheInterface");
		}
		
		// Construct cache from arguments and return it
		$args = func_get_args();
		array_shift($args);
		
		return $class->newInstanceArgs($args);
	}
	
	/**
	 * Construct a session store
	 * 
	 * @param string $name
	 *   Name of the session store object to construct; can either be a class within the
	 *   StreamOne\API\v3 namespace, or one in the global namespace. Will be resolved in
	 *   that order. The referenced class must implement SessionStoreInterface.
	 * @param mixed $name,...
	 *   Constructor arguments for the specified session store
	 * @return SessionStoreInterface
	 *   The constructed session store
	 * 
	 * @throws \InvalidArgumentException
	 *   There is no class named $name, it does not implement SessionStoreInterface, or the passed
	 *   constructor arguments are invalid.
	 */
	public function constructSessionStore($name)
	{
		// Will throw InvalidArgumentException if class does not exist
		$class = $this->resolveReflectionClass($name);
		
		// Check if class implements SessionStoreInterface
		if (!$class->implementsInterface('StreamOne\\API\\v3\\SessionStoreInterface'))
		{
			throw new \InvalidArgumentException("Class " . $name . " does not implement SessionStoreInterface");
		}
		
		// Construct session store from arguments and return it
		$args = func_get_args();
		array_shift($args);
		
		return $class->newInstanceArgs($args);
	}
	
	
	/**
	 * Set the API URL to use for API requests
	 * 
	 * The API URL must be a fully-qualified URL to the API. By default, the API URL for the
	 * StreamOne Cloud Platform (http://api.streamonecloud.net) is used. There is usually no
	 * need to change this unless a private deployment of the platform is used.
	 * 
	 * @param string $url
	 *   The API URL to use
	 */
	public function setApiUrl($url)
	{
		$this->api_url = $url;
	}
	
	/**
	 * Retrieve the API URL used for API requests
	 * 
	 * @see setApiUrl()
	 * 
	 * @return string
	 *   The API URL to use
	 */
	public function getApiUrl()
	{
		return $this->api_url;
	}
	
	
	/**
	 * Enable user authentication with the given user ID and pre-shared key
	 * 
	 * @param string $user_id
	 *   User ID of the user to use for authentication
	 * @param string $user_psk
	 *   User pre-shared key of the user to use for authentication
	 */
	public function setUserAuthentication($user_id, $user_psk)
	{
		$this->authentication_type = self::AUTH_USER;
		$this->auth_actor_id = $user_id;
		$this->auth_actor_psk = $user_psk;
	}
	
	/**
	 * Enable application authentication with the given application ID and pre-shared key
	 * 
	 * @param string $application_id
	 *   Application ID of the application to use for authentication
	 * @param string $application_psk
	 *   Application pre-shared key of the application to use for authentication
	 */
	public function setApplicationAuthentication($application_id, $application_psk)
	{
		$this->authentication_type = self::AUTH_APPLICATION;
		$this->auth_actor_id = $application_id;
		$this->auth_actor_psk = $application_psk;
	}
	
	/**
	 * Get the currently enabled authentication type
	 * 
	 * @return mixed
	 *   One of the following values:
	 *   - Config::AUTH_UNKNOWN if no authentication type is configured
	 *   - Config::AUTH_USER if user authentication is enabled
	 *   - Config::AUTH_APPLICATION if application authentication is enabled
	 */
	public function getAuthenticationType()
	{
		return $this->authentication_type;
	}
	
	/**
	 * Get the current actor ID used for authentication
	 * 
	 * When user authentication is enabled, this returns the user ID to use.
	 * 
	 * When application authentication is enabled, this returns the application ID to use.
	 * 
	 * @return string
	 *   The actor ID to use for authentication
	 */
	public function getAuthenticationActorId()
	{
		return $this->auth_actor_id;
	}
	
	/**
	 * Get the current actor pre-shared key used for authentication
	 * 
	 * When user authentication is enabled, this returns the user PSK to use.
	 * 
	 * When application authentication is enabled, this returns the application PSK to use.
	 * 
	 * @return string
	 *   The actor pre-shared key to use for authentication
	 */
	public function getAuthenticationActorKey()
	{
		return $this->auth_actor_psk;
	}
	
	
	/**
	 * Set the default account ID to use for API requests
	 * 
	 * If a default account is set, new requests obtained from Platform::newRequest will by
	 * default use that account. It is still possible to override this by using
	 * Request::setAccount() on the obtained request.
	 * 
	 * @param string $account_id
	 *   Default account ID for API requests; null to disable
	 */
	public function setDefaultAccountId($account_id)
	{
		$this->default_account_id = $account_id;
	}
	
	/**
	 * Get the default account ID to use for API requests
	 * 
	 * @return string
	 *   Default account ID for API requests; null if not enabled
	 */
	public function getDefaultAccountId()
	{
		return $this->default_account_id;
	}
	
	/**
	 * Check if a default account ID is specified
	 * 
	 * @return bool
	 *   True if and only if a default account ID is specified
	 */
	public function hasDefaultAccountId()
	{
		return ($this->default_account_id !== null);
	}
	
	
	/**
	 * Set the statuses which will give large visible errors when received
	 * 
	 * The Request class will insert HTML code to display a large visible error bar on top of
	 * the page when API requests return one of the status codes set for this option. To
	 * disable any visible warnings, set this option to an empty array.
	 * 
	 * The default value (status codes 2, 3, 4, 5 and 7) shows errors which are usually caused
	 * by a wrong configuration option or incorrect API usage. These are enabled by default to
	 * aid in development, and it is strongly recommended to disable visible errors in a
	 * production environment.
	 * 
	 * @param array $visible_errors
	 *   The status codes to display visible errors for; use an empty array to show no errors
	 */
	public function setVisibleErrors(array $visible_errors)
	{
		$this->visible_errors = $visible_errors;
	}
	
	/**
	 * Get the status codes which will result in large visible errors when received
	 * 
	 * @return array
	 *   The status codes to display visible errors for
	 */
	public function getVisibleErrors()
	{
		return $this->visible_errors;
	}
	
	/**
	 * Check if a given status code should produce a visible error
	 * 
	 * @param int $status
	 *   The status code to check
	 * @return bool
	 *   True if and only if the given status code should produce a visible error
	 */
	public function isVisibleError($status)
	{
		return in_array($status, $this->getVisibleErrors());
	}
	
	/**
	 * Set the factory object to use for instantiating requests
	 *
	 * The factory object will be used to create (Session)Request objects
	 * Any caching object used must implement the RequestFactoryInterface.
	 *
	 * The SDK provides the following factory class:
	 * - RequestFactory, which will just use the Request and SessionRequest classes directly (default)
	 *
	 * @param RequestFactoryInterface $request_factory
	 *   The factory object to use
	 */
	public function setRequestFactory(RequestFactoryInterface $request_factory)
	{
		$this->request_factory = $request_factory;
	}
	
	/**
	 * Get the request factory object used for instantiating requests
	 *
	 * @return RequestFactoryInterface
	 *   The request factory object used
	 */
	public function getRequestFactory()
	{
		return $this->request_factory;
	}
	
	/**
	 * Set the caching object to use for both requests and tokens and roles
	 *
	 * The caching object will be used by the Request class to cache requests when appropiate and by
	 * the Actor class to cache tokens and roles.
	 * Any caching object used must implement the CacheInterface.
	 *
	 * The SDK provides the following caching classes:
	 * - NoopCache, which will not cache anything (default)
	 * - FileCache, which will cache to files on disk
	 * - MemCache, which will cache on a memcached server
	 * - MemoryCache, which will cache in memory
	 *
	 * @param CacheInterface $cache
	 *   The caching object to use
	 */
	public function setCache(CacheInterface $cache)
	{
		$this->request_cache = $cache;
		$this->token_cache = $cache;
	}
	
	/**
	 * Set the caching object to use for requests
	 * 
	 * The caching object will be used by the Request class to cache requests when appropiate.
	 * Any caching object used must implement the CacheInterface.
	 * 
	 * The SDK provides the following caching classes:
	 * - NoopCache, which will not cache anything (default)
	 * - FileCache, which will cache to files on disk
	 * - MemCache, which will cache on a memcached server
	 * - MemoryCache, which will cache in memory
	 * 
	 * @param CacheInterface $cache
	 *   The caching object to use
	 */
	public function setRequestCache(CacheInterface $cache)
	{
		$this->request_cache = $cache;
	}
	
	/**
	 * Get the caching object used for requests
	 * 
	 * @return CacheInterface
	 *   The caching object used
	 */
	public function getRequestCache()
	{
		return $this->request_cache;
	}
	
	/**
	 * Set the caching object to use for tokens and roles
	 *
	 * The caching object will be used by the Actor class to cache tokens and roles.
	 * Any caching object used must implement the CacheInterface.
	 *
	 * The SDK provides the following caching classes:
	 * - NoopCache, which will not cache anything (default)
	 * - FileCache, which will cache to files on disk
	 * - MemCache, which will cache on a memcached server
	 * - MemoryCache, which will cache in memory
	 *
	 * @param CacheInterface $token_cache
	 *   The caching object to use
	 */
	public function setTokenCache(CacheInterface $token_cache)
	{
		$this->token_cache = $token_cache;
	}
	
	/**
	 * Get the caching object used for tokens and roles
	 *
	 * @return CacheInterface
	 *   The caching object used for tokens and roles
	 */
	public function getTokenCache()
	{
		return $this->token_cache;
	}
	
	/**
	 * Set whether to use the session as a caching object for tokens and roles if using a session
	 * 
	 * @param bool $use_session_for_token_cache
	 *   True if and only if the session should be used to cache tokens and roles
	 */
	public function setUseSessionForTokenCache($use_session_for_token_cache)
	{
		$this->use_session_for_token_cache = $use_session_for_token_cache;
	}
	
	/**
	 * Get whether to use the session as a caching object for tokens and roles if using a session
	 * 
	 * @return bool
	 *   True if and only if the session should be used to cache tokens and roles
	 */
	public function getUseSessionForTokenCache()
	{
		return $this->use_session_for_token_cache;
	}
	
	/**
	 * Set the session store to use
	 * 
	 * The session store will be used by default by Session to store information on the currently
	 * active session. Any session store used must implement the SessionStoreInterface.
	 * 
	 * The SDK provides the following session stores:
	 * - MemorySessionStore, which will only save the information in memory for the duration
	 *                       of the current script
	 * - PhpSessionStore, which will save the information in a PHP session (default)
	 * 
	 * @param SessionStoreInterface $session_store
	 *   The session store to use
	 */
	public function setSessionStore(SessionStoreInterface $session_store)
	{
		$this->session_store = $session_store;
	}
	
	/**
	 * Get the session store used
	 * 
	 * @return SessionStoreInterface
	 *   The session store used
	 */
	public function getSessionStore()
	{
		return $this->session_store;
	}
	
	
	/**
	 * Check whether this Config object can be used for performing Requests
	 * 
	 * This method checks the configuration to ensure that it is suitable to use for performing
	 * Requests to the API. To be suitable, the following conditions must be met
	 * 
	 * - Either user authentication or application authentication must be configured with
	 *   non-empty actor ID and pre-shared key
	 * 
	 * @return bool
	 *   True if and only if the Config object can be used for performing Requests
	 */
	public function validateForRequests()
	{
		return (
			in_array($this->getAuthenticationType(),
			         array(self::AUTH_USER, self::AUTH_APPLICATION)) &&
			(strlen($this->getAuthenticationActorId()) > 0) &&
			(strlen($this->getAuthenticationActorKey()) > 0)
		);
	}
}

/**
 * @}
 */
