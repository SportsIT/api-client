<?php
require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$dateFormat = 'Y-m-d\TH:i:s';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);

$targetDate = (new DateTime('now'))->setTime(0, 0, 0, 0);
$nextDay = (clone $targetDate)->modify('+1 day');

// get all payments made between the start of the target day and the start of the next day
$filters = [
    'date__gte' => $targetDate->format($dateFormat),
    'date__lt' => $nextDay->format($dateFormat),
];

// include the facility and payment type relationships for all matching records
$includes = [
    'facility',
    'paymentType',
];

// Call authenticate first to get an access token
$response = $client->authenticate()
    ->get(\Dash\Client::buildIndexRequestUri('payments', $filters, $includes));

// decode the json data to associative array
$data = json_decode($response->getBody()->getContents(), true);