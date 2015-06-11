<?php

/**
 * Base class for PHPUnit tests with custom asserts
 */
class PHPUnit_TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * Assert that an array has a specified key with an exact specified value
	 * 
	 * @param integer|string $key
	 *   Key to check
	 * @param mixed $value
	 *   Value to check
	 * @param array $array
	 *   Array which must have the key with the given value
	 * @param string $message
	 *   Optional message
	 */
	public static function assertArrayKeySameValue($key, $value, $array, $message = '')
	{
		if (!(is_integer($key) || is_string($key)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				1, 'integer or string'
			);
		}
		
		// Value argument can be anything
		
		if (!(is_array($array) || ($array instanceof ArrayAccess)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				3, 'array or ArrayAccess'
			);
		}
		
		$constraint = new PHPUnit_Constraint_ArrayKeySameValue($key, $value);
		
		self::assertThat($array, $constraint, $message);
	}

	/**
	 * Assert that an array does not have a specified key
	 *
	 * @param integer|string $key
	 *   Key to check
	 * @param array $array
	 *   Array which must not have the given key
	 * @param string $message
	 *   Optional message
	 */
	public static function assertArrayKeyDoesNotExist($key, $array, $message = '')
	{
		if (!(is_integer($key) || is_string($key)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				1, 'integer or string'
			);
		}

		if (!(is_array($array) || ($array instanceof ArrayAccess)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				2, 'array or ArrayAccess'
			);
		}

		$constraint = new PHPUnit_Constraint_ArrayKeyDoesNotExist($key);

		self::assertThat($array, $constraint, $message);
	}
	
	/**
	 * Assert than an array is the same as a different array, excluding element ordering
	 * 
	 * @param array $expected
	 *   The expected values in the array
	 * @param array $actual
	 *   The actual values in the array
	 * @param string $message
	 *   Optional message
	 */
	public static function assertArraysSameUnordered($expected, $actual, $message = '')
	{
		if (!(is_array($expected) || ($expected instanceof ArrayAccess)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				1, 'array or ArrayAccess'
			);
		}
		
		if (!(is_array($actual) || ($actual instanceof ArrayAccess)))
		{
			throw PHPUnit_Util_InvalidArgumentHelper::factory(
				2, 'array or ArrayAccess'
			);
		}
		
		$constraint = new PHPUnit_Constraint_ArraysSameUnordered($expected);
		
		self::assertThat($actual, $constraint, $message);
	}
}
