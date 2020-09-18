# Using The Bare Client
The `\Dash\Client` object proxies all method calls to an instance of `\GuzzleHttp\Client` to be fully customizable while also handling authorization for you. 

Documentation for this object can be found [here](http://docs.guzzlephp.org/en/6.5/overview.html). 

## Shortcut methods for common JsonAPI operations
Shortcut methods are also provided in case you chose to not use builders.

### Search (Index)
Searching of all resources of a given type can be done using the shortcut method `search`.
```php
$response = $client->search($resourceType, $filters, $includes, $sort);
```

### Find
Retrieving of a single resource can be done using `find`.
```php
$response = $client->find($resourceType, $id, $filters, $includes, $sort);
```

### Create
Creating of a single resource along with any of its relations can be done using `createResource`. When creating with relations, they must also be added to the `$includes` array.
```php
$response = $client->createResource($resourceType, $id, $data, $filters, $includes, $sort);
```

### Update
Updating of a single resource along with any of its relations can be done using `updateResource`. All has-many relations updated in this way will perform a full replacement. When updating relations, they must also be added to the `$includes` array.
```php
$response = $client->updateResource($resourceType, $id, $data, $filters, $includes, $sort);
```

### Delete
Deleting of a single resource can be done using `deleteResource`.
```php
$response = $client->deleteResource($resourceType, $id);
```

### Get Related Resources
Retrieval of related resources can be done using `getRelatedResources`. This returns all attributes for the resources and can be searched like the `search` method.
```php
$response = $client->getRelatedResources($resourceType, $id, $relationName, $filters, $includes, $sort);
```
 
### Get Related Identifiers
Retrieval of related resource identifiers can be done using `getRelatedIdentifiers`. This returns ONLY the resource identifiers and can be searched like the `search` method.
```php
$response = $client->getRelatedIdentifiers($resourceType, $id, $relationName, $filters, $includes, $sort);
```

### Add to Has-Many Relation
Adding to a has-many relation can be done using `appendHasManyRelation`.
```php
$response = $client->getRelatedIdentifiers($resourceType, $id, $relationName, $data);
```

### Replace Has-Many Relation
Replacing a has-many relation can be done using `appendHasManyRelation`. This will replace all the current related resources with the ones you provide.
```php
$response = $client->replaceHasManyRelation($resourceType, $id, $relationName, $data);
```

### Delete From Has-Many Relation
Deleting from a has-many relation can be done using `appendHasManyRelation`. This will delete all resources that match identifiers you provide.
```php
$response = $client->deleteFromHasManyRelation($resourceType, $id, $relationName, $data);
```