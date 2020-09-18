<?php

namespace Dash\Responses;

use Dash\Builders\BaseRequestBuilder;
use Dash\Builders\IndexRequestBuilder;
use Dash\Builders\ManyRelatedRequestBuilder;
use Dash\Interfaces\CollectionDocumentInterface;
use Dash\Models\Collection;

class CollectionDocument extends Document implements CollectionDocumentInterface, \IteratorAggregate {
  /**
   * @var Collection
   */
  protected $data;

  public function currentPageNumber(): int {
    return $this->getMeta()['page']->{'current-page'};
  }

  public function lastPageNumber(): int {
    return $this->getMeta()['page']->{'last-page'};
  }

  public function itemsPerPage(): int {
    return $this->getMeta()['page']->{'per-page'};
  }

  public function fromIndex(): ?int {
    return $this->getMeta()['page']->{'from'};
  }

  public function toIndex(): ?int {
    return $this->getMeta()['page']->{'to'};
  }

  public function totalItems(): int {
    return $this->getMeta()['page']->{'total'};
  }

  /**
   * @return Collection
   */
  public function getData(): Collection {
    return $this->data;
  }

  /**
   * @return IndexRequestBuilder|ManyRelatedRequestBuilder
   */
  public function getRequest(): BaseRequestBuilder {
    return parent::getRequest();
  }

  /**
   * Retrieve an external iterator.
   *
   * @see https://php.net/manual/en/iteratoraggregate.getiterator.php
   *
   * @throws \Exception on failure
   *
   * @return \Traversable An instance of an object implementing <b>Iterator</b> or
   *                      <b>Traversable</b>
   */
  public function getIterator() {
    return new PageIterator($this);
  }
}
