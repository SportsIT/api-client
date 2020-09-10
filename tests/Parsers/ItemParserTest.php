<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Models\Item;
use Dash\Models\Link;
use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Parsers\ItemParser;
use Dash\Parsers\LinksParser;
use Dash\Parsers\MetaParser;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;
use PHPUnit\Framework\TestCase;

class ItemParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_data_to_item() {
    $parser = $this->getItemParser();
    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(ItemInterface::class, $item);

    static::assertEquals('master', $item->getType());
    static::assertEquals('1', $item->getId());
    $object = new \stdClass();
    $object->foo = 'bar';
    static::assertEquals(
      [
        'description' => 'test',
        'active' => true,
        'object' => $object,
        'array' => [1, 2, 3],
      ],
      $item->getAttributes()
    );
    static::assertInstanceOf(Links::class, $item->getLinks());
    static::assertInstanceOf(Meta::class, $item->getMeta());
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_data_is_not_an_object($invalidData) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Resource MUST be an object, "%s" given.', gettype($invalidData)));

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
   */
  public function it_throws_when_item_does_not_have_type_property() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Resource object MUST contain a type.');

    $parser->parse(json_decode('{"id": "foo"}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_item_does_not_have_id_property() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Resource object MUST contain an id.');

    $parser->parse(json_decode('{"type": "foo"}', false));
  }

