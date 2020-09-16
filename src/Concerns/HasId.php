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
  public function getId(): ?string {
    return $this->id;
  }

  /**
   * @param string|null $id
   *
   * @return $this
   */
  public function setId(?string $id) {
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
