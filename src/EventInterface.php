<?php

namespace Plummer\Calendar;

interface EventInterface
{
	public function getId();

	public function getName();

	public function getStartDate();

	public function getEndDate();

	public function getParent();

	public function getParentDate();
    
    public function getRecurrenceType();

    public function getRecurrenceUntil();

	public function setCalendar(Calendar &$calendar);
}
