<?php

namespace Dash\Builders;

use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\ItemDocumentInterface;

class SingleResourceRequestBuilder extends BaseRequestBuilder {
  /**
   * @var int|string
   */
  protected $id;

  /**
   * @var IndexRequestBuilder
   */
  protected $index;

  /**
   * SingleResourceRequestBuilder constructor.
   *
   * @param IndexRequestBuilder $index
   * @param string|int          $id
   */
  public function __construct(IndexRequestBuilder $index, $id) {
    $this->id = $id;
    $this->index = $index;
    parent::__construct($index->getClient(), $index->getResourceType());
  }

  /**
   * Get the URI for the current request.
   *
   * @return string
   */
  public function getUri(): string {
    return $this->index->getUri()."/{$this->id}";
  }

  /**
   * Get a builder to access related resources (full models) for a given has-one relationship.
   *
   * @param string $relationshipName
   *
   * @return OneRelatedRequestBuilder
   */
  public function oneRelated(string $relationshipName): OneRelatedRequestBuilder {
    return new OneRelatedRequestBuilder(clone $this, $relationshipName);
  }

  /**
   * Get a builder to access related resources (full models) for a given has-many relationship.
   *
   * @param string $relationshipName
   *
   * @return ManyRelatedRequestBuilder
   */
  public function manyRelated(string $relationshipName): ManyRelatedRequestBuilder {
    return new ManyRelatedRequestBuilder(clone $this, $relationshipName);
  }

  /**
   * Performs a get on the current resource.
   *
   * @throws \Exception
   *
   * @return ItemDocumentInterface
   */
  public function get(): ItemDocumentInterface {
    return $this->request('get');
  }

  /**
   * Performs an update against the resource, applying the given data.
   * In accordance with JsonAPI spec, any relationships included will perform a full-replacement of the existing value.
   *
   * @see https://jsonapi.org/format/#crud-updating-resource-relationships
   *
   * @param ItemDocumentInterface $item
   *
   * @throws \Exception
   *
   * @return ItemDocumentInterface
   */
  public function update(ItemDocumentInterface $item): ItemDocumentInterface {
    return $this->request('patch', $item);
  }

  /**
   * Performs a delete against the resource.
   *
   * @throws \Exception
   *
   * @return DocumentInterface
   */
  public function delete(): DocumentInterface {
    return $this->request('delete');
  }
}
