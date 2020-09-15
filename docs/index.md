# Introduction
Access Dash's [jsonapi.org](https://jsonapi.org/) compliant API using a fluent builder inspired by Laravel's Eloquent ORM.

## Authenticating
The builders can be used to access our public API by default, but many fields will be hidden. Authentication using an API key that was generated in Dash will allow you to access all available fields for resources that the key has rights for.

To authenticate with the API, you must exchange the API token credentials for an access token by calling the `authenticate` method on a `\Dash\Client` object.
```php
<?php

$clientID = '<replace with client ID>';
$clientSecret = '<replace with client secret>';
$companyCode = '<replace with company code>';

$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);

$client->authenticate();
```

This will automatically add the access token to all requests made through both the builders and the `\GuzzleHttp\Client` instance accessible through `\Dash\Client`.

## Dates
Dates are expected to be in the format `Y-m-d\TH:i:s` to avoid any errors. To help with this, there is a utility class available for formatting dates.
```php
<?php

// Raw format is available as a constant
$format = \Dash\Utils\Dates::FORMAT;

// Static function available for formatting either a date string or DateTimeInterface object
$date = \Dash\Utils\Dates::format(new DateTime());
$date = \Dash\Utils\Dates::format('2020-09-16 12:00:00');
```

## Using Request Builders
For a vast majority of use cases, builders can be used to interact with all standard JsonApi endpoints such as the following:
- Search (GET /resource-type)
- Create (POST /resource-type)
- Update (PATCH /resource-type)
- Delete (DELETE /resource-type/id)
- Retrieve By ID (GET /resource-type/id)
- View Relationship (GET /resource-type/id/relation-name _or_ /resource-type/id/relationships/relation-name)
- Update Relationship (POST/PATCH/DELETE /resource-type/id/relation-name _or_ /resource-type/id/relationships/relation-name)

See [Request Builders](request-builders.md).

## Using bare client
For the minority of cases where endpoints do not adhere to the JsonApi standard, the `\Dash\Client` object offers some lower level access to the HTTP client.

See [Using The Bare Client](bare-client.md).

## Filtering
See [Filtering](filtering.md).