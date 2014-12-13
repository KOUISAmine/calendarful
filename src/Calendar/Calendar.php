<?php

namespace Plummer\Calendarful\Calendar;

use Plummer\Calendarful\Recurrence\RecurrenceFactoryInterface;
use Plummer\Calendarful\RegistryInterface;

class Calendar implements CalendarInterface, \IteratorAggregate
{
	protected $events;

	protected $recurrenceFactory;

	public function __construct(RecurrenceFactoryInterface $recurrenceFactory = null)
	{
		$this->recurrenceFactory = $recurrenceFactory;
	}

	public static function create(RecurrenceFactoryInterface $recurrenceFactory = null)
	{
		return new static($recurrenceFactory);
	}

	public function getIterator()
	{
		if($this->events === null) {
			throw new \Exception('This calendar needs to be populated with events.');
		}

		return new \ArrayIterator($this->events);
	}

	public function populate(RegistryInterface $eventsRegistry, \DateTime $fromDate, \DateTime $toDate, $limit = null, Array $extraFilters = array())
	{
		$filters = array_merge(
			[
				'fromDate' => $fromDate,
				'toDate' => $toDate,
				'limit' => $limit
			],
			$extraFilters
		);

		$this->events = $eventsRegistry->get($filters);

		$this->processRecurringEvents($fromDate, $toDate, $limit);

		$this->removeOveriddenEvents();

		$this->removeOutOfRangeEvents($fromDate, $toDate);

		$this->events = $limit ? array_slice(array_values($this->events), 0, $limit) : array_values($this->events);

		return $this;
	}

	public function sort()
	{
		usort($this->events, function($event1, $event2) {
			if($event1->getStartDate() == $event2->getStartDate()) {
				return $event1->getId() < $event2->getId() ? -1 : 1;
			}
			return $event1->getStartDate() < $event2->getStartDate() ? -1 : 1;
		});

		return $this;
	}

	public function count()
	{
		return count($this->events);
	}

	protected function processRecurringEvents(\DateTime $fromDate, \DateTime $toDate, $limit = null)
	{
		if($this->recurrenceFactory) {
			foreach($this->recurrenceFactory->getRecurrenceTypes() as $label => $recurrence) {
				$recurrenceType = new $recurrence();

				$occurrences = $recurrenceType->generateOccurrences($this->events, $fromDate, $toDate, $limit);

				$this->events = array_merge($this->events, $occurrences);
			}
		}

		// Remove recurring events that do not occur within the date range
		$this->events = array_filter($this->events, function($event) use ($fromDate, $toDate) {

			if(! $event->getRecurrenceType()) {
				return true;
			}
			else if($event->getStartDate() <= $toDate->format('Y-m-d H:i:s') && $event->getEndDate() >= $fromDate->format('Y-m-d H:i:s')) {
				return true;
			}

			return false;
		});
	}

	protected function removeOveriddenEvents()
	{
		// Events need to be sorted by date and id (both ascending) in order for overridden occurrences not to show
		$this->sort();
		$events = array();

		// New events array is created with the occurrence overrides replacing the relevant occurrences
		array_walk($this->events, function($event) use (&$events) {
			$events[($event->getOccurrenceDate() ?: $event->getStartDate()).'.'.($event->getParentId() ?: $event->getId())] = $event;
		});

		$this->events = $events;
	}

	protected function removeOutOfRangeEvents(\DateTime $fromDate, \DateTime $toDate)
	{
		// Remove events that do not occur within the date range
		$this->events = array_filter($this->events, function($event) use ($fromDate, $toDate) {
			if($event->getStartDate() <= $toDate->format('Y-m-d H:i:s') && $event->getEndDate() >= $fromDate->format('Y-m-d H:i:s')) {
				return true;
			}

			return false;
		});
	}
}