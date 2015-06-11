<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * An actor corresponding to a user (with or without session) or application.
 *
 * Besides information about whether this actor is a user or application, one can also set an
 * account, multiple accounts or a customer for the actor
 */
class Actor
{
	/**
	 * @var Config $config
	 *   The configuration object to use for this Actor
	 */
	private $config;
	
	/**
	 * @var Session|null $session
	 *   The session object to use for this Actor; null if not using a session
	 */
	private $session;
	
	/**
	 * @var CacheInterface $token_cache
	 *   The cache to use for storing data about tokens and roles
	 */
	private $token_cache;
	
	/**
	 * @var string|null $customer
	 *   The customer to use for this Actor
	 */
	private $customer = null;
	
	/**
	 * @var array $accounts
	 *   The account(s) to use for this Actor
	 */
	private $accounts = array();

	/**
	 * Construct a new actor object
	 *
	 * @param Config $config
	 *   The configuration object to use for this actor
	 * @param Session|null $session
	 *   The session object to use for this actor; if null, it will use authentication information
	 *   from the configuration
	 */
	public function __construct(Config $config, Session $session = null)
	{
		$this->config = $config;
		$this->session = $session;
		
		if ($this->session !== null && $this->config->getUseSessionForTokenCache())
		{
			$this->token_cache = new SessionCache($this->session);
		}
		else
		{
			$this->token_cache = $this->config->getTokenCache();
		}

		if ($config->getDefaultAccountId() !== null)
		{
			$this->accounts = array($config->getDefaultAccountId());
		}
	}

	/**
	 * Get the config for this actor
	 * 
	 * @return Config
	 *   The config used for this actor
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Get the session used for this actor
	 * 
	 * @return Session|null
	 *   The session used for this actor; null if not using a session
	 */
	public function getSession()
	{
		return $this->session;
	}
	
	/**
	 * Get the token cache used for this actor
	 * 
	 * @return CacheInterface
	 *   The token cache used for this actor
	 */
	public function getTokenCache()
	{
		return $this->token_cache;
	}

	/**
	 * Set the account to use for this actor
	 *
	 * @param string|null $account
	 *   ID of the account to use for this actor; if null, clear account. Note that calling this
	 *   function will clear the customer, as it is not possible to have both at the same time
	 */
	public function setAccount($account)
	{
		if ($account === null)
		{
			$this->accounts = array();
		}
		else
		{
			$this->accounts = array($account);
		}
		$this->customer = null;
	}

	/**
	 * Get the account used for this actor
	 *
	 * @return string|null
	 *   ID of the account used for this actor; null if none. If more than one account has been set
	 *   (with setAccounts), the first one will be returned
	 */
	public function getAccount()
	{
		if (empty($this->accounts))
		{
			return null;
		}
		return $this->accounts[0];
	}

	/**
	 * Set the accounts to use for this actor
	 *
	 * @param array $accounts
	 *   Array with IDs of the accounts to use for this actor. Note that calling this
	 *   function will clear the customer, as it is not possible to have both at the same time
	 */
	public function setAccounts(array $accounts)
	{
		$this->accounts = $accounts;
		$this->customer = null;
	}

	/**
	 * Get the accounts used for this actor
	 *
	 * @return array
	 *   The IDs of the accounts used for this actor; empty array if none
	 */
	public function getAccounts()
	{
		return $this->accounts;
	}
	
	/**
	 * Whether this actor is for at least one account
	 *
	 * @return bool
	 *   True if and only if this actor is for at least one account
	 */
	protected function hasAccounts()
	{
		return !empty($this->accounts);
	}

	/**
	 * Set the customer to use for this actor
	 *
	 * @param string|null $customer
	 *   ID of the customer to use for this actor; if null, clear customer. Note that calling this
	 *   function will clear the account(s), as it is not possible to have both at the same time
	 */
	public function setCustomer($customer)
	{
		$this->customer = $customer;
		$this->accounts = array();
	}

	/**
	 * Get the customer used for this actor
	 *
	 * @return string|null
	 *   The ID of the customer used for this actor; null if none
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * Create a new request to the API for this actor
	 *
	 * @param string $command
	 *   The API command to call
	 * @param string $action
	 *   The action to perform on the API command
	 *
	 * @return Request
	 *   A request to the given command and action for the given actor
	 *
	 * @throws \LogicException
	 *   When a session is used for this actor and that session is not active
	 */
	public function newRequest($command, $action)
	{
		$request = $this->createCleanRequest($command, $action);
		
		if ($this->customer !== null)
		{
			$request->setCustomer($this->customer);
		}
		elseif (!empty($this->accounts))
		{
			$request->setAccounts($this->accounts);
		}
		else
		{
			// This call is done to overwrite the default account for the config, if it is set
			$request->setAccount(null);
		}

		return $request;
	}
	
