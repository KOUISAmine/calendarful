<?php

namespace Plummer\Calendar;

abstract class RegistryAbstract implements RegistryInterface
{
	public function getIterator()
	{
		return new \ArrayIterator($this->getAll());
	}

	abstract public function set($key, $value);

	abstract public function get($key);

	abstract public function getAll();
}