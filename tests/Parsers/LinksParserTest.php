<?php

namespace Dash\Tests\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Link;
use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Parsers\LinksParser;
use Dash\Parsers\MetaParser;
use PHPUnit\Framework\TestCase;

class LinksParserTest extends TestCase {
  /**
   * @test
   */
  public function it_converts_data_to_links() {
    $parser = new LinksParser(new MetaParser());
    $links = $parser->parse($this->getLinks(), LinksParser::SOURCE_DOCUMENT);

    $this->assertInstanceOf(Links::class, $links);
    $this->assertCount(4, $links->toArray());

    /** @var Link $link */
    $link = $links->self;
    $this->assertInstanceOf(Link::class, $link);
    $this->assertEquals('http://example.com/articles', $link->getHref());
    $this->assertInstanceOf(Meta::class, $link->getMeta());
    $this->assertEquals(new Meta(['copyright' => 'Copyright 2015 Example Corp.']), $link->getMeta());

    /** @var null $link */
    $link = $links->prev;
    $this->assertNull($link);

    /** @var Link $link */
    $link = $links->next;
    $this->assertInstanceOf(Link::class, $link);
    $this->assertEquals('http://example.com/articles?page[offset]=2', $link->getHref());
    $this->assertNull($link->getMeta());

    /** @var Link $link */
    $link = $links->last;
    $this->assertInstanceOf(Link::class, $link);
    $this->assertEquals('http://example.com/articles?page[offset]=10', $link->getHref());
    $this->assertNull($link->getMeta());
  }

  /**
   * @test
   * @dataProvider provideInvalidData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_links_is_not_an_object($invalidData) {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Links MUST be an object, "%s" given.', gettype($invalidData)));

    $parser->parse($invalidData, LinksParser::SOURCE_DOCUMENT);
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
   * @dataProvider provideInvalidLinkData
   *
   * @param mixed $invalidData
   */
  public function it_throws_when_link_is_not_a_string_object_or_null($invalidData) {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(sprintf('Link "foo" MUST be an object, string or null, "%s" given.', gettype($invalidData->foo)));

    $parser->parse($invalidData, LinksParser::SOURCE_DOCUMENT);
  }

  public function provideInvalidLinkData(): array {
    return [
      [json_decode('{"foo": 1}', false)],
      [json_decode('{"foo": 1.5}', false)],
      [json_decode('{"foo": false}', false)],
      [json_decode('{"foo": []}', false)],
    ];
  }

  /**
   * @test
   */
  public function it_throws_when_self_link_is_null() {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Link "self" MUST be an object or string, "NULL" given.');

    $parser->parse(json_decode('{"self": null}', false), LinksParser::SOURCE_DOCUMENT);
  }

  /**
   * @test
   */
  public function it_throws_when_related_link_is_null() {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Link "related" MUST be an object or string, "NULL" given.');

    $parser->parse(json_decode('{"related": null}', false), LinksParser::SOURCE_DOCUMENT);
  }

  /**
   * @test
   */
  public function it_throws_when_relationship_links_misses_self_and_related_links() {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Relationship links object MUST contain at least one of the following properties: `self`, `related`.');

    $parser->parse(json_decode('{}', false), LinksParser::SOURCE_RELATIONSHIP);
  }

  /**
   * @test
   */
  public function it_throws_when_link_does_not_have_href_property() {
    $parser = new LinksParser($this->createMock(MetaParser::class));

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Link "self" MUST have a "href" attribute.');

    $parser->parse(json_decode('{"self": {}}', false), LinksParser::SOURCE_DOCUMENT);
  }

  /**
   * @return \stdClass
   */
  protected function getLinks() {
    $data = [
      'self' => [
        'href' => 'http://example.com/articles',
        'meta' => [
          'copyright' => 'Copyright 2015 Example Corp.',
        ],
      ],
      'prev' => null,
      'next' => [
        'href' => 'http://example.com/articles?page[offset]=2',
      ],
      'last' => 'http://example.com/articles?page[offset]=10',
    ];

    return json_decode(json_encode($data), false);
  }
}
