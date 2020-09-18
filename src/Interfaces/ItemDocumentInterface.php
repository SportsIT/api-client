<?php

namespace Dash\Interfaces;

interface ItemDocumentInterface extends DocumentInterface {
  /**
   * @return ItemInterface
   */
  public function getData();
}
