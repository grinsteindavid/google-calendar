Manage events and calendars on a Google Calendar
=========================

This package makes working with a Google Calendar a breeze.

Features
--------

* Manage any number of calenders and its events with less code

Requirements
--------

* Google calendar API credentials.json (as a service) from https://console.developers.google.com
* credentials.json dir folder should be on a ENV variable named "GOOGLE_CALENDAR_CREDENTIALS" (example: GOOGLE_CALENDAR_CREDENTIALS=/home/ubuntu/credentials.json)

Installation
--------
You can install the package via composer:

composer require grinsteindavid/google-calendar

Usage
--------

```php
use GrinsteinDavid\GoogleCalendar\Calendar;
use GrinsteinDavid\GoogleCalendar\Event;

$calendar = new Calendar();
$calendar->summary = 'Summer';
$calendar->save();

$event = new Event($calendar->id);
$event->timeZone = "America/New_York";
$event->summary = 'First Event';
$event->startDateTime = date("Y-m-d H:i:s", strtotime('+1 hours'));
$event->endDateTime = date("Y-m-d H:i:s", strtotime('+4 hours'));
$event->save();

foreach ($calendar->events() as $event) {
    $event->description = 'Hottest summer!';
    $event->save(); // UPDATED BY ATTRS

    $event->update([  // UPDATED BY PARAMS
    	'description' => 'Hottest summer!'
    ]);
}

$calendar2 = new Calendar($calendarId);

$events = $calendar2->events();

$event = new Event($calendar2->id, $calendar2->events[0]->id);
$event->organizerEmail = 'example1@email.com';
$event->organizerName = "David Miranda Grinstein";
$event->guestsCanInviteOthers = true;
$event->guestsCanModify = true;
$event->guestsCanSeeOtherGuests = true;
$event->anyoneCanAddSelf = true;
$event->attendees = [
    [
        'email' => 'example1@email.com',
        'displayName' => 'example 1'
    ],
    [
        'email' => 'example2@email.com',
        'displayName' => 'example 2'
    ]
];
$event->save();


array_push($event->attendees, [
    'email' => 'example3@email.com',
    'displayName' => 'example 3'
]);

$event->save();
```
