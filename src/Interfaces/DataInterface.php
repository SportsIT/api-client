<?php

namespace Dash\Interfaces;

interface DataInterface {
  /**
   * Get the data as a plain json api array.
   *
   * @return array
   */
  public function toJsonApiArray(): array;
}
