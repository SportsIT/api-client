<?php

namespace Dash\Builders;

abstract class BaseRelatedRequestBuilder extends BaseRelationRequestBuilder {
  public function getUri(): string {
    return $this->single->getUri()."/{$this->relationshipName}?{$this->getParameters()}";
  }
}
