<?php

namespace Dash\Relations;

use Dash\Builders\BaseRelationRequestBuilder;
use Dash\Builders\ManyRelatedRequestBuilder;
use Dash\Interfaces\DocumentInterface;
use Dash\Models\Collection;
use Dash\Responses\CollectionDocument;
use Exception;

class HasManyRelation extends AbstractRelation {
  /**
   * @var Collection|false|null
   */
  protected $included = false;

  /**
   * @param Collection $included
   *
   * @return $this
   */
  public function associate(Collection $included) {
    $this->included = $included;

    return $this;
  }

  /**
   * @return Collection
   */
  public function getIncluded(): Collection {
    return $this->included ?: new Collection();
  }

  /**
   * Sort the included collection by the given key.
   * You can also pass your own callback to determine how to sort the collection values.
   *
   * @param callable $callback
   * @param int      $options
   * @param bool     $descending
   *
   * @return $this
   */
  public function sortBy($callback, $options = SORT_REGULAR, $descending = false) {
    // Included may be empty when defining the relation (on the item),
    // but will be filled when using the relation to fetch the data.
    // Checking if we have included items and applying the order is
    // simpler then keeping track of the sorts and applying them later.
    if ($this->hasIncluded()) {
      $this->included = $this->getIncluded()->sortBy($callback, $options, $descending);
    }

    return $this;
  }

  /**
   * Save the relation by adding all members in the current state.
   *
   * @throws Exception
   *
   * @return DocumentInterface|null
   */
  public function add() {
    if ($this->included === false) {
      return null;
    }

    return $this->newRelatedBuilder()->add($this->toDocument());
  }

  /**
   * Save the relation by replacing all current members with the current state.
   *
   * @throws Exception
   *
   * @return DocumentInterface|null
   */
  public function replace() {
    if ($this->included === false) {
      return null;
    }

    return $this->newRelatedBuilder()->replace($this->toDocument());
  }

  /**
   * Save the relation to have no members in it.
   *
   * @throws Exception
   *
   * @return DocumentInterface
   */
  public function clear() {
    return $this->newRelatedBuilder()->clear();
  }

  /**
   * @throws Exception
   *
   * @return ManyRelatedRequestBuilder
   */
  public function newRelatedBuilder(): BaseRelationRequestBuilder {
    if ($this->parent->isNew()) {
      throw new Exception('Cannot interact with relation if parent does not exist');
    }

    return $this->parent->newBuilder()->one($this->parent->getId())->manyRelated($this->relationName);
  }

  /**
   * @return CollectionDocument
   */
  protected function toDocument(): DocumentInterface {
    return $this->parent->getDocumentFactory()->make($this->included);
  }
}
