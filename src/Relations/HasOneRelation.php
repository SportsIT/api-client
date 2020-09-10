<?php

namespace Dash\Relations;

use Dash\Builders\BaseRelatedRequestBuilder;
use Dash\Builders\BaseRelationshipRequestBuilder;
use Dash\Builders\OneRelatedRequestBuilder;
use Dash\Builders\OneRelationshipRequestBuilder;
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
   * Save the current state of the relation
   *
   * @throws Exception
   *
   * @return \Dash\Interfaces\ItemDocumentInterface
   */
  public function save() {
    if ($this->included === null) {
      return $this->newRelatedBuilder()->dissociate();
    }

    return $this->newRelatedBuilder()->associate($this->toDocument());
  }

  /**
   * @throws Exception
   *
   * @return OneRelatedRequestBuilder
   */
  public function newRelatedBuilder(): BaseRelatedRequestBuilder {
    if ($this->parent->isNew()) {
      throw new Exception('Cannot interact with relation if parent does not exist');
    }

    return $this->parent->newBuilder()->one($this->parent->getId())->oneRelated($this->relationName);
  }

  /**
   * @throws Exception
   *
   * @return OneRelationshipRequestBuilder
   */
  public function newRelationshipBuilder(): BaseRelationshipRequestBuilder {
    if ($this->parent->isNew()) {
      throw new Exception('Cannot interact with relation if parent does not exist');
    }

    return $this->parent->newBuilder()->one($this->parent->getId())->oneRelationship($this->relationName);
  }

  /**
   * @return ItemDocument
   */
  protected function toDocument(): DocumentInterface {
    return $this->parent->getDocumentFactory()->make($this->included);
  }
}
