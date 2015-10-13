<?php
namespace def\Validation;

class Validator
{
	protected $assertions = [];

	public function test($value)
	{
		foreach($this->assertions as $assertion) if(!$assertion($value)) {
			return false;
		}

		return true;
	}

	public static function __callStatic($method, array $args = [])
	{
		return (new static)->__call($method, $args);
	}

	public function __call($method, array $args = [])
	{
		$exists = \function_exists($assertion = __NAMESPACE__ . "\\$method") || \function_exists($assertion = $method);

		$not    = !$exists && 0 === \strpos($method, 'not_') && ($_method = \substr($method, 4));

		$not    = $not && (\function_exists($assertion = __NAMESPACE__ . "\\$_method") || \function_exists($assertion = $_method));

		$exists = $exists || $not;

		if(!$exists)
			throw new \BadMethodCallException("Undefined '$method' validation rule");

		$this->assertions[] = function($value) use($assertion, $args, $not) {
			return $assertion($value, ...$args) ? !$not : $not;
		};

		return $this;
	}

	public function assert(callable $assertion)
	{
		$validator = isset($this) ? $this : new static;
		$validator->assertions[] = $assertion;

		return $validator;
	}
}

// filter rules out of class scope

function match($value, $regexp)
{
	return (bool) \preg_match($regexp, $value);
}

function email($value)
{
	return false !== \filter_var($value, \FILTER_VALIDATE_EMAIL);
}

function equal($value, $control, $strict = false)
{
	return $strict ? $value === $control : $value == $control;
}

function min($value, $min)
{
	return $value >= $min;
}

function max($value, $max)
{
	return $value <= $max;
}

function between($value, $min, $max)
{
	return min($value, $min) && max($value, $max);
}

function count($array, $min = 0, $max = \PHP_INT_MAX)
{
	return is_array($array) && between(\count($array), $min, $max);
}

function length($value, $length)
{
	return \mb_strlen($value, \mb_detect_encoding($value)) == $length;
}

function minlength($value, $min)
{
	return \mb_strlen($value, \mb_detect_encoding($value)) >= $min;
}

function maxlength($value, $max)
{
	return \mb_strlen($value, \mb_detect_encoding($value)) <= $max;
}

function required($value)
{
	return !empty($value);
}

function positive($value)
{
	return $value > 0;
}

function negative($value)
{
	return $value < 0;
}

function json($value)
{
	\json_decode($value);
	return \JSON_ERROR_NONE === \json_last_error();
}
