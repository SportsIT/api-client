<?php

namespace Dash;

use GuzzleHttp\ClientInterface;

class GuzzleFactory {
  const API_BASE_URL = 'https://api.dashplatform.com/v1/';

  const USERAGENT_FORMAT = 'DashApiClient/%s (PHP %s) GuzzleHttp/%s';

  const JSONAPI_CONTENT_TYPE = 'application/vnd.api+json';

  /**
   * @param string|null $accessToken
   *
   * @return ClientInterface
   */
  public function make($accessToken = null): ClientInterface {
    return new \GuzzleHttp\Client($this->buildGuzzleConfig($accessToken));
  }

  /**
   * Build the default request config for the Guzzle client using the current state.
   *
   * @param string|null $accessToken
   *
   * @return array
   */
  protected function buildGuzzleConfig($accessToken = null) {
    return [
      'base_uri' => static::API_BASE_URL,
      'headers' => $this->getDefaultHeaders($accessToken),
    ];
  }

  /**
   * @param string|null $accessToken
   *
   * @return array
   */
  protected function getDefaultHeaders($accessToken = null): array {
    return array_filter([
      'User-Agent' => sprintf(static::USERAGENT_FORMAT, Client::VERSION, phpversion(), \GuzzleHttp\Client::VERSION),
      'Content-Type' => static::JSONAPI_CONTENT_TYPE,
      'Accept' => static::JSONAPI_CONTENT_TYPE,
      'Authorization' => $accessToken ? "Bearer {$accessToken}" : null,
    ]);
  }
}
