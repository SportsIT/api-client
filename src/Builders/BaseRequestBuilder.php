<?php

namespace Dash\Builders;

use Dash\Concerns\HasQueryParameters;
use Dash\DocumentClient;
use Dash\Interfaces\DocumentInterface;
use Dash\Models\Parameters;

abstract class BaseRequestBuilder {
  use HasQueryParameters;

  /**
   * @var DocumentClient
   */
  protected $client;

  /**
   * @var string
   */
  protected $resourceType;

  /**
   * @var Parameters
   */
  protected $parameters;

  /**
   * The number of times to try the request.
   *
   * @var int
   */
  protected $tries = 1;

  /**
   * The number of milliseconds to wait between retries.
   *
   * @var int
   */
  protected $retryDelay = 100;

  /**
   * PendingRequest constructor.
   *
   * @param DocumentClient $client
   * @param string         $resourceType
   */
  public function __construct(DocumentClient $client, string $resourceType) {
    $this->client = $client;
    $this->resourceType = $resourceType;
    $this->parameters = new Parameters();
  }

  /**
   * Get the URI for the current request.
   *
   * @return string
   */
  abstract public function getUri(): string;

  /**
   * @return DocumentClient
   */
  public function getClient(): DocumentClient {
    return $this->client;
  }

  /**
   * @return string
   */
  public function getResourceType(): string {
    return $this->resourceType;
  }

  /**
   * Specify the number of times the request should be attempted.
   *
   * @param int $times
   * @param int $sleep
   *
   * @return $this
   */
  public function retry(int $times, int $sleep = 0) {
    $copy = clone $this;
    $copy->tries = $times;
    $copy->retryDelay = $sleep;

    return $copy;
  }

  protected function request($method, DocumentInterface $body = null) {
    return retry($this->tries, function () use ($method, $body) {
      switch ($method) {
        case 'get':
          return $this->getClient()->get($this->getUri(), $this->getParameters()->toArray());

        default:
          return $this->getClient()->{$method}($this->getUri(), $body, $this->getParameters()->toArray());
      }
    }, $this->retryDelay);
  }
}
