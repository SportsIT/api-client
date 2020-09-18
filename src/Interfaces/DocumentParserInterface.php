<?php

namespace Dash\Interfaces;

interface DocumentParserInterface {
  /**
   * @param string $json
   *
   * @return DocumentInterface
   */
  public function parse(string $json): DocumentInterface;
}
