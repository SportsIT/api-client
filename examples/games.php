<?php
require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);

$targetDate = (new DateTime('now'))->setTime(0, 0, 0, 0);
$nextDay = (clone $targetDate)->modify('+1 day');

// get all games that start between the start of the target day and the start of the next day
$filters = [
  'start__gte' => $targetDate->format(\Dash\Client::DATE_FORMAT),
  'start__lt' => $nextDay->format(\Dash\Client::DATE_FORMAT),
  'event_type' => 'g', // get only games
];

// include the home and visiting team relationships for all matching records
$includes = [
  'homeTeam',
  'visitingTeam',
];

// Call authenticate first to get an access token
$response = $client->authenticate()
  ->get(\Dash\Client::buildIndexRequestUri('events', $filters, $includes));

// decode the json data to associative array
$data = json_decode($response->getBody()->getContents(), true);