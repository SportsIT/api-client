<?php

use Dash\Client;
use Dash\Configuration;

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
$client->authenticate();

// get all payments made between the start of the target day and the start of the next day
$response = $client
  ->resource('payments')
  ->where('date', \Dash\Utils\Filters::OPERATOR_GREATER_THAN_OR_EQUAL, $targetDate->format($dateFormat))
  ->where('date', \Dash\Utils\Filters::OPERATOR_LESS_THAN, $nextDay->format($dateFormat))
  // include the facility and payment type relationships for all matching records
  ->including('facility', 'paymentType')
  ->search();

$data = $response->getData();
