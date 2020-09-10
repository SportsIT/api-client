# Dash - API Client Library
Library for interacting with the [Dash](https://www.dashplatform.com/) API documented [here](https://api.dashplatform.com/v1/docs).

## Installation

### With Composer
The recommended way to install Dash API Client is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Dash API Client:

```bash
php composer.phar require sportsit/api-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update Dash API Client using composer:

 ```bash
composer.phar update
 ```

## Usage
Creation of a client can be done as shown below:
 ```php
$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);
```

The client is a simple wrapper around the Guzzle HTTP library and as such any Guzzle methods can be accessed on the client as well.
```php
$response = $client->get($uri, $options);
```

### Authentication
Making calls to the Dash API requires that you have a valid access token in order to access all fields so make sure you authenticate with the API before making any other calls. Once you have an access token, it will automatically be added to all subsequent calls.
```php
$response = $client->authenticate()->get($uri, $options);
```

### Filtering
Many operations that the Dash API supports can have filtering applied to limit the resources returned. These filters are added as a multidimensional array of constraints on attributes with the default operator being `=` so in the example shown below, it will filter the results to only ones where the name attribute is Tom.
```php
$filters = [
    'name' => 'Tom'
];
```

##### Specifying Operators
Often times, you will need to specify a different operator to get the desired effect. You can specify the operator you want to use for a constraint by using a suffix on the attribute name. The table below explains the available operators:

| Operator | Suffix(s) | Notes |
| -------- | --------- | ------------- |
| `=` | _default_, `__is` | |
| `!=` | `__not`, `__isnot` | |
| `>=` | `__gte` | |
| `<=` | `__lte` | |
| `>` | `__gt` | |
| `<` | `__lt` | |
| `= null` | `__isnull` | Value is discarded as it only filters based on if the attribute is `null` |
| `!= null` | `__notnull`| "" |
| `= null` _or_ `!= null` | `__null` | If value is truthy filters same as `__isnull`, else `__notnull` |
| `in array` | `__in`, `__includes` | Value can be either an array or comma-separated list |
| `not in array` | `__notin`, `__excludes` | "" |
| `regex match` | `__regex` |  |
| `starts like` | `__starts` |  |
| `ends like` | `__ends` |  |
| `time greater than or equal` | `__time_gte` | Special case for datetimes to filter on just the time portion |
| `time less than or equal` | `__time_lte` | "" |
| `time greater than` | `__time_gt` | "" |
| `time less than` | `__time_lt` | "" |

##### Filtering Relations
To filter on a resource's relations, you simply need to use dot notation when defining the key. For example, if we wanted to only get Events at a certain Facility, we would use the following filter:
```php
$filters = ['resource.facility_id' => $facilityID];
```

##### Complex Filtering
Filters can be grouped under `and`/`or` keys in order to achieve more complex filtering rules such as ...
```php

```

### Common JsonAPI Operations
##### Search (Index)
Searching of all resources of a given type can be done using the shortcut method `search`.
```php
$response = $client->search($resourceType, $filters, $includes, $sort);
```

##### Find
Retrieving of a single resource can be done using `find`.
```php
$response = $client->find($resourceType, $id, $filters, $includes, $sort);
```

##### Create
Creating of a single resource along with any of its relations can be done using `createResource`. When creating with relations, they must also be added to the `$includes` array.
```php
$response = $client->createResource($resourceType, $id, $data, $filters, $includes, $sort);
```

##### Update
Updating of a single resource along with any of its relations can be done using `updateResource`. All has-many relations updated in this way will perform a full replacement. When updating relations, they must also be added to the `$includes` array.
```php
$response = $client->updateResource($resourceType, $id, $data, $filters, $includes, $sort);
```

##### Delete
Deleting of a single resource can be done using `deleteResource`.
```php
$response = $client->deleteResource($resourceType, $id);
```

##### Get Related Resources
Retrieval of related resources can be done using `getRelatedResources`. This returns all attributes for the resources and can be searched like the `search` method.
```php
$response = $client->getRelatedResources($resourceType, $id, $relationName, $filters, $includes, $sort);
```
 
##### Get Related Identifiers
Retrieval of related resource identifiers can be done using `getRelatedIdentifiers`. This returns ONLY the resource identifiers and can be searched like the `search` method.
```php
$response = $client->getRelatedIdentifiers($resourceType, $id, $relationName, $filters, $includes, $sort);
```

##### Add to Has-Many Relation
Adding to a has-many relation can be done using `appendHasManyRelation`.
```php
$response = $client->getRelatedIdentifiers($resourceType, $id, $relationName, $data);
```

##### Replace Has-Many Relation
Replacing a has-many relation can be done using `appendHasManyRelation`. This will replace all the current related resources with the ones you provide.
```php
$response = $client->replaceHasManyRelation($resourceType, $id, $relationName, $data);
```

##### Delete From Has-Many Relation
Deleting from a has-many relation can be done using `appendHasManyRelation`. This will delete all resources that match identifiers you provide.
```php
$response = $client->deleteFromHasManyRelation($resourceType, $id, $relationName, $data);
```
