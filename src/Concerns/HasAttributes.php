<?php

namespace Dash\Concerns;

trait HasAttributes {
  /**
   * @var array
   */
  protected $attributes;

  /**
   * @return array
   */
  public function getAttributes(): array {
    return $this->attributes;
  }

  public function hasAttribute(string $name): bool {
    return array_key_exists($name, $this->attributes);
  }

  public function getAttribute(string $name) {
    return $this->attributes[$name] ?? null;
  }

  public function setAttribute(string $name, $value) {
    $this->attributes[$name] = $value;

    return $this;
  }
}
