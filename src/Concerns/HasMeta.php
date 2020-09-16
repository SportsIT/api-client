<?php

namespace Dash\Concerns;

use Dash\Models\Meta;

trait HasMeta {
  /**
   * @var Meta|null
   */
  protected $meta;

  /**
   * @return Meta|null
   */
  public function getMeta(): ?Meta {
    return $this->meta;
  }

  /**
   * @param Meta|null $meta
   *
   * @return $this
   */
  public function setMeta(?Meta $meta) {
    $this->meta = $meta;

    return $this;
  }
}
