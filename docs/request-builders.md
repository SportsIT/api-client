# Using Request Builders
The client provides a variety of different request builders to expose appropriate methods for a given request.

## Retrieving a Builder Instance
A builder instance can be retrieved either from a `\Dash\Client` object
```php
<?php
...

/** @var \Dash\Client $client **/
$builder = $client->resource('events');
```

Or from a `\Dash\Models\Item` object

```php
<?php
$builder = \Dash\Models\Item::ofType('events')->newBuilder();
```

Additionally, `\Dash\Models\Item` objects proxy function calls to an `\Dash\Builders\IndexRequestBuilder` instance

```php
<?php
\Dash\Models\Item::ofType('events')->search();
```

## Common Methods
### Where
Allows for adding filters to the current request.
```php
<?php
// add only one filter at a time
\Dash\Models\Item::ofType('events')
  ->where('start', \Dash\Utils\Filters::OPERATOR_GREATER_THAN, '2020-09-09T12:00:00')
  ->where('start', \Dash\Utils\Filters::OPERATOR_LESS_THAN, '2020-09-12T12:00:00')
  ->where('event_type', 'g'); // when operator is omitted, '=' is used

// add many at a time
\Dash\Models\Item::ofType('events')->where([
  ['start', \Dash\Utils\Filters::OPERATOR_GREATER_THAN, '2020-09-09T12:00:00'],
  ['start', \Dash\Utils\Filters::OPERATOR_LESS_THAN, '2020-09-12T12:00:00'],
  ['event_type', 'g'], // when operator is omitted, '=' is used
]);

// add a filter group for more complex filtering
$filterGroup = new \Dash\Models\FilterGroup(\Dash\Models\FilterGroup::OPERATOR_OR);
$filterGroup->withFilters([
  ['start', \Dash\Utils\Filters::OPERATOR_GREATER_THAN, '2020-09-09T12:00:00'],
  ['start', \Dash\Utils\Filters::OPERATOR_LESS_THAN, '2020-09-12T12:00:00'],
  ['event_type', 'g'], // when operator is omitted, '=' is used
]);
\Dash\Models\Item::ofType('events')->where($filterGroup);

// shortcut method for adding a filter group
\Dash\Models\Item::ofType('events')->whereGroup([
  ['start', \Dash\Utils\Filters::OPERATOR_GREATER_THAN, '2020-09-09T12:00:00'],
  ['start', \Dash\Utils\Filters::OPERATOR_LESS_THAN, '2020-09-12T12:00:00'],
  ['event_type', 'g'], // when operator is omitted, '=' is used
], \Dash\Models\FilterGroup::OPERATOR_OR);
```

For more info on filtering see [filtering](filtering.md).

### Including
Allows for including relations in the response.
```php
<?php
// add one at a time
\Dash\Models\Item::ofType('events')->including('resource');

// add many at a time as method parameters
\Dash\Models\Item::ofType('events')->including('resource', 'customer');

// add many at a time as array
\Dash\Models\Item::ofType('events')->including(['resource', 'customer']);
```

### Fields
Allows for selecting which specific fields should be returned in the response for a given resource type. It can be helpful when optimizing your requests to ensure you receive the fastest response as encoding of data is very costly.
```php
<?php
\Dash\Models\Item::ofType('events')
  ->including('resource')
  // limit fields for events
  ->fields('event', ['start', 'end', 'event_type'])
  // limit fields for resources
  ->fields('resource', ['name']);
```

### Sort
Allows for adding a sort for the request. 
```php
<?php
\Dash\Models\Item::ofType('events')->sort('start', \Dash\Utils\SortDirections::DESC);
```

### CustomParameter
Allows for adding a custom query parameter to the request.
```php
<?php
\Dash\Models\Item::ofType('events')->customParameter('filterRelations', true);
```

### Retry
Allows for defining the retry behavior of the request in case it runs into a transient error such as rate limiting.
```php
<?php
// retry 5 times before failing, waiting 500ms between each
\Dash\Models\Item::ofType('events')->retry(5, 500);
```

## Available Builders
### IndexRequestBuilder
This builder is the first you will encounter when creating a request and can be used to search all resources of a given type or create a new resource.

