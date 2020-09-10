<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Collection;

class CollectionParser {
  /**
   * @var ItemParser
   */
  private $itemParser;

  /**
   * @param ItemParser $itemParser
   */
  public function __construct(ItemParser $itemParser) {
    $this->itemParser = $itemParser;
  }

  /**
   * @param mixed $data
   *
   * @return Collection
   */
  public function parse($data): Collection {
    if (!is_array($data)) {
      throw new ValidationException(sprintf('ResourceCollection MUST be an array, "%s" given.', gettype($data)));
    }

    return Collection::make($data)->map(
      function ($item) {
        return $this->itemParser->parse($item);
      }
    );
  }
}
