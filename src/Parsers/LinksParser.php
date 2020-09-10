<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Link;
use Dash\Models\Links;

class LinksParser {
  const SOURCE_DOCUMENT = 'document';

  const SOURCE_ERROR = 'error';

  const SOURCE_ITEM = 'item';

  const SOURCE_RELATIONSHIP = 'relationship';

  const LINKS_THAT_MAY_NOT_BE_NULL_WHEN_PRESENT = [
    'self',
    'related',
  ];

  /**
   * @var MetaParser
   */
  private $metaParser;

  /**
   * @param MetaParser $metaParser
   */
  public function __construct(MetaParser $metaParser) {
    $this->metaParser = $metaParser;
  }

  /**
   * @param mixed  $data
   * @param string $source
   *
   * @return Links
   */
  public function parse($data, string $source): Links {
    if (!is_object($data)) {
      throw new ValidationException(sprintf('Links MUST be an object, "%s" given.', gettype($data)));
    }

    if ($source === self::SOURCE_RELATIONSHIP && !property_exists($data, 'self') && !property_exists($data, 'related')) {
      throw new ValidationException('Relationship links object MUST contain at least one of the following properties: `self`, `related`.');
    }

    $links = [];

    foreach ($data as $name => $link) {
      $links[] = $this->buildLink($link, $name);
    }

    return new Links($links);
  }

  /**
   * @param mixed  $data
   * @param string $name
   *
   * @return Link|null
   */
  private function buildLink($data, string $name) {
    if (in_array($name, self::LINKS_THAT_MAY_NOT_BE_NULL_WHEN_PRESENT, true) && !is_string($data) && !is_object($data)) {
      throw new ValidationException(sprintf('Link "%s" MUST be an object or string, "%s" given.', $name, gettype($data)));
    }

    if ($data === null) {
      return null;
    }

    if (is_string($data)) {
      return new Link($data);
    }

    if (!is_object($data)) {
      throw new ValidationException(sprintf('Link "%s" MUST be an object, string or null, "%s" given.', $name, gettype($data)));
    }

    if (!property_exists($data, 'href')) {
      throw new ValidationException(sprintf('Link "%s" MUST have a "href" attribute.', $name));
    }

    return new Link($data->href, property_exists($data, 'meta') ? $this->metaParser->parse($data->meta) : null);
  }
}