#### Methods
##### Search
Runs a search request and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
$collection = \Dash\Models\Item::ofType('events')->search();
```

##### Create
Runs a create request and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
$item = \Dash\Models\Item::ofType('events')->fill([
  'start' => '2020-09-09T12:00:00',
  'end' => '2020-09-09T13:00:00',
  'event_type' => 'b',
]);

$itemDocument = \Dash\Models\Item::ofType('events')->create($item->toDocument());

// or use save method on Item to same effect
$itemDocument = $item->save();
```

##### One
Turns the request into a `\Dash\Builders\SingleResourceRequestBuilder`
```php
<?php
// relates to the event with an ID of 12
\Dash\Models\Item::ofType('events')->one(12);
```

##### SetPageSize
Allows for specifying how many items should be returned on each page.
```php
<?php
\Dash\Models\Item::ofType('events')->setPageSize(200);
```

##### SetPageNumber
Allows for specifying what page should be received.
```php
<?php
\Dash\Models\Item::ofType('events')->setPageNumber(5);
```

### SingleResourceRequestBuilder
This builder represents a request that affects a single resource that exists.

#### Methods
##### Get
Runs a get request and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->get();

// or use method on item to same effect
$document = \Dash\Models\Item::ofType('events')->find(123);
```

##### Update
Runs an update request and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
/** @var \Dash\Responses\ItemDocument $itemDocument */
$document = \Dash\Models\Item::ofType('events')->one(123)->update($itemDocument);

// or use method on item to same effect
/** @var \Dash\Models\Item $item */
$document = $item->save();
```

##### Delete
Runs an update request and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->delete();

// or use method on item to same effect
/** @var \Dash\Models\Item $item */
$document = $item->delete();
```

##### OneRelated
Turns the request into a `\Dash\Builders\OneRelatedRequestBuilder`.
```php
<?php
$related = \Dash\Models\Item::ofType('events')->one(123)->oneRelated('eventType');
```

##### ManyRelated
Turns the request into a `\Dash\Builders\ManyRelatedRequestBuilder`.
```php
<?php
$related = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees');
```

### OneRelatedRequestBuilder
This builder represents a request that affects a single resource's has-one relationship. By default, full models are returned but if you only need the identifiers (id and resource type) of related resources, you can specify that with `onlyIdentifiers()`. 

#### Methods
##### OnlyIdentifiers
Updates current request to only return identifiers instead of full models.
```php
<?php
\Dash\Models\Item::ofType('events')->one(123)->oneRelated('eventType')->onlyIdentifiers();
```

##### Get
Runs a get request and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->oneRelated('eventType')->get();
```

##### Associate
Associates the resource with another and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
/** @var \Dash\Responses\ItemDocument $item */
$document = \Dash\Models\Item::ofType('events')->one(123)->oneRelated('eventType')->associate($item);
```

##### Dissociate
Removes the association between two resources and returns the result as a `\Dash\Responses\ItemDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->oneRelated('eventType')->dissociate();
```

### ManyRelatedRequestBuilder
This builder represents a request that affects a single resource's has-many relationship. By default, full models are returned but if you only need the identifiers (id and resource type) of related resources, you can specify that with `onlyIdentifiers()`

#### Methods
##### OnlyIdentifiers
Updates current request to only return identifiers instead of full models.
```php
<?php
\Dash\Models\Item::ofType('events')->one(123)->manyRelated('eventType')->onlyIdentifiers();
```

##### Search
Runs a get request and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees')->search();
```

##### Add
Runs an update request adding the given resources to the relation and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
/** @var \Dash\Responses\CollectionDocument $collection */
$document = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees')->add($collection);
```

##### Replace
Runs an update request replacing the current value of the relation with those given and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
/** @var \Dash\Responses\CollectionDocument $collection */
$document = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees')->replace($collection);
```

##### Clear
Runs an update request clearing the relation of all values and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
$document = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees')->clear();
```

##### Delete
Runs an update request removing the given resources from the relation and returns the result as a `\Dash\Responses\CollectionDocument`.
```php
<?php
/** @var \Dash\Responses\CollectionDocument $collection */
$document = \Dash\Models\Item::ofType('events')->one(123)->manyRelated('fees')->delete($collection);
```
