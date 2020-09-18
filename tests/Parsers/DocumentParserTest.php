<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Models\Error;
use Dash\Models\ErrorCollection;
use Dash\Models\Item;
use Dash\Models\JsonApi;
use Dash\Models\Link;
use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Parsers\CollectionParser;
use Dash\Parsers\DocumentParser;
use Dash\Parsers\ErrorCollectionParser;
use Dash\Parsers\ErrorParser;
use Dash\Parsers\ItemParser;
use Dash\Parsers\JsonApiParser;
use Dash\Parsers\LinksParser;
use Dash\Parsers\MetaParser;
use Dash\Responses\CollectionDocument;
use Dash\Responses\Document;
use Dash\Responses\ItemDocument;
use PHPUnit\Framework\TestCase;

class DocumentParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_jsondocument_to_document() {
    $parser = $this->getDocumentParser();
    $document = $parser->parse(
      json_encode(
        [
          'data' => [],
        ]
      )
    );

    $this->assertInstanceOf(Document::class, $document);
  }

  /**
   * @test
   */
  public function it_throws_when_json_is_not_valid() {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Unable to parse JSON data: Malformed UTF-8 characters, possibly incorrectly encoded');

    $parser->parse("\x80");
  }

  /**
   * @test
   * @dataProvider provideInvalidJson
   *
   * @param string $invalidJson
   */
  public function it_throws_when_json_is_not_a_jsonapi_document(string $invalidJson) {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Document MUST be an object, "%s" given.', gettype(json_decode($invalidJson, false))));

    $parser->parse($invalidJson);
  }

  public function provideInvalidJson(): array {
    return [
      [json_encode(null)],
      [json_encode(1)],
      [json_encode(1.5)],
      [json_encode(false)],
      [json_encode('Foo bar')],
      [json_encode(['Foo bar'])],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_data_errors_and_meta_are_missing() {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Document MUST contain at least one of the following properties: `data`, `errors`, `meta`.');

    $parser->parse(json_encode(new \stdClass()));
  }

  /**
   * @test
   */
  public function it_throws_when_both_data_and_errors_are_present() {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('The properties `data` and `errors` MUST NOT coexist in Document.');

    $parser->parse('{"data": [], "errors": []}');
  }

  /**
   * @test
   */
  public function it_throws_when_included_is_present_but_data_is_not() {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('If Document does not contain a `data` property, the `included` property MUST NOT be present either.');

    $parser->parse('{"included": [], "errors": []}');
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_data_is_not_an_array_object_or_null($invalidData) {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Document property "data" MUST be null, an array or an object, "%s" given.', gettype(json_decode($invalidData, false)->data)));

    $parser->parse($invalidData);
  }

  public function provideInvalidData(): array {
    return [
      ['{"data": 1}'],
      ['{"data": 1.5}'],
      ['{"data": false}'],
      ['{"data": "foo"}'],
    ];
  }

  /**
   * @test
   * @dataProvider provideInvalidIncluded
   *
   * @param mixed $invalidIncluded
   */
  public function it_throws_when_included_is_not_an_array($invalidIncluded) {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Document property "included" MUST be an array, "%s" given.', gettype(json_decode($invalidIncluded, false)->included)));

    $parser->parse($invalidIncluded);
  }

  public function provideInvalidIncluded(): array {
    return [
      ['{"data": [], "included": null}'],
      ['{"data": [], "included": 1}'],
      ['{"data": [], "included": 1.5}'],
      ['{"data": [], "included": false}'],
      ['{"data": [], "included": "foo"}'],
      ['{"data": [], "included": {}}'],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_it_finds_duplicate_resources() {
    $parser = $this->getDocumentParser();

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Resources MUST be unique based on their `type` and `id`, 1 duplicate(s) found.');

    $parser->parse(
      json_encode(
        [
          'data' => [
            [
              'type' => 'master',
              'id' => '1',
              'attributes' => [
                'foo' => 'bar',
              ],
            ],
          ],
          'included' => [
            [
              'type' => 'master',
              'id' => '1',
              'attributes' => [
                'foo' => 'bar',
              ],
            ],
          ],
        ]
      )
    );
  }

  /**
   * @test
   */
  public function it_parses_a_resource_document() {
    $parser = $this->getDocumentParser();
    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            'type' => 'master',
            'id' => '1',
            'attributes' => [
              'foo' => 'bar',
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(ItemDocument::class, $document);
    $this->assertInstanceOf(ItemInterface::class, $document->getData());
    $this->assertEquals('master', $document->getData()->getType());
    $this->assertEquals('1', $document->getData()->getId());
  }

  /**
   * @test
   */
  public function it_parses_a_resource_collection_document() {
    $parser = $this->getDocumentParser();
    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            [
              'type' => 'master',
              'id' => '1',
              'attributes' => [
                'foo' => 'bar',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(CollectionDocument::class, $document);
    $this->assertInstanceOf(Collection::class, $document->getData());
    $this->assertCount(1, $document->getData());
    $this->assertEquals('master', $document->getData()->get(0)->getType());
    $this->assertEquals('1', $document->getData()->get(0)->getId());
  }

  /**
   * @test
   */
  public function it_parses_a_document_without_data() {
    $parser = $this->getDocumentParser();
    $document = $parser->parse(
      json_encode(
        [
          'meta' => [
            'foo' => 'bar',
          ],
        ]
      )
    );

    $this->assertInstanceOf(Document::class, $document);
  }

  /**
   * @test
   */
  public function it_parses_included() {
    $parser = $this->getDocumentParser();
    $document = $parser->parse(
      json_encode(
        [
          'data' => [],
          'included' => [
            [
              'type' => 'master',
              'id' => '1',
              'attributes' => [
                'foo' => 'bar',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(CollectionDocument::class, $document);
    $this->assertInstanceOf(Collection::class, $document->getIncluded());
    $this->assertCount(1, $document->getIncluded());
    $this->assertEquals('master', $document->getIncluded()->get(0)->getType());
    $this->assertEquals('1', $document->getIncluded()->get(0)->getId());
  }

  /**
   * @test
   */
  public function it_links_singular_relations_to_items_from_included() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            'type' => 'master',
            'id' => '1',
            'attributes' => [
              'foo' => 'bar',
            ],
            'relationships' => [
              'child' => [
                'data' => [
                  'type' => 'child',
                  'id' => '1',
                ],
              ],
            ],
          ],
          'included' => [
            [
              'type' => 'child',
              'id' => '1',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(Item::class, $document->getData());
    $this->assertInstanceOf(Item::class, $document->getData()->getRelationValue('child'));
    $this->assertSame($document->getIncluded()->get(0), $document->getData()->getRelationValue('child'));
  }

  /**
   * @test
   */
  public function it_does_not_link_empty_singular_relations() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            'type' => 'master',
            'id' => '1',
            'attributes' => [
              'foo' => 'bar',
            ],
            'relationships' => [
              'child' => [
                'data' => null,
              ],
            ],
          ],
          'included' => [
            [
              'type' => 'child',
              'id' => '1',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(Item::class, $document->getData());
    $this->assertNull($document->getData()->getRelationValue('child'));
    $this->assertInstanceOf(Item::class, $document->getIncluded()->get(0));
  }

  /**
   * @test
   */
  public function it_links_plural_relations_to_items_from_included() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            'type' => 'master',
            'id' => '1',
            'attributes' => [
              'foo' => 'bar',
            ],
            'relationships' => [
              'children' => [
                'data' => [
                  [
                    'type' => 'child',
                    'id' => '1',
                  ],
                  [
                    'type' => 'child',
                    'id' => '2',
                  ],
                ],
              ],
            ],
          ],
          'included' => [
            [
              'type' => 'child',
              'id' => '1',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
            [
              'type' => 'child',
              'id' => '2',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(Item::class, $document->getData());
    $this->assertInstanceOf(Collection::class, $document->getData()->getRelationValue('children'));
    $this->assertInstanceOf(Item::class, $document->getData()->getRelationValue('children')->get(0));
    $this->assertSame($document->getIncluded()->get(0), $document->getData()->getRelationValue('children')->get(0));
    $this->assertSame($document->getIncluded()->get(1), $document->getData()->getRelationValue('children')->get(1));
  }

  /**
   * @test
   */
  public function it_does_not_link_empty_plural_relations() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [
            'type' => 'master',
            'id' => '1',
            'attributes' => [
              'foo' => 'bar',
            ],
            'relationships' => [
              'children' => [
                'data' => [],
              ],
            ],
          ],
          'included' => [
            [
              'type' => 'child',
              'id' => '1',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
            [
              'type' => 'child',
              'id' => '2',
              'attributes' => [
                'foo' => 'baz',
              ],
            ],
          ],
        ]
      )
    );

    $this->assertInstanceOf(Item::class, $document->getData());
    $this->assertInstanceOf(Collection::class, $document->getData()->getRelationValue('children'));
    $this->assertEmpty($document->getData()->getRelationValue('children'));
    $this->assertInstanceOf(Item::class, $document->getIncluded()->get(0));
    $this->assertInstanceOf(Item::class, $document->getIncluded()->get(1));
  }

  /**
   * @test
   */
  public function it_parses_links() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [],
          'links' => [
            'self' => 'http://example.com/blogs',
          ],
        ]
      )
    );

    static::assertInstanceOf(Links::class, $document->getLinks());

    static::assertEquals(new Links(['self' => new Link('http://example.com/blogs')]), $document->getLinks());
  }

  /**
   * @test
   */
  public function it_parses_errors() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'errors' => [
            [
              'id' => '1',
              'code' => 'foo_bar',
            ],
          ],
        ]
      )
    );

    static::assertInstanceOf(ErrorCollection::class, $document->getErrors());

    static::assertEquals(new ErrorCollection([new Error('1', null, null, 'foo_bar')]), $document->getErrors());
  }

  /**
   * @test
   */
  public function it_parses_meta() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [],
          'meta' => [
            'foo' => 'bar',
          ],
        ]
      )
    );

    static::assertInstanceOf(Meta::class, $document->getMeta());

    static::assertEquals(new Meta(['foo' => 'bar']), $document->getMeta());
  }

  /**
   * @test
   */
  public function it_parses_jsonapi() {
    $parser = $this->getDocumentParser();

    $document = $parser->parse(
      json_encode(
        [
          'data' => [],
          'jsonapi' => [
            'version' => '1.0',
          ],
        ]
      )
    );

    static::assertInstanceOf(Jsonapi::class, $document->getJsonapi());

    static::assertEquals(new JsonApi('1.0'), $document->getJsonapi());
  }

  private function getDocumentParser(): DocumentParser {
    $metaParser = new MetaParser();
    $linksParser = new LinksParser($metaParser);
    $itemParser = new ItemParser($linksParser, $metaParser);

    return new DocumentParser(
      $itemParser,
      new CollectionParser($itemParser),
      new ErrorCollectionParser(new ErrorParser($linksParser, $metaParser)),
      $linksParser,
      new JsonApiParser($metaParser),
      $metaParser
    );
  }
}
