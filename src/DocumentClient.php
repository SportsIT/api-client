<?php

namespace Dash;

use Dash\Interfaces\DocumentInterface;
use Dash\Parsers\ResponseParser;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class DocumentClient {
  /**
   * @var ClientInterface
   */
  private $client;

  /**
   * @var ResponseParser
   */
  private $parser;

  /**
   * @param ClientInterface $client
   * @param ResponseParser  $parser
   */
  public function __construct(ClientInterface $client, ResponseParser $parser) {
    $this->client = $client;
    $this->parser = $parser;
  }

  /**
   * @param string $endpoint
   * @param array  $queryParameters
   * @param array  $headers
   *
   * @return DocumentInterface
   */
  public function get(string $endpoint, array $queryParameters = [], array $headers = []): DocumentInterface {
    return $this->parseResponse($this->client->get($endpoint, [
      'query' => $queryParameters,
      'headers' => $headers,
    ]));
  }

  /**
   * @param string                 $endpoint
   * @param DocumentInterface|null $body
   * @param array                  $queryParameters
   * @param array                  $headers
   *
   * @return DocumentInterface
   */
  public function post(string $endpoint, DocumentInterface $body = null, array $queryParameters = [], array $headers = []): DocumentInterface {
    return $this->parseResponse($this->client->post($endpoint, [
      'body' => $body === null ? $body : $this->prepareBody($body),
      'query' => $queryParameters,
      'headers' => $headers,
    ]));
  }

  /**
   * @param string                 $endpoint
   * @param DocumentInterface|null $body
   * @param array                  $queryParameters
   * @param array                  $headers
   *
   * @return DocumentInterface
   */
  public function patch(string $endpoint, DocumentInterface $body = null, array $queryParameters = [], array $headers = []): DocumentInterface {
    return $this->parseResponse($this->client->patch($endpoint, [
      'body' => $body === null ? $body : $this->prepareBody($body),
      'query' => $queryParameters,
      'headers' => $headers,
    ]));
  }

  /**
   * @param string                 $endpoint
   * @param DocumentInterface|null $body
   * @param array                  $queryParameters
   * @param array                  $headers
   *
   * @return DocumentInterface
   */
  public function delete(string $endpoint, DocumentInterface $body = null, array $queryParameters = [], array $headers = []): DocumentInterface {
    return $this->parseResponse($this->client->delete($endpoint, [
      'body' => $body === null ? $body : $this->prepareBody($body),
      'query' => $queryParameters,
      'headers' => $headers,
    ]));
  }

  /**
   * @param DocumentInterface $body
   *
   * @return string
   */
  protected function prepareBody(DocumentInterface $body): string {
    return $this->sanitizeJson(json_encode($body));
  }

  /**
   * @param string $json
   *
   * @return string
   */
  protected function sanitizeJson(string $json): string {
    return str_replace('\r\n', '\\n', $json);
  }

  /**
   * @param ResponseInterface $response
   *
   * @return DocumentInterface
   */
  protected function parseResponse(ResponseInterface $response): DocumentInterface {
    return $this->parser->parse($response);
  }
}
