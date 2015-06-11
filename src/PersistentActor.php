<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * A persistent actor is an actor that will be persisted in the current session
 *
 * It is thus only possible to create a persistent actor with a session
 */
class PersistentActor extends Actor {
	
	/**
	 * Key used to store accounts for this actor in the session
	 */
	const ACCOUNTS_CACHE_KEY = 's1:persistentactor:accounts';
	
	/**
	 * Key used to store customer for this actor in the session
	 */
	const CUSTOMER_CACHE_KEY = 's1:persistentactor:customer';
	
	/**
	 * @var Session $session
	 *   The session object to use for this PersistentActor
	 */
	private $session;
	
	/**
	 * Construct a new persistent actor object
	 *
	 * If actor information can be found in the current session, it will be loaded. Otherwise, a new
	 * actor will be created. The config of the session will be used for the actor
	 *
	 * @param Session $session
	 *   The session object to use for this persistent actor
	 */
	public function __construct(Session $session)
	{
		parent::__construct($session->getConfig(), $session);
		$this->session = $session;
		
		$this->loadFromSession();
	}
	
	/**
	 * {@inheritDoc}
	 * 
	 * Will also persist the account in the current session
	 */
	public function setAccount($account)
	{
		parent::setAccount($account);
		$this->persistToSession();
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * Will also persist the accounts in the current session
	 */
	public function setAccounts(array $accounts)
	{
		parent::setAccounts($accounts);
		$this->persistToSession();
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * Will also persist the customer in the current session
	 */
	public function setCustomer($customer)
	{
		parent::setCustomer($customer);
		$this->persistToSession();
	}
	
	/**
	 * Load actor data from the current session
	 * 
	 * If actor data is available from the current session (i.e. accounts or customer), it will be
	 * set on this actor
	 */
	protected function loadFromSession()
	{
		if ($this->session->getSessionStore()->hasCacheKey(self::ACCOUNTS_CACHE_KEY))
		{
			$this->setAccounts($this->session->getSessionStore()
			                                 ->getCacheKey(self::ACCOUNTS_CACHE_KEY));
		}
		elseif ($this->session->getSessionStore()->hasCacheKey(self::CUSTOMER_CACHE_KEY))
		{
			$this->setCustomer($this->session->getSessionStore()
			                                 ->getCacheKey(self::CUSTOMER_CACHE_KEY));
		}
	}
	
	/**
	 * Persist this actor to the current session
	 * 
	 * This will save the current account(s) and customer, so it can be loaded again
	 */
	protected function persistToSession()
	{
		if (count($this->getAccounts()) > 0)
		{
			$this->session->getSessionStore()
			              ->setCacheKey(self::ACCOUNTS_CACHE_KEY, $this->getAccounts());
		}
		else
		{
			$this->session->getSessionStore()->unsetCacheKey(self::ACCOUNTS_CACHE_KEY);
		}
		
		if ($this->getCustomer() !== null)
		{
			$this->session->getSessionStore()
			              ->setCacheKey(self::CUSTOMER_CACHE_KEY, $this->getCustomer());
		}
		else
		{
			$this->session->getSessionStore()->unsetCacheKey(self::CUSTOMER_CACHE_KEY);
		}
	}
}

/**
 * @}
 */
