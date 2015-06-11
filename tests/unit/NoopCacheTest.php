<?php

require_once('CacheInterfaceTest.php');

use StreamOne\API\v3\NoopCache;

/**
 * Test the NoopCache
 */
class NoopCacheTest extends CacheInterfaceTest
{
	protected function constructCache()
	{
		return new NoopCache;
	}
}
