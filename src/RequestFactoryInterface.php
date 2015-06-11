<?php

namespace StreamOne\API\v3;

/**
 * Interface definition for a request factory to instantiate different kinds of requests
 */
interface RequestFactoryInterface
{
	/**
	 * Instantiate a new request without a session
	 *
	 * @param string $command
	 *   The command to execute
	 * @param string $action
	 *   The action to execute
	 * @param Config $config
	 *   The Config object to use for the request
	 * @return Request
	 *   The instantiated request
	 */
	public function newRequest($command, $action, Config $config);
	
	/**
	 * Instantiate a new request within a session
	 *
	 * @param string $command
	 *   The command to execute
	 * @param string $action
	 *   The action to execute
	 * @param Config $config
	 *   The Config object to use for the request
	 * @param SessionStoreInterface $session_store
	 *   The session store containing the required session information
	 * @return SessionRequest
	 *   The instantiated request
	 */
	public function newSessionRequest($command, $action, Config $config,
	                                  SessionStoreInterface $session_store);
}
