<?php

/**
 * A PHPUnit constraint that is met if an array does not contain a certain key
 */
class PHPUnit_Constraint_ArrayKeyDoesNotExist extends PHPUnit_Framework_Constraint
{
	/**
	 * The array key to check
	 *
	 * @var integer|string
	 */
	private $key;

	/**
	 * Construct a new constraint
	 *
	 * @param integer|string $key
	 *   The array key to check
	 */
	public function __construct($key)
	{
		parent::__construct();

		$this->key = $key;
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
			return !array_key_exists($this->key, $other);
		}

		if ($other instanceof ArrayAccess)
		{
			return !$other->offsetExists($this->key);
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
		return 'does not have the key ' . $this->exporter->export($this->key);
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
