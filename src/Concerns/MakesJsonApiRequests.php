<?php

namespace Dash\Concerns;

trait MakesJsonApiRequests {
  /**
   * Make an index request to search all instances of the given resource type.
   *
   * @param string $resourceType
   * @param array  $filters
   * @param array  $includes
   * @param null   $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function search($resourceType, $filters = [], $includes = [], $sort = null) {
    return $this->client->get(static::buildIndexRequestUri($resourceType, $filters, $includes, $sort));
  }

  /**
   * Make a request to fetch the given resource by resource type and id.
   *
   * @param string      $resourceType
   * @param string      $id
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function find($resourceType, $id, $filters = [], $includes = [], $sort = null) {
    return $this->client->get(static::buildResourceRequestUri($resourceType, $id, $filters, $includes, $sort));
  }

  /**
   * Make a request to fetch related resources for the given resource's relation.
   *
   * @param string      $resourceType
   * @param string      $id
   * @param string      $relationName
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function getRelatedResources($resourceType, $id, $relationName, $filters = [], $includes = [], $sort = null) {
    return $this->client->get(static::buildRelatedResourceRequestUri($resourceType, $id, $relationName, $filters, $includes, $sort));
  }

  /**
   * Make a request to fetch the given relationship for a resource.
   *
   * @param string      $resourceType
   * @param string      $id
   * @param string      $relationName
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function getRelationship($resourceType, $id, $relationName, $filters = [], $includes = [], $sort = null) {
    return $this->client->get(static::buildRelationshipRequestUri($resourceType, $id, $relationName, $filters, $includes, $sort));
  }

  /**
   * Make a request to create a resource of the given resource type.
   *
   * @param string      $resourceType
   * @param array       $data
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function createResource($resourceType, $data, $filters = [], $includes = [], $sort = null) {
    return $this->client->post(static::buildIndexRequestUri($resourceType, $filters, $includes, $sort), [
      'json' => $data,
    ]);
  }

  /**
   * Make a request to update a given resource.
   * Relationships can be updated as well, with to-many relationships doing a full replacement.
   *
   * @param string      $resourceType
   * @param string      $id
   * @param array       $data
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function updateResource($resourceType, $id, $data, $filters = [], $includes = [], $sort = null) {
    return $this->client->patch(static::buildResourceRequestUri($resourceType, $id, $filters, $includes, $sort), [
      'json' => $data,
    ]);
  }

  /**
   * Make a request to delete a given resource.
   *
   * @param string $resourceType
   * @param string $id
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function deleteResource($resourceType, $id) {
    return $this->client->delete(static::buildResourceRequestUri($resourceType, $id));
  }

  /**
   * Make a request to add to a given resource's to-many relationship.
   *
   * @param string $resourceType
   * @param string $id
   * @param string $relationName
   * @param array  $data
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function appendToManyRelation($resourceType, $id, $relationName, $data) {
    return $this->client->post(static::buildRelationshipRequestUri($resourceType, $id, $relationName), [
      'json' => $data,
    ]);
  }

  /**
   * Make a request to do a full replace for a given resource's to-many relationship.
   *
   * @param string $resourceType
   * @param string $id
   * @param string $relationName
   * @param array  $data
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function replaceToManyRelation($resourceType, $id, $relationName, $data) {
    return $this->client->patch(static::buildRelationshipRequestUri($resourceType, $id, $relationName), [
      'json' => $data,
    ]);
  }

  /**
   * Make a request to delete from a given resource's to-many relationship.
   *
   * @param string $resourceType
   * @param string $id
   * @param string $relationName
   * @param array  $data
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function deleteFromToManyRelation($resourceType, $id, $relationName, $data) {
    return $this->client->delete(static::buildRelationshipRequestUri($resourceType, $id, $relationName), [
      'json' => $data,
    ]);
  }
}
