<?php

namespace Dash;

use Dash\Builders\IndexRequestBuilder;
use Dash\Concerns\BuildsUris;
use Dash\Concerns\MakesJsonApiRequests;
use Dash\Exceptions\AuthException;
use Dash\Exceptions\NotAuthenticatedException;
use Dash\Models\Item;

/**
 * Class Client.
 *
 * @mixin \GuzzleHttp\Client
 */
class Client {
  use BuildsUris;
  use MakesJsonApiRequests;

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

    $body = json_decode($response->getBody()->getContents(), true);

    if ($response->getStatusCode() !== 200 || $body['auth'] === false) {
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
