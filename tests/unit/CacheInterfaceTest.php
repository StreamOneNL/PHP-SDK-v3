<?php

use StreamOne\API\v3\CacheInterface;

/**
 * Abstract class providing tests for a class implementing CacheInterface
 */
abstract class CacheInterfaceTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Construct a cache to test
	 * 
	 * @return CacheInterface
	 *   An instantiated cache to run the tests on
	 */
	abstract protected function constructCache();
	
	/**
	 * Test setting and getting a key
	 * 
	 * @param string $key
	 *   The key to set
	 * @param mixed $value
	 *   The value to set for the key
	 * @param CacheInterface $cache
	 *   Cache object to use for this test; if not given, a new one is constructed
	 * 
	 * @dataProvider provideSetGet
	 */
	public function testSetGet($key, $value, CacheInterface $cache = null)
	{
		if ($cache === null)
		{
			$cache = $this->constructCache();
		}
		
		// Store time before setting the key
		$set_time = microtime(true);
		
		// Set the key
		$cache->set($key, $value);
		
		// If the key can be retrieved, test whether it is retrieved correctly
		$get_val = $cache->get($key);
		$get_age = $cache->age($key);
		
		// Store time after getting the key
		$get_time = microtime(true);
		
		if ($get_age === false)
		{
			// Age not found; key must be not found as well
			$this->assertSame(false, $get_val);
		}
		else
		{
			// Age found; value must be equal to set value, and age must be sensible
			$this->assertSame($value, $get_val);
			
			// Ceil and +1 to avoid any possibility for rounding issues with integer times
			$max_age = ceil($get_time - $set_time) + 1;
			$this->assertLessThanOrEqual($max_age, $get_age);
		}
	}
	
	public function provideSetGet()
	{
		return array(
			array('test-str', 'string'),
			array('test-int', 5),
			array('test-bool', true),
			array('test-float', 3.14159),
			array('test-array', array(1,1,2,3,5,8,13,21)),
			array('test-hash', array('a' => 'val', 'foo' => 'bar'))
		);
	}
	
	/**
	 * Test overwriting the value of a key
	 * 
	 * @param string $key
	 *   The key to test
	 * @param string $value1
	 *   Initial value to set
	 * @param string $value2
	 *   New value to set; not equal to $value1
	 * 
	 * @dataProvider provideChange
	 */
	public function testChange($key, $value1, $value2)
	{
		$cache = $this->constructCache();
		
		$this->testSetGet($key, $value1, $cache);
		$this->testSetGet($key, $value2, $cache);
	}
	
	public function provideChange()
	{
		return array(
			array('test-str', 'string', 'newstring'),
			array('test-int', 5, -7),
			array('test-bool', true, false),
			array('test-float', 3.14159, 1.41),
			array('test-array', array(1,1,2,3,5,8,13,21), array('foo', 'bar')),
			array('test-hash', array('a' => 'val', 'foo' => 'bar'), array('c' => 'test')),
			array('test-s-i', 'string', 5),
			array('test-b-f', true, 3.14159),
			array('test-a-h', array(1,2,3), array('foo' => 'bar'))
		);
	}
}
