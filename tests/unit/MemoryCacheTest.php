<?php

require_once('CacheInterfaceTest.php');

use StreamOne\API\v3\MemoryCache;

/**
 * Test the MemoryCache
 */
class MemoryCacheTest extends CacheInterfaceTest
{
	protected function constructCache()
	{
		return new MemoryCache;
	}
}