	/**
	 * Check if this actor has a given token.
	 * 
	 * Tokens will be fetched from the StreamOne API
	 * 
	 * @param string $token
	 *   The token to check for
	 * @return bool
	 *   True if and only if the current actor has the given token
	 * @throws RequestException
	 *   If loading the roles from the API failed
	 */
	public function hasToken($token)
	{
		$roles = $this->getRoles();
		
		// Depending on the active customer or account(s) and the roles the active user /
		// application has, there are 3 possible ways to check whether the current actor has a token:
		// [1] The roles do not provide enough information and more specific tokens should be
		//     retrieved. See shouldCheckMyTokens() for more information when this is the case
		// [2] There are no active account(s), check global or customer-specific tokens
		// [3] There is at least one active account (but [1] does not hold, check account-specific
		//     tokens
		
		if ($this->shouldCheckMyTokens($roles))
		{
			return $this->checkMyTokens($token);
		}
		elseif (!$this->hasAccounts())
		{
			return $this->checkNonAccountHasToken($roles, $token);
		}
		else
		{
			return $this->checkAccountHasToken($roles, $token);
		}
	}
	
	/**
	 * Create a 'clean' request for a given command and action
	 * 
	 * This will make sure a session request is used if a session is active. No actor or customer
	 * will be set on this request, even if the actor does have them. 
	 *
	 * @param string $command
	 *   The API command to call
	 * @param string $action
	 *   The action to perform on the API command
	 *
	 * @return Request
	 *   A request to the given command and action
	 *
	 * @throws \LogicException
	 *   When a session is used for this actor and that session is not active
	 */
	protected function createCleanRequest($command, $action)
	{
		if ($this->session !== null)
		{
			return $this->session->newRequest($command, $action);
		}
		else
		{
			$request_factory = $this->config->getRequestFactory();
			return $request_factory->newRequest($command, $action, $this->config);
		}
	}
	
