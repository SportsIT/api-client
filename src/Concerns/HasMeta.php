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
  public function getMeta() {
    return $this->meta;
  }

  /**
   * @param Meta|null $meta
   *
   * @return $this
   */
  public function setMeta(Meta $meta = null) {
    $this->meta = $meta;

    return $this;
  }
}