  /**
   * @test
   * @dataProvider provideInvalidIdItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_id_is_not_a_string($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Resource property "id" MUST be a string, "%s" given.', gettype($invalidItem->id)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidIdItem(): array {
    return [
      [json_decode('{"type": "foo", "id": false}', false)],
      [json_decode('{"type": "foo", "id": null}', false)],
      [json_decode('{"type": "foo", "id": []}', false)],
      [json_decode('{"type": "foo", "id": {}}', false)],
    ];
  }

  /**
   * @test
   * @dataProvider provideInvalidTypeItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_type_is_not_a_string($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Resource property "type" MUST be a string, "%s" given.', gettype($invalidItem->type)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidTypeItem(): array {
    return [
      [json_decode('{"id": "foo", "type": 1}', false)],
      [json_decode('{"id": "foo", "type": 1.5}', false)],
      [json_decode('{"id": "foo", "type": false}', false)],
      [json_decode('{"id": "foo", "type": null}', false)],
      [json_decode('{"id": "foo", "type": []}', false)],
      [json_decode('{"id": "foo", "type": {}}', false)],
    ];
  }

  /**
   * @test
   * @dataProvider provideInvalidAttributesItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_attributes_is_not_an_object($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Resource property "attributes" MUST be an object, "%s" given.', gettype($invalidItem->attributes)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidAttributesItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "attributes": 1}', false)],
      [json_decode('{"id": "foo", "type": "foo", "attributes": 1.5}', false)],
      [json_decode('{"id": "foo", "type": "foo", "attributes": false}', false)],
      [json_decode('{"id": "foo", "type": "foo", "attributes": null}', false)],
      [json_decode('{"id": "foo", "type": "foo", "attributes": []}', false)],
      [json_decode('{"id": "foo", "type": "foo", "attributes": "foo"}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_type_is_present_in_attributes() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in attributes: `type`, `id`, `relationships`, `links`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "attributes": {"type": null}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_id_is_present_in_attributes() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in attributes: `type`, `id`, `relationships`, `links`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "attributes": {"id": null}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_relationships_is_present_in_attributes() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in attributes: `type`, `id`, `relationships`, `links`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "attributes": {"relationships": null}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_links_is_present_in_attributes() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in attributes: `type`, `id`, `relationships`, `links`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "attributes": {"links": null}}', false));
  }

  /**
   * @test
   * @dataProvider provideInvalidRelationshipsItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_relationships_is_not_an_object($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Resource property "relationships" MUST be an object, "%s" given.', gettype($invalidItem->relationships)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidRelationshipsItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "relationships": 1}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": 1.5}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": false}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": null}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": []}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": "foo"}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_type_is_present_in_relationships() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in relationships: `type`, `id`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "relationships": {"type": null}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_id_is_present_in_relationships() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('These properties are not allowed in relationships: `type`, `id`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "relationships": {"id": null}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_property_is_present_in_both_attributes_and_relationships() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Relationship "foo" cannot be set because it already exists in Resource object.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "attributes": {"foo": "bar"}, "relationships": {"foo": "bar"}}', false));
  }

  /**
   * @test
   * @dataProvider provideInvalidRelationshipsItemItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_relationships_item_is_not_an_object($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Relationship MUST be an object, "%s" given.', gettype($invalidItem->relationships->foo)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidRelationshipsItemItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": 1}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": 1.5}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": false}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": null}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": []}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": "foo"}}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_relationships_item_misses_links_data_and_meta() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Relationship object MUST contain at least one of the following properties: `links`, `data`, `meta`.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {}}}', false));
  }

  /**
   * @test
   * @dataProvider provideInvalidRelationshipsItemIdentifierItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_relationships_item_identifier_is_not_an_object_array_or_null($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('ResourceIdentifier MUST be an object, "%s" given.', gettype($invalidItem->relationships->foo->data)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidRelationshipsItemIdentifierItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": 1}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": 1.5}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": false}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": "foo"}}}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_relationships_item_identifier_does_not_have_type_property() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('ResourceIdentifier object MUST contain a type.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo"}}}}', false));
  }

  /**
   * @test
   */
  public function it_throws_when_relationships_item_identifier_does_not_have_id_property() {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('ResourceIdentifier object MUST contain an id.');

    $parser->parse(json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"type": "foo"}}}}', false));
  }

  /**
   * @test
   * @dataProvider provideInvalidRelationshipsItemIdentifierIdItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_relationships_item_identifier_id_is_not_a_string($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('ResourceIdentifier property "id" MUST be a string, "%s" given.', gettype($invalidItem->relationships->foo->data->id)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidRelationshipsItemIdentifierIdItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"type": "foo", "id": false}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"type": "foo", "id": null}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"type": "foo", "id": []}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"type": "foo", "id": {}}}}}', false)],
    ];
  }

  /**
   * @test
   * @dataProvider provideInvalidRelationshipsItemIdentifierTypeItem
   *
   * @param mixed $invalidItem
   */
  public function it_throws_when_relationships_item_identifier_type_is_not_a_string($invalidItem) {
    $parser = $this->getItemParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('ResourceIdentifier property "type" MUST be a string, "%s" given.', gettype($invalidItem->relationships->foo->data->type)));

    $parser->parse($invalidItem);
  }

  public function provideInvalidRelationshipsItemIdentifierTypeItem(): array {
    return [
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": 1}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": 1.5}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": false}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": null}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": []}}}}', false)],
      [json_decode('{"id": "foo", "type": "foo", "relationships": {"foo": {"data": {"id": "foo", "type": {}}}}}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_parses_a_has_one_relationship() {
    $parser = $this->getItemParser();

    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(HasOneRelation::class, $item->getRelation('child'));
    static::assertInstanceOf(Links::class, $item->getRelation('child')->getLinks());
    static::assertInstanceOf(Meta::class, $item->getRelation('child')->getMeta());

    static::assertInstanceOf(Item::class, $item->getRelation('child')->getIncluded());
    static::assertEquals('child', $item->getRelation('child')->getIncluded()->getType());
    static::assertEquals('2', $item->getRelation('child')->getIncluded()->getId());
  }

  /**
   * @test
   */
  public function it_parses_an_empty_has_one_relationship() {
    $parser = $this->getItemParser();

    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(HasOneRelation::class, $item->getRelation('empty'));
    static::assertNull($item->getRelation('empty')->getLinks());
    static::assertNull($item->getRelation('empty')->getMeta());
    static::assertTrue($item->getRelation('empty')->hasIncluded());
    static::assertNull($item->getRelation('empty')->getIncluded());
  }

  /**
   * @test
   */
  public function it_parses_a_has_many_relationship() {
    $parser = $this->getItemParser();

    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(HasManyRelation::class, $item->getRelation('children'));
    static::assertInstanceOf(Links::class, $item->getRelation('children')->getLinks());
    static::assertInstanceOf(Meta::class, $item->getRelation('children')->getMeta());

    static::assertInstanceOf(Collection::class, $item->getRelation('children')->getIncluded());
    static::assertCount(2, $item->getRelation('children')->getIncluded());
    static::assertEquals('child', $item->getRelation('children')->getIncluded()->get(0)->getType());
    static::assertEquals('3', $item->getRelation('children')->getIncluded()->get(0)->getId());
    static::assertEquals('child', $item->getRelation('children')->getIncluded()->get(1)->getType());
    static::assertEquals('4', $item->getRelation('children')->getIncluded()->get(1)->getId());
  }

  /**
   * @test
   */
  public function it_parses_links() {
    $parser = $this->getItemParser();

    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(Links::class, $item->getLinks());

    static::assertEquals(new Links(['self' => new Link('http://example.com/master/1')]), $item->getLinks());
  }

  /**
   * @test
   */
  public function it_parses_meta() {
    $parser = $this->getItemParser();

    $item = $parser->parse($this->getJsonApiItemMock('master', '1'));

    static::assertInstanceOf(Meta::class, $item->getMeta());

    static::assertEquals(new Meta(['foo' => 'bar']), $item->getMeta());
  }

  /**
   * @return ItemParser
   */
  private function getItemParser(): ItemParser {
    return new ItemParser(
      new LinksParser(new MetaParser()),
      new MetaParser()
    );
  }

  /**
   * @param $type
   * @param $id
   *
   * @return mixed
   */
  private function getJsonApiItemMock($type, $id) {
    $data = [
      'type' => $type,
      'id' => $id,
      'attributes' => [
        'description' => 'test',
        'active' => true,
        'object' => [
          'foo' => 'bar',
        ],
        'array' => [
          1,
          2,
          3,
        ],
      ],
      'relationships' => [
        'child' => [
          'data' => [
            'type' => 'child',
            'id' => '2',
          ],
          'links' => [
            'self' => 'http://example.com/'.$type.'/'.$id.'/relationships/child',
          ],
          'meta' => [
            'foo' => 'bar',
          ],
        ],
        'children' => [
          'data' => [
            [
              'type' => 'child',
              'id' => '3',
            ],
            [
              'type' => 'child',
              'id' => '4',
            ],
          ],
          'links' => [
            'self' => 'http://example.com/'.$type.'/'.$id.'/relationships/children',
          ],
          'meta' => [
            'foo' => 'bar',
          ],
        ],
        'morph' => [
          'data' => [
            'type' => 'child',
            'id' => '5',
          ],
          'links' => [
            'self' => 'http://example.com/'.$type.'/'.$id.'/relationships/morph',
          ],
          'meta' => [
            'foo' => 'bar',
          ],
        ],
        'morphmany' => [
          'data' => [
            [
              'type' => 'child',
              'id' => '6',
            ],
            [
              'type' => 'child',
              'id' => '7',
            ],
            [
              'type' => 'child',
              'id' => '8',
            ],
          ],
          'links' => [
            'self' => 'http://example.com/'.$type.'/'.$id.'/relationships/morphmany',
          ],
          'meta' => [
            'foo' => 'bar',
          ],
        ],
        'empty' => [
          'data' => null,
        ],
        'nodata' => [
          'links' => [
            'self' => 'http://example.com/'.$type.'/'.$id.'/relationships/nodata',
          ],
          'meta' => [
            'foo' => 'bar',
          ],
        ],
      ],
      'links' => [
        'self' => 'http://example.com/master/1',
      ],
      'meta' => [
        'foo' => 'bar',
      ],
    ];

    return json_decode(json_encode($data), false);
  }
}
