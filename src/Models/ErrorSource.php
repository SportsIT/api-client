<?php

namespace Dash\Models;

class ErrorSource {
  /**
   * @var string|null
   */
  protected $pointer;

  /**
   * @var string|null
   */
  protected $parameter;

  /**
   * @param string|null $pointer
   * @param string|null $parameter
   */
  public function __construct(string $pointer = null, string $parameter = null) {
    $this->pointer = $pointer;
    $this->parameter = $parameter;
  }

  /**
   * @return string|null
   */
  public function getPointer() {
    return $this->pointer;
  }

  /**
   * @return string|null
   */
  public function getParameter() {
    return $this->parameter;
  }
}
