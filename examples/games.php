<?php

use Dash\Client;
use Dash\Configuration;
use Dash\Models\Item;
use Dash\Utils\Filters;

require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$dateFormat = 'Y-m-d\TH:i:s';

$config = new Configuration($clientID, $clientSecret, $companyCode);
$client = new Client($config);

$targetDate = (new DateTime('now'))->setTime(0, 0, 0, 0);
$nextDay = (clone $targetDate)->modify('+1 day');

// Call authenticate first to get an access token
$response = $client->authenticate();

// retrieve events
$document = Item::ofType('events')
  // that are games and start between the start of the target day and the start of the next day
  ->where([
    ['start', Filters::OPERATOR_GREATER_THAN_OR_EQUAL, $targetDate->format($dateFormat)],
    ['start', Filters::OPERATOR_LESS_THAN, $nextDay->format($dateFormat)],
  ])
  // individual filters can be added as well
  ->where('event_type', 'g') // defaults to '=' operator when using 2 parameters
  // include the home and visiting team relationships for all matching records
  ->including([
    'homeTeam',
    'visitingTeam',
  ])
  // search to retrieve a list of records
  ->search();

// search returns a CollectionDocument, which is able to iterate over all pages
foreach ($document as $page) {
  var_dump($page->getData()->toArray());
}
