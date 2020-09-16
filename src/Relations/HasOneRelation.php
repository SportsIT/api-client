<?php

namespace Dash\Relations;

use Dash\Builders\BaseRelationRequestBuilder;
use Dash\Builders\OneRelatedRequestBuilder;
use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Responses\ItemDocument;
use Exception;

class HasOneRelation extends AbstractRelation {
  /**
   * @var ItemInterface|false|null
   */
  protected $included = false;

  /**
   * @param ItemInterface $included
   *
   * @return $this
   */
  public function associate(ItemInterface $included) {
    $this->included = $included;

    return $this;
  }

  /**
   * @return ItemInterface|null
   */
  public function getIncluded() {
    return $this->included ?: null;
  }

  /**
   * Save the current state of the relation.
   *
   * @throws Exception
   *
   * @return \Dash\Interfaces\ItemDocumentInterface
   */
  public function save() {
    if ($this->included === null) {
      return $this->newRelatedBuilder()->dissociate();
    }

    // @phan-suppress-next-line PhanTypeMismatchArgument
    return $this->newRelatedBuilder()->associate($this->toDocument());
  }

  /**
   * @throws Exception
   *
   * @return OneRelatedRequestBuilder
   */
  public function newRelatedBuilder(): BaseRelationRequestBuilder {
    if ($this->parent->isNew()) {
      throw new Exception('Cannot interact with relation if parent does not exist');
    }

    return $this->parent->newBuilder()->one($this->parent->getId())->oneRelated($this->relationName);
  }

  /**
   * @return ItemDocument
   */
  protected function toDocument(): DocumentInterface {
    return $this->parent->getDocumentFactory()->make($this->included);
  }
}
