<?php

use Dash\Client;
use Dash\Configuration;
use Dash\Models\Item;

require __DIR__.'/../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$dateFormat = 'Y-m-d\TH:i:s';

$config = new Configuration($clientID, $clientSecret, $companyCode);
$client = new Client($config);

// Call authenticate first to get an access token
$response = $client->authenticate();

// Get all resources with their resource areas
$response = Item::ofType('resources')
  ->including('resourceAreas')
  ->search();

// decode the json data to associative array
$data = $response->getData();
