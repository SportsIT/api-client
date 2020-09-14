<?php

namespace Dash\Relations;

use Dash\Builders\BaseRelatedRequestBuilder;
use Dash\Builders\BaseRelationshipRequestBuilder;
use Dash\Concerns\HasLinks;
use Dash\Concerns\HasMeta;
use Dash\Concerns\HasRelations;
use Dash\Interfaces\DataInterface;
use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Item;

abstract class AbstractRelation {
  use HasMeta;
  use HasLinks;

  /**
   * @var DataInterface|false|null
   */
  protected $included = false;

  /**
   * @var bool
   */
  protected $omitIncluded = false;

  /**
   * @var Item
   */
  protected $parent;

  /**
   * @var string
   */
  protected $relationName;

  /**
   * AbstractRelation constructor.
   *
   * @param ItemInterface $parent
   * @param string       $relationName
   */
  public function __construct(ItemInterface $parent, string $relationName) {
    $this->parent = $parent;
    $this->relationName = $relationName;
  }

  /**
   * @return BaseRelatedRequestBuilder
   */
  abstract public function newRelatedBuilder(): BaseRelatedRequestBuilder;

  /**
   * @return BaseRelationshipRequestBuilder
   */
  abstract public function newRelationshipBuilder(): BaseRelationshipRequestBuilder;

  abstract protected function toDocument(): DocumentInterface;

  /**
   * @return $this
   */
  public function dissociate() {
    $this->included = null;

    return $this;
  }

  /**
   * @return bool
   */
  public function hasIncluded(): bool {
    return $this->included !== false;
  }

  /**
   * @param bool $omitIncluded
   *
   * @return $this
   */
  public function setOmitIncluded(bool $omitIncluded) {
    $this->omitIncluded = $omitIncluded;

    return $this;
  }

  /**
   * @return bool
   */
  public function shouldOmitIncluded(): bool {
    return $this->omitIncluded;
  }
}
