<?php

namespace Plummer\Calendar;

class CalendarFactory
{
	public static function fromRegistry(CalendarAbstract $calendar, RegistryInterface $eventsRegistry, RegistryInterface $recurrencesRegistry)
	{
		return static::fromIterator($calendar, $eventsRegistry->getIterator(), $recurrencesRegistry->getIterator());
	}

	public static function fromIterator(CalendarAbstract $calendar, \Iterator $eventsIterator, \Iterator $recurrencesIterator)
	{
		$calendar->addEvents($eventsIterator);
		$calendar->addRecurrenceTypes($recurrencesIterator);

		return $calendar;
	}
}