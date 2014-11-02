<?php

namespace Plummer\Calendarful;

interface RecurrenceInterface
{
	public function getLabel();

	public function getLimit();

	public function generateEvents(Array $events, $fromDate, $toDate, $limit = null);
}
