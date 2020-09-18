<?php

namespace Dash\Concerns;

use Dash\Models\Links;

trait HasLinks {
  /**
   * @var Links|null
   */
  protected $links;

  /**
   * @return Links|null
   */
  public function getLinks(): ?Links {
    return $this->links;
  }

  /**
   * @param Links|null $links
   *
   * @return $this
   */
  public function setLinks(?Links $links) {
    $this->links = $links;

    return $this;
  }
}
