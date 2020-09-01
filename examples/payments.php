<?php
require '../vendor/autoload.php';

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$dateFormat = 'Y-m-d\TH:i:s';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
// Call authenticate to get an access token
$client = (new \Dash\Client($config))->authenticate();

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

// increase the page size from 15 (default) to 25
$page = new \Dash\PageObject(1, 25);

// add any custom query parameters such as telling filters to apply to included relationships
$custom = [
  'filterRelations' => true,
];

$response = $client->get(\Dash\Client::buildIndexRequestUri('payments', $filters, $includes, null, $page, $custom));

// decode the json data to associative array
$responseArray = json_decode($response->getBody()->getContents(), true);
$data = $responseArray['data'];

// simplified method of retrieving all pages
// NOTE: API endpoints are rate-limited (results in 429 status code) so final solutions need to be able to deal with that
if ($responseArray['meta']['page']['current-page'] !== $responseArray['meta']['page']['last-page']) {
  for ($currentPage = $responseArray['meta']['page']['current-page']++; $currentPage < $responseArray['meta']['page']['last-page']; $currentPage++) {
    $page->setPageNumber($currentPage);
    $response = $client->get(\Dash\Client::buildIndexRequestUri('payments', $filters, $includes, null, $page, $custom));
    // decode the json data to associative array
    $responseArray = json_decode($response->getBody()->getContents(), true);
    $data = array_merge($data, $responseArray['data']);
  }
}