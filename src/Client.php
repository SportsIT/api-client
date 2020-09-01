<?php

namespace Dash;

use Dash\Exceptions\AuthException;
use Dash\Exceptions\NotAuthenticatedException;

/**
 * Class Client
 * @package Dash
 *
 * @mixin \GuzzleHttp\Client
 */
class Client {
  const API_BASE_URL = 'https://api.dashplatform.com/api/v1/';

  const AUTH_GRANT_TYPE = 'client_credentials';

  /**
   * @var \GuzzleHttp\Client $guzzle
   */
  private $guzzle;

  /**
   * @var Configuration $config
   */
  private $config;

  /**
   * @var string $token
   */
  private $token;

  /**
   * Client constructor.
   * @param Configuration $config
   */
  public function __construct(Configuration $config) {
    $this->config = $config;
    $this->guzzle = $this->buildGuzzleClient();
  }

  /**
   * Authenticate with the API and get an access token
   *
   * @return $this
   * @throws AuthException
   */
  public function authenticate() {
    $response = $this->guzzle->post('company/auth/token', [
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

    $this->guzzle = $this->buildGuzzleClient();

    return $this;
  }

  /**
   * Build a new Guzzle client with the current default request config
   *
   * @return \GuzzleHttp\Client
   */
  protected function buildGuzzleClient() {
    return new \GuzzleHttp\Client($this->buildGuzzleConfig());
  }

  /**
   * Build the default request config for the Guzzle client using the current state
   *
   * @return array
   */
  protected function buildGuzzleConfig() {
    $config = [
      'base_uri' => static::API_BASE_URL,
    ];

    if (isset($this->token)) {
      $config['headers'] = [
        'Authorization' => "Bearer {$this->token}",
      ];
    }

    return $config;
  }

  /**
   * @param string $resource
   * @param array $filters
   * @param array $includes
   * @param string|null $sort
   * @param PageObject|null $page
   * @param array $custom
   * @return string
   */
  public static function buildIndexRequestUri(
      $resource,
      $filters = [],
      $includes = [],
      $sort = null,
      PageObject $page = null,
      $custom = []
  ) {
    $uri = '';

    if (!empty($filters)) {
      $filtersStr = '';

      foreach ($filters as $key => $value) {
        $filtersStr = static::addParameterSeparator($filtersStr, '') . "filter[{$key}]={$value}";
      }

      $uri = "?{$filtersStr}";
    }

    if (!empty($includes)) {
      $includeStr = '';

      foreach ($includes as $include) {
        $includeStr = static::addParameterSeparator($includeStr, 'include=', ',') . $include;
      }

      $uri = static::addParameterSeparator($uri) . $includeStr;
    }

    if (isset($sort)) {
      $uri = static::addParameterSeparator($uri) . $sort;
    }

    if (isset($page)) {
      if ($page->getPageNumber() !== null) {
        $uri = static::addParameterSeparator($uri) . 'page[number]=' . $page->getPageNumber();
      }

      if ($page->getPageSize() !== null) {
        $uri = static::addParameterSeparator($uri) . 'page[size]=' . $page->getPageSize();
      }
    }

    if (!empty($custom)) {
      $customStr = '';

      foreach ($custom as $parameterName => $parameterValue) {
        $customStr = static::addParameterSeparator($customStr, "{$parameterName}=", ',') . $parameterValue;
      }

      $uri = static::addParameterSeparator($uri) . $customStr;
    }

    return "{$resource}{$uri}";
  }

  /**
   * @param string $str
   * @param string $empty
   * @param string $else
   * @return string
   */
  protected static function addParameterSeparator($str, $empty = '?', $else = '&') {
    if ($str === '') {
      $str .= $empty;
    } else {
      $str .= $else;
    }

    return $str;
  }

  /**
   * Proxy calls to the underlying Guzzle client
   *
   * @param $name
   * @param $arguments
   * @return mixed
   * @throws NotAuthenticatedException
   */
  public function __call($name, $arguments) {
    if (!isset($this->token)) {
      throw new NotAuthenticatedException('Error: Need to authenticate before making API calls');
    }

    return $this->guzzle->{$name}(...$arguments);
  }
}
