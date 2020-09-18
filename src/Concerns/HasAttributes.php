<?php

namespace Dash\Concerns;

trait HasAttributes {
  /**
   * @var array
   */
  protected $attributes = [];

  /**
   * @return array
   */
  public function getAttributes(): array {
    return $this->attributes;
  }

  public function hasAttribute($name): bool {
    return array_key_exists($name, $this->attributes);
  }

  public function getAttribute($name) {
    return $this->attributes[$name] ?? null;
  }

  public function setAttribute($name, $value) {
    $this->attributes[$name] = $value;

    return $this;
  }
}
