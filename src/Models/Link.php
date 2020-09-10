<?php

namespace Dash\Models;

use Dash\Concerns\HasMeta;

class Link {
  use HasMeta;

  /**
   * @var string
   */
  protected $href;

  /**
   * @param string    $href
   * @param Meta|null $meta
   */
  public function __construct(string $href, Meta $meta = null) {
    $this->href = $href;
    $this->meta = $meta;
  }

  /**
   * @return string
   */
  public function getHref(): string {
    return $this->href;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  public function toArray() {
    $array = [
      'href' => $this->getHref(),
    ];

    if ($this->getMeta() !== null) {
      $array['meta'] = $this->getMeta()->toArray();
    }

    return $array;
  }
}
