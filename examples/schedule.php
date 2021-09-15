<?php
require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);

$targetDate = (new DateTime('now'))->setTime(0, 0, 0, 0);
$nextDay = (clone $targetDate)->modify('+1 day');
$facilityID = '1';

// get all events between the start of the target day and the start of the next day that are published
$filters = [
  'end__gte' => $targetDate->format(\Dash\Client::DATE_FORMAT),
  'start__lt' => $nextDay->format(\Dash\Client::DATE_FORMAT),
  'eventType.code__not' => 'L', // we don't want locker room events
  // event must be published to be visible or the event type must have display_private enabled
  'and.or.0.publish' => true,
  'and.or.1.and.publish' => false,
  'and.or.1.and.eventType.display_private' => true,
  'resource.facility.id' => $facilityID,
];

// include the following relationships for all matching records
$includes = [
  'resource',
  'resourceArea',
  'eventType',
  'homeTeam',
  'visitingTeam',
];

// sort the events by their start times
$sort = 'start';

// Call authenticate first to get an access token
$response = $client->authenticate()
  ->get(\Dash\Client::buildIndexRequestUri('events', $filters, $includes));

// decode the json data to associative array
$data = json_decode($response->getBody()->getContents(), true);