<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Models\Meta;

class MetaParser {
  /**
   * @param mixed $data
   *
   * @return Meta
   */
  public function parse($data): Meta {
    if (!is_object($data)) {
      throw new ValidationException(sprintf('Meta MUST be an object, "%s" given.', gettype($data)));
    }

    return new Meta((array) $data);
  }
}
