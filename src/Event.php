<?php 

namespace Grinsteindavid\GoogleCalendar;

class Event
{
	private $service;
	private $instance;

    public $id;
    public $calendarId;
    public $summary;
    public $description;
    public $location;
    public $startDateTime;
    public $startDate;
    public $endDateTime;
    public $endDate;
    public $timeZone;
    public $attendees;
    public $created;
    public $updated;
    public $htmlLink;
    public $organizerEmail;
    public $organizerName;
    public $locked;
    public $guestsCanInviteOthers;
    public $guestsCanModify;
    public $guestsCanSeeOtherGuests;
    public $anyoneCanAddSelf;
    
    public function __construct(string $calendarId = NULL, string $eventId = NULL)
    {
    	$credentialsPath = getenv('GOOGLE_CALENDAR_CREDENTIALS');

        $client = new \Google_Client();
		$client->setApplicationName("GOOGLE CALENDAR");
		$client->addScope(\Google_Service_Calendar::CALENDAR);
		$client->setAuthConfig($credentialsPath);

		$this->service =  new \Google_Service_Calendar($client); 

        $this->calendarId = $calendarId;
        $this->attendees = [];
        $this->locked = false;
        $this->guestsCanInviteOthers = false;
        $this->guestsCanModify = false;
        $this->guestsCanSeeOtherGuests = false;
        $this->anyoneCanAddSelf = false;

		if ($eventId) {
			$this->id = $eventId;
			$this->get();
		}
    }

    public function get()
    {
    	$this->instance = $this->service->events->get($this->calendarId, $this->id);
        $this->updateAttrs();
    }

    public function save()
    {	
        if ($this->id) return $this->update();

        $params = [];
        $params['summary'] = $this->summary;
        $params['description'] = $this->description;
        $params['location'] = $this->location;
        $params['start'] = [
            //'date' => $this->googleDateFormat($this->startDate),
            'dateTime' => $this->googleDateTimeFormat($this->startDateTime),
            'timeZone' => $this->timeZone ? $this->timeZone : date_default_timezone_get(),
        ];
        $params['end'] = [
            //'date' => $this->googleDateFormat($this->endDate),
            'dateTime' => $this->googleDateTimeFormat($this->endDateTime),
            'timeZone' => $this->timeZone ? $this->timeZone : date_default_timezone_get(),
        ];
        $params['locked'] = $this->locked;
        $params['guestsCanInviteOthers'] = $this->guestsCanInviteOthers;
        $params['guestsCanModify'] = $this->guestsCanModify;
        $params['guestsCanSeeOtherGuests'] = $this->guestsCanSeeOtherGuests;
        $params['anyoneCanAddSelf'] = $this->anyoneCanAddSelf;
        $params['reminders'] = [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'email', 'minutes' => 30],
                ['method' => 'popup', 'minutes' => 30]
            ]
        ];
        if ($this->organizerEmail) $params['organizer'] = [
            'email' => $this->organizerEmail,
            'displayName' => $this->organizerName,
        ];
        if (count($this->attendees)) $params['attendees'] = $this->attendees;

    	$this->instance = new \Google_Service_Calendar_Event($params);

    	$this->instance = $this->service->events->insert($this->calendarId, $this->instance);
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
        $this->instance->timeZone = isset($optParams['timeZone']) ? $optParams['timeZone'] : $this->timeZone;
        $this->instance->start->dateTime = isset($optParams['startDateTime']) ? $this->googleDateTimeFormat($optParams['startDateTime']) : $this->googleDateTimeFormat($this->startDateTime);
        //$this->instance->start->date = isset($optParams['startDate']) ? $optParams['startDate'] : $this->startDate;
        $this->instance->start->timeZone = isset($optParams['timeZone']) ? $optParams['timeZone'] : $this->timeZone;
        $this->instance->end->dateTime = isset($optParams['endDateTime']) ? $this->googleDateTimeFormat($optParams['endDateTime']) : $this->googleDateTimeFormat($this->endDateTime);
        //$this->instance->end->date = isset($optParams['endDate']) ? $optParams['endDate'] : $this->endDate;
        $this->instance->end->timeZone = isset($optParams['timeZone']) ? $optParams['timeZone'] : $this->timeZone;
        $this->instance->attendees = isset($optParams['attendees']) ? $optParams['attendees'] : $this->attendees;
        $this->instance->organizer->email = isset($optParams['organizerEmail']) ? $optParams['organizerEmail'] : $this->organizerEmail;
        $this->instance->organizer->displayName = isset($optParams['organizerName']) ? $optParams['organizerName'] : $this->organizerName;
        $this->instance->locked = isset($optParams['locked']) ? $optParams['locked'] : $this->locked;
        $this->instance->guestsCanInviteOthers = isset($optParams['guestsCanInviteOthers']) ? $optParams['guestsCanInviteOthers'] : $this->guestsCanInviteOthers;
        $this->instance->guestsCanModify = isset($optParams['guestsCanModify']) ? $optParams['guestsCanModify'] : $this->guestsCanModify;
        $this->instance->guestsCanSeeOtherGuests = isset($optParams['guestsCanSeeOtherGuests']) ? $optParams['guestsCanSeeOtherGuests'] : $this->guestsCanSeeOtherGuests;
        $this->instance->anyoneCanAddSelf = isset($optParams['anyoneCanAddSelf']) ? $optParams['anyoneCanAddSelf'] : $this->anyoneCanAddSelf;

    	$this->instance = $this->service->events->update($this->calendarId, $this->id, $this->instance);
        $this->updateAttrs();
    }

    public function delete()
    {
    	$this->service->events->delete($this->calendarId, $this->id);
    }

    private function updateAttrs()
    {
        $this->summary = $this->instance->getSummary();
        $this->description = $this->instance->getDescription();
        $this->location = $this->instance->getLocation();
        $this->created = date('Y-m-d H:i:s', strtotime($this->instance->getCreated()));
        $this->updated = date('Y-m-d H:i:s', strtotime($this->instance->getUpdated()));
        $this->timeZone = $this->instance->timeZone;
        $this->startDateTime = date('Y-m-d H:i:s', strtotime($this->instance->start->dateTime));
        //$this->startDate = date('Y-m-d', strtotime($this->instance->start->date));
        $this->endDateTime = date('Y-m-d H:i:s', strtotime($this->instance->end->dateTime));
        //$this->endDate = date('Y-m-d', strtotime($this->instance->end->date));
        $this->attendees = $this->instance->attendees;
        $this->htmlLink = $this->instance->htmlLink;
        $this->locked = $this->instance->locked;
        $this->organizerEmail = $this->instance->organizer->email;
        $this->organizerName = $this->instance->organizer->displayName;
        $this->guestsCanInviteOthers = $this->instance->guestsCanInviteOthers;
        $this->guestsCanModify = $this->instance->guestsCanModify;
        $this->guestsCanSeeOtherGuests = $this->instance->guestsCanSeeOtherGuests;
        $this->anyoneCanAddSelf = $this->instance->anyoneCanAddSelf;
    }

    private function googleDateTimeFormat(string $date = null)
    {
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    private function googleDateFormat(string $date = null)
    {
        return date('Y-m-d\TH', strtotime($date)); // INCORRECT FORMAT
    }
}