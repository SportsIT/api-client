<?php

namespace Dash\Builders;

abstract class BaseRelationshipRequestBuilder extends BaseRelationRequestBuilder {
  public function getUri(): string {
    return $this->single->getUri()."/relationships/{$this->relationshipName}";
  }
}
