<?php
/**
 * @addtogroup StreamOneSDK The StreamOne SDK
 * 
 * The StreamOne SDK contains various classes for communication with the StreamOne platform. All
 * classes work with configuration settings, which can be set using the Config class.
 * 
 * The central class in the SDK is the Platform class, which holds the active configuration
 * and can be used as a factory for the various operations to perform with the SDK.
 * 
 * @{
 */

namespace StreamOne\API\v3;

/**
 * A representation of the StreamOne platform used as a factory for various available operations
 * 
 * To work with the SDK, a Platform needs to be constructed with the correct configuration
 * parameters. This Platform can then be used to create various ways to work with the configured
 * platform, such as Requests or Sessions.
 */
class Platform
{
	/**
	 * @var Config $config
	 *   The configuration used for this platform
	 */
	private $config;
	
	/**
	 * Construct a new Platform
	 *
	 * @param array|Config $configuration
	 *   A key=>value array of configuration options. See the documentation of the Config
	 *   class for the available configuration options. Can also be a Config instance
	 *
	 * @throws \InvalidArgumentException
	 *   For the same reasons as Config::__construct().
	 */
	public function __construct($configuration)
	{
		if (!$configuration instanceof Config)
		{
			$configuration = new Config($configuration);
		}
		$this->config = $configuration;
	}
	
	/**
	 * Get the Config object used by this Platform instance
	 * 
	 * @return Config
	 *   The Config object used by this Platform instance
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * Create a new request to the API
	 * 
	 * @param string $command
	 *   The API command to call
	 * @param string $action
	 *   The action to perform on the API command
	 * 
	 * @return Request
	 *   A request to the given command and action
	 */
	public function newRequest($command, $action)
	{
		$request_factory = $this->config->getRequestFactory();
		return $request_factory->newRequest($command, $action, $this->config);
	}
	
	/**
	 * Create a Session object to work with API sessions
	 * 
	 * @param SessionStoreInterface $session_store
	 *   The session store to use for this session; if not given, use the one defined in
	 *   the configuration object
	 * 
	 * @return Session
	 *   The created session object
	 */
	public function newSession(SessionStoreInterface $session_store = null)
	{
		return new Session($this->config, $session_store);
	}
	
	/**
	 * Create an Actor object to perform requests as an actor
	 * 
	 * @param Session|null $session
	 *   If given, the actor will use this session to act upon (i.e. it will be a user
	 *   actor with the given user information); if not given, use actor information
	 *   from the configuration
	 * 
	 * @return Actor
	 *   The created actor object
	 */
	public function newActor(Session $session = null)
	{
		return new Actor($this->config, $session);
	}
}

/**
 * @}
 */
