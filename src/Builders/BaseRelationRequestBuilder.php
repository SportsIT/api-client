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
   * @var bool
   */
  protected $onlyIdentifiers = false;

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

  /**
   * @return bool
   */
  public function isIdentifiersOnly(): bool {
    return $this->onlyIdentifiers;
  }

  /**
   * @param bool $onlyIdentifiers
   *
   * @return $this
   */
  public function onlyIdentifiers(bool $onlyIdentifiers = true) {
    $this->onlyIdentifiers = $onlyIdentifiers;

    return $this;
  }

  public function getUri(): string {
    return $this->single->getUri().($this->isIdentifiersOnly() ? '/relationships' : '')."/{$this->relationshipName}";
  }
}
