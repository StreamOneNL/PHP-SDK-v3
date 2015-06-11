<?php

/**
 * A PHPUnit constraint that is met if an array has a certain key with a certain value
 */
class PHPUnit_Constraint_ArrayKeySameValue extends PHPUnit_Framework_Constraint
{
	/**
	 * The array key to check
	 * 
	 * @var integer|string
	 */
	private $key;
	
	/**
	 * The value that the array key must have
	 * 
	 * @var mixed
	 */
	private $value;
	
	/**
	 * Construct a new constraint
	 * 
	 * @param integer|string $key
	 *   The array key to check
	 * @param mixed $value
	 *   The value that the array key must have
	 */
	public function __construct($key, $value)
	{
		parent::__construct();
		
		$this->key = $key;
		$this->value = $value;
	}
	
	/**
	 * Evaluate the constraint
	 * 
	 * @param mixed $other
	 *   The array to match
	 * @return bool
	 *   True if and only if the constraint is met
	 */
	protected function matches($other)
	{
		if (is_array($other))
		{
			return (array_key_exists($this->key, $other) &&
			        ($other[$this->key] === $this->value));
		}
		
		if ($other instanceof ArrayAccess)
		{
			return ($other->offsetExists($this->key) &&
			        ($other->offsetGet($this->key) === $this->value));
		}
		
		return false;
	}
	
	/**
	 * Returns a string representation of the constraint
	 * 
	 * @return string
	 *   A string representation of the constraint
	 */
	public function toString()
	{
		return 'has the key ' . $this->exporter->export($this->key) .
		       ' with value ' . $this->exporter->export($this->value);
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
