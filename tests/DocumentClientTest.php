<?php

namespace Dash\Tests;

use Dash\DocumentClient;
use Dash\Models\Item;
use Dash\Parsers\ResponseParser;
use Dash\Responses\Document;
use Dash\Responses\ItemDocument;
use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class DocumentClientTest extends TestCase {
  /**
   * @test
   */
  public function it_builds_a_get_request() {
    $response = $this->createMock(ResponseInterface::class);
    $document = new Document();

    /** @var MockObject|Client $client */
    $client = $this->createMock(Client::class);

    $client->expects($this->once())
      ->method('get')
      ->with('/test/1', ['X-Foo' => 'bar'])
      ->willReturn($response);

    /** @var MockObject|ResponseParser $parser */
    $parser = $this->createMock(ResponseParser::class);

    $parser->expects($this->once())
      ->method('parse')
      ->with($response)
      ->willReturn($document);

    $documentClient = new DocumentClient($client, $parser);

    $responseDocument = $documentClient->get('/test/1', ['X-Foo' => 'bar']);

    $this->assertSame($document, $responseDocument);
  }

  /**
   * @test
   */
  public function it_builds_a_delete_request() {
    $response = $this->createMock(ResponseInterface::class);
    $document = new Document();

    /** @var MockObject|Client $client */
    $client = $this->createMock(Client::class);

    $client->expects($this->once())
      ->method('delete')
      ->with('/test/1', null, [], ['X-Foo' => 'bar'])
      ->willReturn($response);

    /** @var MockObject|ResponseParser $parser */
    $parser = $this->createMock(ResponseParser::class);

    $parser->expects($this->once())
      ->method('parse')
      ->with($response)
      ->willReturn($document);

    $documentClient = new DocumentClient($client, $parser);

    $responseDocument = $documentClient->delete('/test/1', null, [], ['X-Foo' => 'bar']);

    $this->assertSame($document, $responseDocument);
  }

  /**
   * @test
   */
  public function it_builds_a_patch_request() {
    $response = $this->createMock(ResponseInterface::class);
    $document = new Document();
    $itemDocument = new ItemDocument();
    $itemDocument->setData((new Item())->setType('test')->setId('1'));

    /** @var MockObject|Client $client */
    $client = $this->createMock(Client::class);

    $client->expects($this->once())
      ->method('patch')
      ->with('/test/1', '{"data":{"type":"test","id":"1"}}', [], ['X-Foo' => 'bar'])
      ->willReturn($response);

    /** @var MockObject|ResponseParser $parser */
    $parser = $this->createMock(ResponseParser::class);

    $parser->expects($this->once())
      ->method('parse')
      ->with($response)
      ->willReturn($document);

    $documentClient = new DocumentClient($client, $parser);

    $responseDocument = $documentClient->patch('/test/1', $itemDocument, [], ['X-Foo' => 'bar']);

    $this->assertSame($document, $responseDocument);
  }

  /**
   * @test
   */
  public function it_builds_a_post_request() {
    $response = $this->createMock(ResponseInterface::class);
    $document = new Document();
    $itemDocument = new ItemDocument();
    $itemDocument->setData((new Item())->setType('test'));

    /** @var MockObject|Client $client */
    $client = $this->createMock(Client::class);

    $client->expects($this->once())
      ->method('post')
      ->with('/test/1', '{"data":{"type":"test"}}', [], ['X-Foo' => 'bar'])
      ->willReturn($response);

    /** @var MockObject|ResponseParser $parser */
    $parser = $this->createMock(ResponseParser::class);

    $parser->expects($this->once())
      ->method('parse')
      ->with($response)
      ->willReturn($document);

    $documentClient = new DocumentClient($client, $parser);

    $responseDocument = $documentClient->post('/test/1', $itemDocument, [], ['X-Foo' => 'bar']);

    $this->assertSame($document, $responseDocument);
  }
}
