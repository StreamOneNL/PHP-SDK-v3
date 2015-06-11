<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * Manage a session for use with the StreamOne platform
 */
class Session
{
	/**
	 * @var Config $config
	 *   The configuration object to use for this Session
	 */
	private $config;
	
	/**
	 * @var SessionStoreInterface $session_store
	 *   The session store to use for this session
	 */
	private $session_store;
	
	/**
	 * @var Request $start_request
	 *   The last request executed by the start() method
	 */
	private $start_request = null;
	
	/**
	 * Construct a new session object
	 * 
	 * The session object may or may not have an active session, depending on what is stored
	 * in the passed session store object.
	 * 
	 * @param Config $config
	 *   The configuration object to use for this session
	 * @param SessionStoreInterface $session_store
	 *   The session store to use for this session; if not given, use the one defined in the
	 *   given configuration object
	 */
	public function __construct(Config $config, SessionStoreInterface $session_store = null)
	{
		$this->config = $config;
		
		if ($session_store === null)
		{
			$this->session_store = $config->getSessionStore();
		}
		else
		{
			$this->session_store = $session_store;
		}
	}
	
	/**
	 * Retrieve the configuration used in this session
	 *
	 * @return Config
	 *   The configuration used in this session
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * Retrieve the session store used in this session
	 * 
	 * @return SessionStoreInterface
	 *   The session store used in this session
	 */
	public function getSessionStore()
	{
		return $this->session_store;
	}
	
	/**
	 * Check whether there is an active session
	 * 
	 * If there is no active session, it is only possible to start a new session.
	 * 
	 * @return bool
	 *   True if and only if there is an active session
	 */
	public function isActive()
	{
		return $this->session_store->hasSession();
	}

	/**
	 * Create a new session with the StreamOne API.
	 * 
	 * To start a new session provide the username, password, and IP address of the user
	 * requesting the new session. The IP address is required for rate limiting purposes.
	 * 
	 * @param string $username
	 *   The username to use for this session
	 * @param string $password
	 *   The password to use for this session
	 * @param string $ip
	 *   The IP address of the user creating the session
	 *
	 * @return bool
	 *   Whether the session has been started succesfully; if the session was not created
	 *   successfully, 
	 */
	public function start($username, $password, $ip)
	{
		// Initialize session to obtain challenge from API
		$request_factory = $this->config->getRequestFactory();
		$request = $request_factory->newRequest('session', 'initialize', $this->config);
		$request->setArgument('user', $username);
		$request->setArgument('userip', $ip);
		$request->execute();
		
		$this->saveStartRequest($request);

		if (!$request->success())
		{
			return false;
		}

		$request_body = $request->body();
		$needs_v2_hash = $request_body['needsv2hash'];
		$salt = $request_body['salt'];
		$challenge = $request_body['challenge'];
		
		$response = Password::generatePasswordResponse($password, $salt, $challenge);

		// Initializing session was OK, try to start it
		$request = $request_factory->newRequest('session', 'create', $this->config);
		$request->setArgument('challenge', $challenge);
		$request->setArgument('response', $response);
		if ($needs_v2_hash)
		{
			$v2_hash = Password::generateV2PasswordHash($password);
			$request->setArgument('v2hash', $v2_hash);
		}
		$request->execute();

		$this->saveStartRequest($request);
		
		if (!$request->success())
		{
			return false;
		}

		$request_body = $request->body();

		$this->session_store->setSession($request_body['id'], $request_body['key'],
		                                 $request_body['user'], $request_body['timeout']);

		return true;
	}
	
	/**
	 * Save the request used in start() for later inspection by startStatus/startStatusMessage
	 * 
	 * @param Request $request
	 *   The request to save for later inspection
	 */
	protected function saveStartRequest(Request $request)
	{
		$this->start_request = $request;
	}
	
	/**
	 * The status of the last request of the last call to start()
	 * 
	 * Depending on what exactly went wrong, this can be either the session/initialize or the
	 * session/create API-call.
	 * 
	 * This method should not be called before start() is called.
	 * 
	 * @return int
	 *   The status of the last request of the last call to start(), or null if the
	 *   reponse was invalid
	 * 
	 * @throw \LogicException
	 *   The start() method has not been called on this instance
	 */
	public function startStatus()
	{
		if ($this->start_request === null)
		{
			throw new \LogicException('The start() method has not been called on this instance');
		}
		
		if (!$this->start_request->valid())
		{
			return null;
		}
		
		return $this->start_request->status();
	}
	
	
	/**
	 * The status message of the last request of the last call to start()
	 * 
	 * Depending on what exactly went wrong, this can be either the session/initialize or the
	 * session/create API-call.
	 * 
	 * This method should not be called before start() is called.
	 * 
	 * @return string
	 *   The status message of the last request of the last call to start(), or null if the
	 *   reponse was invalid
	 * 
	 * @throw \LogicException
	 *   The start() method has not been called on this instance
	 */
	public function startStatusMessage()
	{
		if ($this->start_request === null)
		{
			throw new \LogicException('The start() method has not been called on this instance');
		}
		
		if (!$this->start_request->valid())
		{
			return null;
		}
		
		return $this->start_request->statusMessage();
	}
	
	/**
	 * End the currently active session; i.e. log out the user
	 * 
	 * This method should only be called with an active session.
	 * 
	 * @throw \LogicException
	 *   There is currently no active session
	 * 
	 * @return bool
	 *   True if and only if the session was successfully deleted in the API. If not, the session
	 *   is still cleared from the session store and the session is therefore always inactive
	 *   after this method returns.
	 */
	public function end()
	{
		if (!$this->isActive())
		{
			throw new \LogicException("No active session");
		}

		$request = $this->newRequest('session', 'delete');
		$request->execute();
		
		$this->session_store->clearSession();
		
		return $request->success();
	}

	/**
	 * Create a new request that uses the currently active session
	 * 
	 * This method should only be called with an active session.
	 * 
	 * @param string $command
	 *   The command for the new request
	 * @param string $action
	 *   The action for the new request
	 * @return SessionRequest
	 *   The new request using the currently active session for authentication
	 * 
	 * @throws \LogicException
	 *   When no session is active
	 */
	public function newRequest($command, $action)
	{
		if (!$this->isActive())
		{
			throw new \LogicException("No active session");
		}
		
		$request_factory = $this->config->getRequestFactory();
		return $request_factory->newSessionRequest($command, $action, $this->config,
		                                           $this->session_store);
	}

	/**
	 * Retrieve the ID of the user currently logged in with this session
	 * 
	 * This method can only be called if there is an active session.
	 * 
	 * @return string
	 *   The ID of the user currently logged in with this session
	 * 
	 * @throw \LogicException
	 *   There is no currently active session
	 */

	public function getUserId()
	{
		if (!$this->isActive())
		{
			throw new \LogicException("No active session");
		}
		
		return $this->session_store->getUserId();
	}
}

/**
 * @}
 */
