<?php
require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);

// we want all resources so no filters are needed
$filters = [];

// include the resource areas for each resource
$includes = [
  'resourceAreas',
];

// Call authenticate first to get an access token
$response = $client->authenticate()
  ->get(\Dash\Client::buildIndexRequestUri('resources', $filters, $includes));

// decode the json data to associative array
$data = json_decode($response->getBody()->getContents(), true);