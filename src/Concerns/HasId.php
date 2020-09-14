<?php

namespace Dash\Concerns;

trait HasId {
  /**
   * @var string|null
   */
  protected $id;

  /**
   * @return string|null
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param string|null $id
   *
   * @return $this
   */
  public function setId($id = null) {
    $this->id = $id;

    return $this;
  }

  /**
   * @return bool
   */
  public function hasId(): bool {
    return isset($this->id);
  }
}
