<?php

require_once('SessionStoreInterfaceTest.php');

use StreamOne\API\v3\PhpSessionStore;

/**
 * Test the PhpSessionStore
 */
class PhpSessionStoreTest extends SessionStoreInterfaceTest
{
	protected function constructSessionStore()
	{
		return new PhpSessionStore;
	}
}
