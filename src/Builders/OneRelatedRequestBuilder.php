<?php

namespace Dash\Builders;

use Dash\Interfaces\ItemDocumentInterface;
use Dash\Responses\ItemDocument;

class OneRelatedRequestBuilder extends BaseRelationRequestBuilder {
  /**
   * Performs a get on the current resource.
   *
   * @throws \Exception
   *
   * @return ItemDocument
   */
  public function get(): ItemDocument {
    return $this->request('get');
  }

  /**
   * Performs a full replace on the relationship with the given data.
   *
   * @param ItemDocumentInterface $item
   *
   * @throws \Exception
   *
   * @return ItemDocumentInterface
   */
  public function associate(ItemDocumentInterface $item): ItemDocumentInterface {
    return $this->request('patch', $item);
  }

  /**
   * Performs a delete on the relationship and removes relationship members specified in the given data.
   *
   * @throws \Exception
   *
   * @return ItemDocumentInterface
   */
  public function dissociate(): ItemDocumentInterface {
    return $this->request('patch', new ItemDocument());
  }
}