	/**
	 * Check whether the api/myroles action should be used to check for tokens
	 * 
	 * There might not be enough information yet in the list of roles for the current application or
	 * user to determine the tokens for this actor. This method detects in which cases a more
	 * specific list of tokens needs to be requested.
	 * 
	 * The main issue is that there is no mapping between accounts and customers. Consider a user
	 * that has a role in a specific account and an actor wants to do a request in an account.
	 * We then have no way to know if that account belongs to the customer, which we need to know
	 * to be able to get the tokens of that account (as these should include any tokens for the
	 * customer of that account). To remedy this, we can request the active tokens specifc for this
	 * case.
	 * 
	 * This method is used to detect this case.
	 * 
	 * @param array $roles
	 *   The roles as returned from the getmyroles API actions
	 * @return bool
	 *   True if and only if the api/myroles action should be checked for tokens
	 */
	protected function shouldCheckMyTokens($roles)
	{
		if ($this->hasAccounts())
		{
			foreach ($roles as $role)
			{
				if (isset($role['customer']))
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Use the tokens of the current actor to check if the current actor has the given token
	 * 
	 * This might request the tokens of the current actor from the API, if not cached
	 * 
	 * @param string $token
	 *   The token to check for
	 * @return bool
	 *   True if and only if the current actor has the given token
	 * @throws RequestException
	 *   If loading the tokens from the API failed
	 */
	protected function checkMyTokens($token)
	{
		$tokens = $this->getMyTokens();
		
		return in_array($token, $tokens);
	}
	
	/**
	 * Get the tokens for the current actor
	 * 
	 * @return array
	 *   The tokens for the current actor
	 * @throws RequestException
	 *   If loading the tokens from the API failed
	 */
	protected function getMyTokens()
	{
		$tokens = $this->loadMyTokensFromCache();
		if ($tokens === false)
		{
			$tokens = $this->loadMyTokensFromApi();
			$this->token_cache->set($this->tokensCacheKey(), $tokens);
		}
		return $tokens;
	}
	
	/**
	 * Load the tokens for the current actor from the cache
	 *
	 * @return array
	 *   The tokens for the current actor, loaded from the cache. If the cache does not contain the
	 *   roles false will be returned
	 */
	protected function loadMyTokensFromCache()
	{
		return $this->token_cache->get($this->tokensCacheKey());
	}
	
	/**
	 * Determine the key to use for caching tokens
	 *
	 * @return string
	 *   A cache-key used for tokens
	 */
	protected function tokensCacheKey()
	{
		return 's1:tokens:' . $this->config->getAuthenticationType() . ':' .
		       $this->config->getAuthenticationActorId() . ':' . $this->customer . ':' .
		       implode('|', $this->accounts);
	}
	
	/**
	 * Load the tokens for the current actor from the API
	 * 
	 * This will also store the tokens in the cache
	 * 
	 * @return array
	 *   The tokens for the current actor
	 * @throws RequestException
	 *   If loading the tokens from the API failed
	 */
	protected function loadMyTokensFromApi()
	{
		$request = $this->newRequest('api', 'mytokens');
		$request->execute();
		
		if (!$request->success())
		{
			throw RequestException::fromRequest($request);
		}
		
		return $request->body();
	}
	
	/**
	 * Check if an actor not having an account has a given token
	 *
	 * This function checks whether the token is available for an actor having a customer or a
	 * global actor
	 *
	 * @param array $roles
	 *   The roles as returned from the getmyroles API actions
	 * @param string $token
	 *   The token to check
	 * @return bool
	 *   True if and only if the current actor has the given token in any role, taking into account
	 *   customers
	 */
	protected function checkNonAccountHasToken($roles, $token)
	{
		foreach ($roles as $role)
		{
			if ($this->checkRoleForToken($role, $token, $this->customer, null))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Check if an actor having at least one account has a given token
	 *
	 * This function checks whether the token is available for all customers of the current actor
	 *
	 * @param array $roles
	 *   The roles as returned from the getmyroles API actions
	 * @param string $token
	 *   The token to check
	 * @return bool
	 *   True if and only if the current actor has the given token in any role, taking into account
	 *   customers and accounts
	 */
	protected function checkAccountHasToken($roles, $token)
	{
		$num_ok = 0;
		foreach ($this->accounts as $account)
		{
			foreach ($roles as $role)
			{
				if ($this->checkRoleForToken($role, $token, null, $account))
				{
					$num_ok++;
					break;
				}
			}
		}
		return $num_ok == count($this->accounts);
	}
	
	/**
	 * Check if a given role has a specific token in an account or customer
	 *
	 * @param array $role
	 *   The role as returned from the getmyroles API actions
	 * @param string $token
	 *   The token to check
	 * @param string|null $customer
	 *   The customer to use for checking
	 * @param string|null $account
	 *   The account to use for checking
	 * @return bool
	 *   True if and only if the given role has the given token and is a super-role if the given
	 *   customer and account
	 */
	protected function checkRoleForToken($role, $token, $customer, $account)
	{
		return ($this->roleIsSuperOf($role, $customer, $account) &&
			in_array($token, $role['role']['tokens']));
	}
	
	/**
	 * Get the roles for the current configuration and session
	 * 
	 * @return array
	 *   An array containing all the roles for the current configuration and session
	 * @throws RequestException
	 *   If loading the roles from the API failed
	 */
	protected function getRoles()
	{
		if ($this->session !== null || $this->config->getAuthenticationType() == Config::AUTH_USER)
		{
			$actor_type = 'user';
		}
		else
		{
			$actor_type = 'application';
		}
		
		$roles = $this->loadRolesFromCache($actor_type);
		
		if ($roles === false)
		{
			$roles = $this->loadRolesFromApi($actor_type);
			
			// Store it in the cache
			$this->token_cache->set($this->rolesCacheKey($actor_type), $roles);
		}
		
		return $roles;
	}
	
	/**
	 * Load the roles of the current configuration and session from the cache
	 *
	 * @param string $actor_type
	 *   The actor type to load roles for; either "user" or "application"
	 * @return array|bool
	 *   The roles for the current configuration and session, loaded from the cache. If the cache
	 *   does not contain the roles false will be returned
	 */
	protected function loadRolesFromCache($actor_type)
	{
		return $this->token_cache->get($this->rolesCacheKey($actor_type));
	}
	
	/**
	 * Determine the key to use for caching roles
	 *
	 * @param string $actor_type
	 *   The actor type to determine the cache key for; either "user" or "application"
	 * @return string
	 *   A cache-key used for roles
	 */
	protected function rolesCacheKey($actor_type)
	{
		return 's1:roles:' . $actor_type . ':' .
		       $this->config->getAuthenticationActorId();
	}
	
	/**
	 * Load the roles of the current configuration and session from the API
	 * 
	 * @param string $actor_type
	 *   The actor type to load roles for; either "user" or "application"
	 * @return array
	 *   The roles for the current configuration and session, loaded from the API
	 * @throws RequestException
	 *   If loading the roles from the API failed
	 */
	protected function loadRolesFromApi($actor_type)
	{
		$request = $this->newRequest($actor_type, 'getmyroles');
		$request->execute();
		
		if (!$request->success())
		{
			throw RequestException::fromRequest($request);
		}
		
		return $request->body();
	}
	
	/**
	 * Determine if a given role is a super-role of a given customer and/or account
	 * 
	 * A role is a super-role if:
	 * - It is a role without a customer or account
	 * - It is a role with a customer (and without an account) and the customer matches the given argument
	 * - It is a role with an account and the account matches the given argument
	 * 
	 * Note that there is a fourth case: if it is a role with a customer, an account is given in the
	 * arguments and that account belongs to the customer. This case is not handled here, as finding
	 * out if an account belongs to a customer can not be done.
	 * 
	 * @see shouldCheckMyTokens
	 * 
	 * @param array $role
	 *   The role as returned from the getmyroles API actions
	 * @param string|null $customer
	 *   The customer to check for or null if no customer
	 * @param string|null $account
	 *   The account to check for or null if no account
	 * @return bool
	 *   True if and only if the given role is a super-role of the given arguments
	 */
	protected function roleIsSuperOf($role, $customer, $account)
	{
		if (!isset($role['customer']) && !isset($role['account']))
		{
			return true;
		}
		
		if (!isset($role['account']) && $customer !== null && $role['customer']['id'] == $customer)
		{
			return true;
		}
		
		if (isset($role['account']) && $account !== null && $role['account']['id'] == $account)
		{
			return true;
		}
		
		return false;
	}
}

/**
 * @}
 */
