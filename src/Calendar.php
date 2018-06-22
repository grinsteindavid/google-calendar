<?php 

namespace GrinsteinDavid\GoogleCalendar;

use GrinsteinDavid\GoogleCalendar\Event;

class Calendar
{
    private $service;
    private $instance;

    public $id;
    public $summary;
    public $description;
    public $location;
    public $timeZone;
    public $events;
    
    public function __construct(string $calendarId = NULL)
    {
        $credentialsPath = getenv('GOOGLE_CALENDAR_CREDENTIALS');

        $client = new \Google_Client();
        $client->setApplicationName("GOOGLE CALENDAR");
        $client->addScope(\Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig($credentialsPath);

        $this->service =  new \Google_Service_Calendar($client); 

        if ($calendarId) {
            $this->id = $calendarId;
            $this->get();
        }
    }

    public function get()
    {
        $this->instance = $this->service->calendars->get($this->id);
        $this->updateAttrs();
    }

    public function save()
    {   
        $this->instance = new \Google_Service_Calendar_Calendar();
        $this->instance->setSummary($this->summary);
        $this->instance->setDescription($this->description);
        $this->instance->setLocation($this->location);
        $this->instance->setTimeZone($this->timeZone);

        $this->instance = $this->service->calendars->insert($this->instance);
        $this->id = $this->instance->getId();
    }

    public function update(array $optParams = [])
    {   
        $this->instance->setSummary(
            isset($optParams['summary']) ? $optParams['summary'] : $this->summary
        );
        $this->instance->setDescription(
            isset($optParams['description']) ? $optParams['description'] : $this->description
        );
        $this->instance->setLocation(
            isset($optParams['location']) ? $optParams['location'] : $this->location
        );
        $this->instance->setTimeZone(
            isset($optParams['timeZone']) ? $optParams['timeZone'] : $this->timeZone
        );

        $this->instance = $this->service->calendars->update($this->id, $this->instance);
        $this->updateAttrs();
    }

    public function delete()
    {
        $this->service->calendars->delete($this->id);
    }

    public function events(array $optParams = [])
    {
        /**$exampleParams = [
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => date('c'),
        ];**/

        $this->events = [];

        foreach ($this->service->events->listEvents($this->id, $optParams)->getItems() as $event) {
            array_push($this->events, new Event($this->id, $event->getId()));
        }

        return $this->events;
    }

    private function updateAttrs()
    {
        $this->summary = $this->instance->getSummary();
        $this->description = $this->instance->getDescription();
        $this->location = $this->instance->getLocation();
        $this->timeZone = $this->instance->getTimeZone();
    }
}