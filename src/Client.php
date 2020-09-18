<?php

namespace Dash;

use Dash\Builders\IndexRequestBuilder;
use Dash\Exceptions\AuthException;
use Dash\Exceptions\NotAuthenticatedException;
use Dash\Models\Item;
use Dash\Utils\BuildsUris;

/**
 * Class Client.
 *
 * @mixin \GuzzleHttp\Client
 */
class Client {
  const AUTH_GRANT_TYPE = 'client_credentials';

  const VERSION = '3.0.0';

  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * @var Configuration
   */
  private $config;

  /**
   * @var string
   */
  private $token;

  /**
   * Client constructor.
   *
   * @param Configuration $config
   */
  public function __construct(Configuration $config) {
    $this->config = $config;
    $this->refreshClient();
  }

  /**
   * Authenticate with the API and get an access token.
   *
   * @throws AuthException
   *
   * @return $this
   */
  public function authenticate() {
    $response = $this->client->post('company/auth/token', [
      'query' => [
        'company' => $this->config->getCompanyCode(),
      ],
      'json' => [
        'grant_type' => static::AUTH_GRANT_TYPE,
        'client_id' => $this->config->getClientID(),
        'client_secret' => $this->config->getClientSecret(),
      ],
    ]);

    $body = json_decode($response->getBody()->getContents(), true) ?? [];

    if ($response->getStatusCode() !== 200 || !isset($body['auth']) || $body['auth'] === false) {
      throw new AuthException("Error when authorizing: {$body['message']}");
    }

    $this->token = $body['access_token'];

    $this->refreshClient();

    return $this;
  }

  protected function refreshClient() {
    $this->client = (new GuzzleFactory())->make($this->token);
    Item::setDocumentClient((new DocumentClientFactory())->make($this->token));
  }

  /**
   * Get a builder to access a resource.
   *
   * @param string $resourceType
   *
   * @return IndexRequestBuilder
   */
  public function resource(string $resourceType) {
    return new IndexRequestBuilder((new DocumentClientFactory())->make($this->token), $resourceType);
  }

  /**
   * Make an index request to search all instances of the given resource type.
   *
   * @param string      $resourceType
   * @param array       $filters
   * @param array       $includes
   * @param string|null $sort
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function search(string $resourceType, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->get(BuildsUris::buildIndexRequestUri($resourceType, $filters, $includes, $sort));
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
  public function find(string $resourceType, $id, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->get(BuildsUris::buildResourceRequestUri($resourceType, $id, $filters, $includes, $sort));
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
  public function getRelatedResources(string $resourceType, $id, string $relationName, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->get(BuildsUris::buildRelatedResourceRequestUri($resourceType, $id, $relationName, $filters, $includes, $sort));
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
  public function getRelationship(string $resourceType, $id, string $relationName, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->get(BuildsUris::buildRelationshipRequestUri($resourceType, $id, $relationName, $filters, $includes, $sort));
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
  public function createResource(string $resourceType, array $data, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->post(BuildsUris::buildIndexRequestUri($resourceType, $filters, $includes, $sort), [
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
  public function updateResource(string $resourceType, $id, array $data, array $filters = [], array $includes = [], ?string $sort = null) {
    return $this->client->patch(BuildsUris::buildResourceRequestUri($resourceType, $id, $filters, $includes, $sort), [
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
  public function deleteResource(string $resourceType, $id) {
    return $this->client->delete(BuildsUris::buildResourceRequestUri($resourceType, $id));
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
  public function appendToManyRelation(string $resourceType, $id, string $relationName, array $data) {
    return $this->client->post(BuildsUris::buildRelationshipRequestUri($resourceType, $id, $relationName), [
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
  public function replaceToManyRelation(string $resourceType, $id, string $relationName, array $data) {
    return $this->client->patch(BuildsUris::buildRelationshipRequestUri($resourceType, $id, $relationName), [
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
  public function deleteFromToManyRelation(string $resourceType, $id, string $relationName, array $data) {
    return $this->client->delete(BuildsUris::buildRelationshipRequestUri($resourceType, $id, $relationName), [
      'json' => $data,
    ]);
  }

  /**
   * Proxy calls to the underlying Guzzle client.
   *
   * @param $name
   * @param $arguments
   *
   * @throws NotAuthenticatedException
   *
   * @return mixed
   */
  public function __call($name, $arguments) {
    if (!isset($this->token)) {
      throw new NotAuthenticatedException('Error: Need to authenticate before making API calls');
    }

    return $this->client->{$name}(...$arguments);
  }
}
