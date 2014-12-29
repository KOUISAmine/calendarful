# Calendarful

[![Build Status](https://travis-ci.org/benplummer/calendarful.svg?branch=master)](https://travis-ci.org/benplummer/calendarful)
[![Packagist](https://img.shields.io/packagist/v/plummer/calendarful.svg?style=flat)](https://packagist.org/packages/plummer/calendarful)
[![Packagist](https://img.shields.io/packagist/l/plummer/calendarful.svg?style=flat)](https://github.com/benplummer/calendarful/blob/master/LICENSE)

Calendarful is a simple and easily extendable PHP solution that allows the generation of occurrences of recurring events, 
thus eliminating the need to store hundreds or maybe thousands of occurrences in a database or other methods of storage. 

This package ships with default implementations of interfaces for use out of the box although it is very simple to provide
your own implementations if needs be.

## Installation

This project can be installed via [Composer]:

``` bash
$ composer require plummer/calendarful
```

## Required Set Up

There are a few steps to take before using this package.

### Event Model

This package requires that you have an `Event` model. This could be an Eloquent model from Laravel or any ORM model or class.

For the model to be compatible with this package it must implement the `EventInterface`
 
```php
<?php

use Plummer\Calendarful\Event\EventInterface;

class Event implements EventInterface
{
    
}
```

Your `Event` model must then provide an implementation for each of the methods within the `EventInterface`.

Ideally, there should be a property on the model that is relevant to each of the getter and setter methods of the `EventInterface` for this package to function most effectively.
For example, there should be a start date property that `getStartDate()` and `setStartDate()` can use etc.
 
The `MockEvent` class under the `tests` directory provides an example implementation of the methods and relevant properties to be used.
Documentation of each `EventInterface` method inside the file also provides brief explanations of the purpose of each of the properties.

### Event Registry

Once you have your `Event` model fully set up, you need to create a class for your `EventRegistry` which should implement the `RegistryInterface`.

This is my example Event Registry using Laravel's Eloquent ORM:

```php
<?php

use Plummer\Calendarful\RegistryInterface;

class EventRegistry implements RegistryInterface
{
    public function get(Array $filters = array())
    {
        $events = [];

        if(!$filters) {
            foreach(\TestEvent::all() as $event) {
                $events[$event->getId()] = $event;
            }
        }
        else {
            $results = \TestEvent::where('startDate', '<=', $filters['toDate']->format('Y-m-d'))
                        ->where('endDate', '>=', $filters['fromDate']->format('Y-m-d'))
                        ->orWhere(
                            function($query) use ($filters) {
                                $query->whereNotNull('type')
                                    ->where(
                                        function ($query) use ($filters) {
                                            $query->whereNull('until')
                                                ->where('until', '>=', $filters['fromDate']->format('Y-m-d'), 'or');
                                        }
                                    );
                            }
                        )->get();


            foreach($results as $event) {
                $events[$event->getId()] = $event;
            }
        }

        return $events;
    }
}
```

When you populate the default `Calendar` class with events, the parameters you pass will be passed to the 
Event Registry as the `filters` you can see being used above. These passed `filters` allow the Event Registry 
to do a lot of the work in returning only the relevant events.

The `EventRegistry` above uses the date filters to determine which events fall into the date range given.
If no filters are provided and all events are returned, the `Calendar` class will determine which of those events are relevant.

**The sole reason for filters being passed to the Event Registry is to optimize performance by using relevant events earlier in the process.**

# Usage

With an Event model and Event Registry set up, you just need to instantiate the Event Registry and Calendar and populate the Calendar.

The `populate` method takes in the Event Registry, the date range that the Calendar should cover (from and to date) and a limit if there
is a maximum limit on the amount of events you want back.
 
```php
$eventsRegistry = new EventRegistry();

$calendar = Plummer\Calendarful\Calendar\Calendar::create()
			    ->populate($eventsRegistry, new \DateTime('2014-04-01'), new \DateTime('2014-04-30'));
```

The default Calendar uses an ArrayIterator so now we can access the events like so:

```php
foreach($calendar as $event) {
    // Use event as necessary... 
}
```

## Recurring Events

To identify recurring events and generate occurrences for them, a `RecurrenceFactory` comes into the above process.

```php
$eventsRegistry = new EventRegistry();

$recurrenceFactory = new \Plummer\Calendarful\Recurrence\RecurrenceFactory();
$recurrenceFactory->addRecurrenceType('daily', 'Plummer\Calendarful\Recurrence\Type\Daily');
$recurrenceFactory->addRecurrenceType('weekly', 'Plummer\Calendarful\Recurrence\Type\Weekly');
$recurrenceFactory->addRecurrenceType('monthly', 'Plummer\Calendarful\Recurrence\Type\MonthlyDate');

$calendar = Plummer\Calendarful\Calendar\Calendar::create($recurrenceFactory)
			    ->populate($eventsRegistry, new \DateTime('2014-04-01'), new \DateTime('2014-04-30'));
```

We can see that the three default package recurrence types were injected into the Recurrence Factory and passed to the Calendar.

In order for occurrences to be generated, the `getRecurrenceType()` return value for a recurring event should match up to the first parameter
value from where Recurrence Types are added to the Recurrence Factory e.g. 'daily', 'weekly' or 'monthly' above.

When occurrences are generated, they will be a clone of their parent aside from updates on their dates and recurrence related properties.

### Overriding Occurrences

If you are using this package for its recurrence functionality, it is likely you will want to override an occurrence at some point.

For instance, you may have a weekly recurring event that runs every Monday but you may want next week's occurrence to run on the Tuesday instead 
and continue on Mondays again thereafter. This is where occurrence overrides come in.

When you want to override an occurrence you need to create a new Event and save it to your storage method of choice.
For the override to be recognised by the package you need to update the values of those properties on the Event model 
returned by `getParentId()` and `getOccurrenceDate`.

Lets say your Event model has properties called `parentId` and `occurrenceDate` in conjunction with those `EventInterface` methods mentioned.

To override next Monday's occurrence to Tuesday you would need to set the `parentId` to that of the parent event that recurs every Monday
and the `occurrenceDate` to the date that the occurrence would have been; the Monday. The `startDate` would also need to be updated to the Tuesday's date.
Once that new event is saved, the Monday occurrence next week would be replaced by the override in the calendar.

**If a parent event start date ever changes, all of the occurrence dates for the overrides of the occurrences for that event need to be altered by
the same difference in time to ensure the overrides still work.**

### Adding your own Recurrence Types

To add your own Recurrence Type all you need to do is create a new class that implements the `RecurrenceInterface` and its methods.

The new Recurrence Type can then be added to the `RecurrenceFactory` in the same way as shown above.

```php
$recurrenceFactory = new \Plummer\Calendarful\Recurrence\RecurrenceFactory();
$recurrenceFactory->addRecurrenceType('ThisShouldMatchAnEventRecurrenceTypePropertyValue', 'Another\RecurrenceType\ClassPath');
```

## Different Types of Calendars

This package supports different types of calendars as long as they implement the `CalendarInterface`.

You may want to use multiple calendars at once, in which case you can use the `CalendarFactory`.
You add calendars to the factory in much the same way as the `RecurrenceFactory` works.
 
```php
$calendarFactory = new \Plummer\Calendarful\Calendar\CalendarFactory();
$calendarFactory->addCalendarType('gregorian', 'Plummer\Calendarful\Calendar\Calendar');
$calendarFactory->addCalendarType('anotherType', 'Another\CalendarType\ClassPath');
```
 
Next, you can either retrieve the calendar type you desire.

```php
$calendar = $calendarFactory->createCalendar('gregorian');
```

Or retrieve all calendar types to loop through etc.

```php
foreach($calendarFactory->getCalendarTypes() as $type => $calendar) {
    // Use calendar...
}
```

## Extending the Package

There are interfaces for every component within this package therefore if the default implementations do not do
exactly as you wish or you want them to work slightly differently it is quite simple to construct your own implementation.
This may be for one component or for all.

**If you do use your own components, I highly recommend looking at the functionality of the existing default components as
you may wish to use parts e.g. to ensure occurrence overrides etc still function.**

# Testing

Unit tests can be run inside the package:

``` bash
$ ./vendor/bin/phpunit
```

# Contributing

If you wish to contribute to this package, feel free to submit a PR or an issue and I will try to review it as soon as possible.

Any feedback on the package, whether good, bad or if anything is missing will always be taken on board.

Feel free to tweet me at @Ben_Plummer or email me at ben@benplummer.co.uk. 

# License

**plummer/calendarful** is licensed under the MIT license.  See the `LICENSE` file for more details.

[Composer]: https://getcomposer.org/
