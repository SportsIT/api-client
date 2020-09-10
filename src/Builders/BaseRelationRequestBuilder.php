<?php

namespace Dash\Builders;

abstract class BaseRelationRequestBuilder extends BaseRequestBuilder {
  /**
   * @var SingleResourceRequestBuilder
   */
  protected $single;

  /**
   * @var string
   */
  protected $relationshipName;

  /**
   * BaseRelationRequestBuilder constructor.
   *
   * @param SingleResourceRequestBuilder $single
   * @param string                       $relationshipName
   */
  public function __construct(SingleResourceRequestBuilder $single, string $relationshipName) {
    $this->relationshipName = $relationshipName;
    $this->single = $single;
    parent::__construct($single->getClient(), $single->getResourceType());
  }
}
