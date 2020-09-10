<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\ErrorCollection;

class ErrorCollectionParser {
  /**
   * @var ErrorParser
   */
  private $errorParser;

  /**
   * @param ErrorParser $errorParser
   */
  public function __construct(ErrorParser $errorParser) {
    $this->errorParser = $errorParser;
  }

  /**
   * @param mixed $data
   *
   * @return ErrorCollection
   */
  public function parse($data): ErrorCollection {
    if (!is_array($data)) {
      throw new ValidationException(sprintf('ErrorCollection MUST be an array, "%s" given.', gettype($data)));
    }

    if (count($data) === 0) {
      throw new ValidationException('ErrorCollection cannot be empty and MUST have at least one Error object.');
    }

    return new ErrorCollection(
      array_map(
        function ($error) {
          return $this->errorParser->parse($error);
        },
        $data
      )
    );
  }
}
