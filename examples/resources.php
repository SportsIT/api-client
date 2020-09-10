<?php
require '../vendor/autoload.php';

$clientID = '929abc4b6e78ebff57d321c97869bac2';
$clientSecret = '14ef2906729c7572a9f58c21ff5f4768';
$companyCode = 'arenasports_demo';

$dateFormat = 'Y-m-d\TH:i:s';

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