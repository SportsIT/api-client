<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\JsonApi;
use Dash\Models\Meta;
use Dash\Parsers\JsonApiParser;
use Dash\Parsers\MetaParser;
use PHPUnit\Framework\TestCase;

class JsonApiParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_data_to_jsonapi() {
    $parser = new JsonapiParser(new MetaParser());
    $jsonapi = $parser->parse($this->getJsonapi());

    $this->assertInstanceOf(JsonApi::class, $jsonapi);
    $this->assertEquals('1.0', $jsonapi->getVersion());

    $this->assertInstanceOf(Meta::class, $jsonapi->getMeta());
    $this->assertEquals(new Meta(['copyright' => 'Copyright 2015 Example Corp.']), $jsonapi->getMeta());
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_data_is_not_an_object($invalidData) {
    $parser = new JsonapiParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Jsonapi MUST be an object, "%s" given.', gettype($invalidData)));

    $parser->parse($invalidData);
  }

  public function provideInvalidData(): array {
    return [
      [1],
      [1.5],
      [false],
      [null],
      ['foo'],
      [[]],
    ];
  }

  /**
   * @test
   * @dataProvider provideInvalidVersionJsonapi
   *
   * @param mixed $invalidJsonapi
   */
  public function it_throws_when_version_is_not_a_string($invalidJsonapi) {
    $parser = new JsonApiParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Jsonapi property "version" MUST be a string, "%s" given.', gettype($invalidJsonapi->version)));

    $parser->parse($invalidJsonapi);
  }

  public function provideInvalidVersionJsonapi(): array {
    return [
      [json_decode('{"version": 1}', false)],
      [json_decode('{"version": 1.5}', false)],
      [json_decode('{"version": false}', false)],
      [json_decode('{"version": null}', false)],
      [json_decode('{"version": []}', false)],
      [json_decode('{"version": {}}', false)],
    ];
  }

  /**
   * @return \stdClass
   */
  protected function getJsonapi() {
    $data = [
      'version' => '1.0',
      'meta' => [
        'copyright' => 'Copyright 2015 Example Corp.',
      ],
    ];

    return json_decode(json_encode($data), false);
  }
}
