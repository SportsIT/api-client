<?php

namespace Dash;

use Dash\Concerns\BuildsUris;
use Dash\Concerns\MakesJsonApiRequests;
use Dash\Exceptions\AuthException;
use Dash\Exceptions\NotAuthenticatedException;

/**
 * Class Client
 * @package Dash
 *
 * @mixin \GuzzleHttp\Client
 */
class Client
{
    use BuildsUris, MakesJsonApiRequests;

    const API_BASE_URL = 'https://api.dashplatform.com/v1/';

    const AUTH_GRANT_TYPE = 'client_credentials';

    const VERSION = '2.1.0';

    const USERAGENT_FORMAT = 'DashApiClient/%s (PHP %s) GuzzleHttp/%s';

    const JSONAPI_CONTENT_TYPE = 'application/vnd.api+json';

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
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->guzzle = $this->buildGuzzleClient();
    }

    /**
     * Authenticate with the API and get an access token
     *
     * @return $this
     * @throws AuthException
     */
    public function authenticate()
    {
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
    protected function buildGuzzleClient()
    {
        return new \GuzzleHttp\Client($this->buildGuzzleConfig());
    }

    /**
     * Build the default request config for the Guzzle client using the current state
     *
     * @return array
     */
    protected function buildGuzzleConfig()
    {
        $config = [
            'base_uri' => static::API_BASE_URL,
            'headers' => [
                'User-Agent' => sprintf(static::USERAGENT_FORMAT, static::VERSION, phpversion(), \GuzzleHttp\Client::VERSION),
                'Content-Type' => static::JSONAPI_CONTENT_TYPE,
                'Accept' => static::JSONAPI_CONTENT_TYPE,
            ],
        ];

        if (isset($this->token)) {
            $config = array_merge_recursive($config, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                ]
            ]);
        }

        return $config;
    }

    /**
     * Proxy calls to the underlying Guzzle client
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws NotAuthenticatedException
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->token)) {
            throw new NotAuthenticatedException('Error: Need to authenticate before making API calls');
        }

        return $this->guzzle->{$name}(...$arguments);
    }
}
