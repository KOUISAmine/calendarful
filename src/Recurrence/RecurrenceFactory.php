<?php

namespace Plummer\Calendarful\Recurrence;

use \Plummer\Calendarful\RegistryInterface as RegistryInterface;

class RecurrenceFactory implements RecurrenceFactoryInterface
{
	private $factories = [];

	public function fromRegistry(RegistryInterface $recurrenceRegistry)
	{

	}

	public function addRecurrenceFactory(RecurrenceInterface $recurrenceFactory)
	{
		$this->factories[$recurrenceFactory->getLabel()] = $recurrenceFactory;
	}

	public function getFactories()
	{
		return $this->factories;
	}

	public function createFactory($type)
	{
		if(!isset($this->factories[$type])) {
			throw new \Exception('The type passed does not exist.');
		}

		return $this->factories[$type];
	}
}