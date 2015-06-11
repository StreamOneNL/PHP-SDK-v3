<?php

require_once('CacheInterfaceTest.php');

use StreamOne\API\v3\FileCache;

/**
 * Test the FileCache
 */
class FileCacheTest extends CacheInterfaceTest
{
	protected function constructCache()
	{
		return new FileCache('/tmp/s1_cache', 3);
	}
}
