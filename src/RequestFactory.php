<?php

namespace StreamOne\API\v3;

/**
 * Default request factory for the StreamOne PHP SDK
 */
class RequestFactory implements RequestFactoryInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function newRequest($command, $action, Config $config)
	{
		return new Request($command, $action, $config);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function newSessionRequest($command, $action, Config $config,
	                                  SessionStoreInterface $session_store)
	{
		return new SessionRequest($command, $action, $config, $session_store);
	}
}
