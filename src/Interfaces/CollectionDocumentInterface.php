<?php

namespace Dash\Interfaces;

use Dash\Models\Collection;

interface CollectionDocumentInterface {
  /**
   * @return Collection
   */
  public function getData();
}
