<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Collection;
use Dash\Models\Item;
use Dash\Parsers\CollectionParser;
use Dash\Parsers\ItemParser;
use PHPUnit\Framework\TestCase;

class CollectionParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_data_to_collection() {
    $itemParser = $this->createMock(ItemParser::class);
    $itemParser->expects($this->exactly(2))
      ->method('parse')
      ->willReturn(new Item());

    $parser = new CollectionParser($itemParser);
    $collection = $parser->parse($this->getResourceCollection());

    $this->assertInstanceOf(Collection::class, $collection);
    $this->assertEquals(2, $collection->count());

    $this->assertInstanceOf(Item::class, $collection->get(0));
    $this->assertInstanceOf(Item::class, $collection->get(1));

    $this->assertEquals('plain', $collection->get(0)->getType());
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_data_is_not_an_array($invalidData) {
    $parser = new CollectionParser($this->createMock(ItemParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('ResourceCollection MUST be an array, "%s" given.', gettype($invalidData)));

    $parser->parse($invalidData);
  }

  public function provideInvalidData(): array {
    $object = new \stdClass();
    $object->foo = 'bar';

    return [
      [1],
      [1.5],
      [false],
      [null],
      ['foo'],
      [$object],
    ];
  }

  /**
   * @return \stdClass
   */
  protected function getResourceCollection() {
    $data = [
      [
        'id' => '1',
        'type' => 'plain',
        'attributes' => [
          'foo' => 'bar',
        ],
      ],
      [
        'id' => '2',
        'type' => 'plain',
        'attributes' => [
          'foo' => 'bar',
        ],
      ],
    ];

    return json_decode(json_encode($data), false);
  }
}
