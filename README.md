# StreamOne PHP SDK version 3

This is the SDK that can be used to communicate with the StreamOne Platform by using the StreamOne API version 3.

## Table of contents

* [Requirements](#requirements)
* [Installation](#installation)
 * [Using composer](#using-composer)
 * [Manually](#manually)
* [Usage](#usage)
 * [Configuration](#configuration)
 * [Platform](#platform)
 * [Request](#request)
 * [Session](#session)
 * [Actor](#actor)
* [Other useful classes](#other-useful-classes)
* [Complete example](#complete-example)
* [License and copyright](#license-and-copyright)


## Requirements

The SDK requires PHP 5.4 or higher and the [PHP fopen wrappers](http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen) should be enabled.

## Installation

### Using composer

The recommended way to install the SDK is to use [Composer](http://getcomposer.org).
To install, add the following to your `composer.json` file:


```json
{
	"require": {
		"streamone/php-sdk-v3": "~3.2"
	}
}
```

Afterwards, you should update the package by running Composer in the directory where the `composer.json` file is located:

```bash
php composer.phar update streamone/php-sdk-v3
```

### Manually

You can download a ZIP of the latest release using the "tags" button at the top of the Github page.
Place the contents of the ZIP-file somewhere and you can use it.
Note that if you use this method you should `require` the files of the SDK yourself.

## Usage

To use the StreamOne SDK, you should first set up a configuration and afterwards you can start communicating with the StreamOne API.

### Configuration

To set up a configuration, you should initialize the `StreamOne\API\v3\Config` with the desired configuration:

```php
<?php

require_once('vendor/autoload.php');

use StreamOne\API\v3\Config;

$config = new Config(array(
	...
));
```

The following configuration options are available:

* **api_url** (required): this should be the base URL of the API to use. For example: `https://api.streamonecloud.net`.
* **authentication_type** (required): this should be either `user` or `application` and denotes the type of authentication to use.
* **user_id** and **user_psk** (required if **authentication_type** is `user`): these should contain the ID and preshared key of the user to use for authentication.
* **application_id** and **application_psk** (required if **authentication_type** is `application`): these should contain the ID and preshared key of the application to use for authentication.
* **default_account_id** (optional): this can be set to the ID of an account and if set, this will be the account to use by default for all API actions.
* **visible_errors** (optional, defaults to `[2,3,4,5,7]`): a list of all error codes to display prominently. All possible errors are defined in `Status.php`.
* **request_factory** (optional, defaults to a `StreamOne\API\v3\RequestFactory`): factory to use for creating requests. If you want to overwrite it you can pass an implementation of `StreamOne\API\v3\RequestFactoryInterface` here.
* **cache** (optional, defaults to a `StreamOne\API\v3\NoopCache`): cache to use for both requests and tokens. Should be an implementation of `StreamOne\API\v3\CacheInterface`.
* **request_cache** (optional, defaults to a `StreamOne\API\v3\NoopCache`): cache to use for requests. Overwrites anything set for **cache** and should also be an implementation of `StreamOne\API\v3\CacheInterface`.
* **token_cache** (optional, defaults to a `StreamOne\API\v3\NoopCache`): cache to use for tokens. Overwrites anything set for **cache** and should also be an implementation of `StreamOne\API\v3\CacheInterface`.
* **use_session_for_token_cache** (optional, defaults to `true`): if `true`, the session will be used to store token information if using a session. Otherwise the **token_cache** will always be used.
* **session_store** (optional, defaults to a `StreamOne\API\v3\PhpSessionStore`): the session store to use to store session information and optionally token information (if **use_session_for_token_cache** is set to `true`).

Note that for **request_factory**, **cache**, **request_cache**, **token_cache** and **session_store** you can either pass an instance of an object implementing the required interface or an array of values where the first element should be the full class name (including namespace) and the other arguments will be passed to the constructor of that class.

### Platform

The Platform class is the main entry point for performing requests. You pass it the Config during creation and it allows you to perform requests, start a new session or create an actor.

Example:

```php
<?php

use StreamOne\API\v3\Platform;

$config = ... // As above

$platform = new Platform($config);

// Start a new request
$request = $platform->newRequest('api', 'info');

// Or use a session
$session = $platform->newSession(); // You can optionally pass a different session store here

// Or create an actor
$actor = $platform->newActor(); // You can pass a session here to use that session for this actor
```

### Request

A Request can be used to perform an actual request to the StreamOne API. It extends `RequestBase` which contains code that should be used by other request classes.

The following actions can be done using a request:

* Set an account: use `setAccount($account)` to use an account for this request. By default the **default_account** from the Config will be used, if set.
* Set multiple accounts: use `setAccounts(array $accounts)` to set multiple accounts for this request. Some API actions allow you to provide more than one account.
* Set a customer: use `setCustomer($customer)` to use a customer instead of an account for this request. API actions supporting multiple accounts or a customer can use this.
* Set the timezone using `setTimeZone(DateTimeZone $timezone)`. If not set the default timezone of the current actor will be used, but one might want to overwrite this.
* Set an argument by using `setArgument($key, $value)`: most API actions allow and / or require arguments to be set. Use this function to provide them.

After setting up a request you should call `execute()` to actually connect to the API and perform the request.
After doing so, the following information is available in the request:

* `valid()`: true if and only if the API request connected to the API successfully and contains valid data.
* `status()`: the status code of the API response. Normally `0` means OK.
* `statusMessage()`: the (textual) status message of the API response.
* `success()`: true if and only if `valid()` returns `true` and `status()` returns `0`.
* `header()`: the complete header of the API response. `null` if `valid()` returns `false`.
* `body()`: the complete body of the API response. `null` if `valid()` returns `false`.

We provide a `StreamOne\API\v3\RequestException` class that can be used to throw an exception when the API request fails.

An example API request:

```php
<?php

use StreamOne\API\v3\RequestException;

$platform = ... // As above

$request = $platform->newRequest('item', 'view');
$request
	->setArgument('itemtype', 'video')
	->execute();

if ($request->success())
{
	foreach ($request->body() as $item)
	{
		// Do something with $item
	}
}
else
{
	throw RequestException::fromRequest($request);
}

```

### Session

A Session can be used in the StreamOne platform by an application to perform API actions on behalf of a user.

To use a session, you need to authenticate as an application (by setting **authentication_type** to `application`).
Then you can use the `StreamOne\API\v3\Session` class to start a session and to perform actions using that session.

The Session class provides the following useful methods:

* `isActive()`: returns `true` if and only if a session is active, i.e. the user is currently logged in.
* `start($username, $password, $ip)`: start a new session for the user with the given username and password.
  `$ip` should be set to the IP address of the client that wants to log in. This makes sure the API can perform rate limiting when someone fails to log in too many times without succeeding.
* `end()` can be used to end the currently active session.
* `newRequest()` can be used to perform a request on behalf of the user for this session.
* `getUserId()` can be used to get the ID of the user that is currently logged in.

An example of using a session:

```php
<?php

use StreamOne\API\v3\RequestException;

$platform = ... // As above

$session = $platform->newSession();

if (!$session->isActive())
{
	$session->start('username', 'password', '10.11.12.13');
}

$request = $session->newRequest('item', 'view');
// etc
```

### Actor

An Actor corresponds to a user or an application.
It can be used to perform multiple requests with the same settings, like accounts and / or customer.

Actors can also be used to check if the required tokens for an API action are available for the given actor.
The system will request tokens from the API when required and it will cache this information so this is not done for every request. The **token_cache** from the Config will be used to store this information.

An example of using an actor:

```php
<?php

$platform = ... // As above

$actor = $platform->newActor();
// or
$session = ... // As above
$actor = $platform->newActor($session);

$actor->setAccount('abcdef');

if ($actor->hasToken('item', 'view'))
{
	$request = $actor->newRequest('item', 'view');
	// etc
}
```

## Other useful classes

There are more classes available in the StreamOne SDK:

* `FileCache`, `MemCache`, `MemoryCache`, `NoopCache` and `SessionCache`: different cache classes storing the cache in a file, memcached, the memory, nowhere and in the current session respectively.
* `MemorySessionStore` and `PhpSessionStore`: different session stores that stores session information in memory and in the PHP session respectively.
* `Password` is used when logging in using a session and can also be used when changing the password of a user.
* `PersistentActor` saves all actor related information in the current session. This is useful if you want your application to remember the account / customer settings for an actor.
* `Status` contains constants for all statuses that the API can report.

## Complete example

```php
<?php

use StreamOne\API\v3\Config;
use StreamOne\API\v3\Platform;
use StreamOne\API\v3\RequestException;

require_once('vendor/autoload.php');

$config = new Config(array(
	'api_url' => 'https://api.streamonecloud.net',
	'authentication_type' => 'user',
	'user_id' => 'abcdefghijkl',
	'user_psk' => 'abcdefghijklmnopqrstuvwxyzABCDEF',
	'default_account_id' => 'mnopqrstuvwx',
));

$request = $platform->newRequest('api', 'info');

$request->execute();

if ($request->success())
{
	var_dump($request->body());
}
else
{
	throw RequestException::fromRequest($request);
}

```

## License and copyright

All source code is licensed under the [MIT License](LICENSE).

Copyright (c) 2014-2015 [StreamOne B.V.](http://streamone.nl)
