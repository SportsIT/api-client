<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Meta;
use Dash\Parsers\MetaParser;
use PHPUnit\Framework\TestCase;

class MetaParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_data_to_meta() {
    $parser = new MetaParser();
    $meta = $parser->parse($this->getMeta());

    $this->assertInstanceOf(Meta::class, $meta);
    $this->assertCount(1, $meta->toArray());
    $this->assertEquals(new Meta(['copyright' => 'Copyright 2015 Example Corp.']), $meta);
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_data_is_not_an_object($invalidData) {
    $parser = new MetaParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Meta MUST be an object, "%s" given.', gettype($invalidData)));

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
   * @return \stdClass
   */
  protected function getMeta() {
    $data = [
      'copyright' => 'Copyright 2015 Example Corp.',
    ];

    return json_decode(json_encode($data), false);
  }
}
