<?php

namespace Dash\Builders;

use Dash\Interfaces\ItemDocumentInterface;
use Dash\Responses\CollectionDocument;

class IndexRequestBuilder extends BaseRequestBuilder {
  /**
   * Get the URI for the current request.
   *
   * @return string
   */
  public function getUri(): string {
    return "{$this->resourceType}";
  }

  /**
   * Performs a get on the current resource.
   *
   * @throws \Exception
   *
   * @return CollectionDocument
   */
  public function search(): CollectionDocument {
    return $this->request('get');
  }

  /**
   * Performs a create with the given data.
   *
   * @param ItemDocumentInterface $item
   *
   * @throws \Exception
   *
   * @return ItemDocumentInterface
   */
  public function create(ItemDocumentInterface $item): ItemDocumentInterface {
    return $this->request('post', $item);
  }

  /**
   * Get a builder to access a single resource.
   *
   * @param string|int $id
   *
   * @return SingleResourceRequestBuilder
   */
  public function one($id): SingleResourceRequestBuilder {
    return new SingleResourceRequestBuilder(clone $this, $id);
  }
}
