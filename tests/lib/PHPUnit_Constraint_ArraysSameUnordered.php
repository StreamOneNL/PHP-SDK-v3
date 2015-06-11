<?php

/**
 * A PHPUnit constraint that is met if two arrays have the same elements
 * 
 * More detailed: the constraint is met if both arrays have exactly the same keys with exactly
 * the same values, regardless of the order of the elements in the array
 */
class PHPUnit_Constraint_ArraysSameUnordered extends PHPUnit_Framework_Constraint
{
	/**
	 * The array to match
	 * 
	 * @var array
	 */
	private $array;
	
	/**
	 * Construct a new constraint
	 * 
	 * @param array $array
	 *   The array to match
	 */
	public function __construct(array $array)
	{
		parent::__construct();
		
		$this->array = $array;
	}
	
	/**
	 * Evaluate the constraint
	 * 
	 * @param mixed $other
	 *   Other array to match
	 * @return bool
	 *   True if and only if the constraint is met
	 */
	protected function matches($other)
	{
		if (!is_array($other))
		{
			return false;
		}
		
		// Compare arrays both ways
		return ($this->isSubsetOf($other, $this->array) && $this->isSubsetOf($this->array, $other));
	}
	
	/**
	 * Check if the first array is a subset of the second array
	 * 
	 * @param array $sub
	 *   Array which must be a subset
	 * @param array $super
	 *   Array which must be a superset
	 * @return bool
	 *   True if and only $sub is a subset of $super
	 */
	protected function isSubsetOf(array $sub, array $super)
	{
		foreach ($sub as $key => $value)
		{
			// Check if this key from sub exists in super and has the same value
			if (!array_key_exists($key, $super) || ($super[$key] !== $sub[$key]))
			{
				return false;
			}
		}
		
		// All keys in sub exist in super and have the same value; succeed
		return true;
	}
	
	/**
	 * Returns a string representation of the constraint
	 * 
	 * @return string
	 *   A string representation of the constraint
	 */
	public function toString()
	{
		return 'is the same (unordered) as ' . $this->exporter->export($this->array);
	}
	
	/**
	 * Returns the description of the failure
	 * 
	 * @param mixed $other
	 *   Evaluated value or object
	 * @return string
	 *   The description of the failure
	 */
	protected function failureDescription($other)
	{
		return 'an array ' . $this->toString();
	}
}
