<?php

namespace Dash\Tests\Parsers;

use Dash\Parsers\DocumentParser;
use Dash\Parsers\ResponseParser;
use Dash\Responses\CollectionDocument;
use Dash\Responses\Document;
use Dash\Responses\InvalidResponseDocument;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_psr_reponse_to_document() {
    $documentParser = $this->createMock(DocumentParser::class);
    $documentParser->expects($this->once())
      ->method('parse')
      ->willReturn(new CollectionDocument());

    $parser = new ResponseParser($documentParser);

    $response = new Response(200, [], json_encode(['data' => []]));
    $document = $parser->parse($response);

    $this->assertInstanceOf(CollectionDocument::class, $document);
    $this->assertSame($response, $document->getResponse());
  }

  /**
   * @test
   */
  public function it_parses_a_response_with_an_empty_body() {
    $documentParser = $this->createMock(DocumentParser::class);
    $documentParser->expects($this->never())
      ->method('parse');

    $parser = new ResponseParser($documentParser);

    $response = new Response(201);
    $document = $parser->parse($response);

    $this->assertInstanceOf(Document::class, $document);
    $this->assertSame($response, $document->getResponse());
  }

  /**
   * @test
   */
  public function it_parses_an_error_response() {
    $documentParser = $this->createMock(DocumentParser::class);
    $documentParser->expects($this->never())
      ->method('parse');

    $parser = new ResponseParser($documentParser);

    $response = new Response(500);
    $document = $parser->parse($response);

    $this->assertInstanceOf(InvalidResponseDocument::class, $document);
    $this->assertSame($response, $document->getResponse());
  }
}
