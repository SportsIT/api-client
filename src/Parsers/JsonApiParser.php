<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\JsonApi;

class JsonApiParser {
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
   * @param mixed $data
   *
   * @return JsonApi
   */
  public function parse($data): JsonApi {
    if (!is_object($data)) {
      throw new ValidationException(sprintf('Jsonapi MUST be an object, "%s" given.', gettype($data)));
    }
    if (property_exists($data, 'version') && !is_string($data->version)) {
      throw new ValidationException(sprintf('Jsonapi property "version" MUST be a string, "%s" given.', gettype($data->version)));
    }

    return new Jsonapi(
      property_exists($data, 'version') ? $data->version : null,
      property_exists($data, 'meta') ? $this->metaParser->parse($data->meta) : null
    );
  }
}
