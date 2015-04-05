<?php

namespace Plummer\Calendarful\Event;

/**
 * Interface EventInterface
 *
 * A common interface for events.
 *
 * Any event objects to be used within a calendar implementation must
 * implement this interface and consist of properties related to the
 * methods below.
 *
 * @package Plummer\Calendarful
 * @abstract
 */
interface EventInterface
{
	/**
	 * Get the unique id of the event.
	 * Most likely a primary key of the record in a db etc.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getId();

	/**
	 * Get the start date of the event.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getStartDate();

	/**
	 * Set the start date of the event.
	 *
	 * @param  \DateTime $startDate
	 * @return mixed
	 * @abstract
	 */
	public function setStartDate(\DateTime $startDate);

	/**
	 * Get the end date of the event.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getEndDate();

	/**
	 * Set the end date of the event.
	 *
	 * @param  \DateTime $endDate
	 * @return mixed
	 * @abstract
	 */
	public function setEndDate(\DateTime $endDate);

	/**
	 * Get the duration between the event start date and end date.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getDuration();

	/**
	 * The following methods only apply to events that recur.
	 *
	 * If the event is not recurring, the property values should be null.
	 * Some may still be null even if the event does recur.
	 */

	/**
	 * Get the id of the parent of the event.
	 *
	 * An event will tend to have a parent when it has overridden an occurrence of
	 * the parent event that does recur.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getParentId();

	/**
	 * Get the occurrence date of the event.
	 *
	 * When an occurrence of a recurring event is overridden, the date of that occurrence
	 * should be the occurrence date property value of the new event that is created in its
	 * place. When the start date of the parent recurring event is updated, the occurrence
	 * date of the overriding event should also be updated.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getOccurrenceDate();

	/**
	 * Get the recurrence type of the event.
	 *
	 * This is usually a string that matches up to the label of a recurrence type.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getRecurrenceType();

	/**
	 * Set the recurrence type of the event.
	 *
	 * @param  null  $type
	 * @return mixed
	 * @abstract
	 */
	public function setRecurrenceType($type = null);

	/**
	 * Get the until date of the event.
	 *
	 * @return mixed
	 * @abstract
	 */
	public function getRecurrenceUntil();
}
