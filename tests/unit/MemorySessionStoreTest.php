<?php

require_once('SessionStoreInterfaceTest.php');

use StreamOne\API\v3\MemorySessionStore;

/**
 * Test the MemorySessionStore
 */
class MemorySessionStoreTest extends SessionStoreInterfaceTest
{
	protected function constructSessionStore()
	{
		return new MemorySessionStore;
	}
}
