<?php

namespace Dash\Parsers;

use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\DocumentParserInterface;
use Dash\Responses\Document;
use Dash\Responses\InvalidResponseDocument;
use Psr\Http\Message\ResponseInterface;

class ResponseParser {
  /**
   * @var DocumentParserInterface
   */
  private $parser;

  /**
   * @param DocumentParserInterface $parser
   */
  public function __construct(DocumentParserInterface $parser) {
    $this->parser = $parser;
  }

  /**
   * @param ResponseInterface $response
   *
   * @return DocumentInterface
   */
  public function parse(ResponseInterface $response): DocumentInterface {
    $document = new InvalidResponseDocument();

    if ($this->responseHasBody($response)) {
      $document = $this->parser->parse((string) $response->getBody());
    } elseif ($this->responseHasSuccessfulStatusCode($response)) {
      $document = new Document();
    }

    $document->setResponse($response);

    return $document;
  }

  /**
   * @param ResponseInterface $response
   *
   * @return bool
   */
  private function responseHasBody(ResponseInterface $response): bool {
    return (bool) $response->getBody()->getSize();
  }

  /**
   * @param ResponseInterface $response
   *
   * @return bool
   */
  private function responseHasSuccessfulStatusCode(ResponseInterface $response): bool {
    return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
  }
}
