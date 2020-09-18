<?php

namespace Dash\Responses;

class PageIterator implements \Iterator {
  /**
   * @var CollectionDocument
   */
  protected $originalResponse;

  /**
   * @var CollectionDocument[]
   */
  protected $retrievedPages = [];

  /**
   * @var int
   */
  protected $currentPageNumber;

  /**
   * @var CollectionDocument
   */
  protected $mostRecentResponse;

  /**
   * PageIterator constructor.
   *
   * @param CollectionDocument $originalResponse
   */
  public function __construct(CollectionDocument $originalResponse) {
    $this->originalResponse = $originalResponse;
    $this->mostRecentResponse = $this->originalResponse;
    $this->currentPageNumber = $originalResponse->getRequest()->getParameters()->getPageNumber();
    $this->retrievedPages[$this->currentPageNumber] = $this->originalResponse;
  }

  /**
   * Retrieve the given page: First, checking pages that have already been retrieved.
   *
   * @param $pageNumber
   *
   * @throws \Exception
   *
   * @return CollectionDocument
   */
  public function getPage($pageNumber): CollectionDocument {
    if (!isset($this->retrievedPages[$pageNumber])) {
      $copy = $this->mostRecentResponse->getRequest()->setPageNumber($pageNumber);
      $this->retrievedPages[$pageNumber] = $copy->search();
      $this->mostRecentResponse = $this->retrievedPages[$pageNumber];
    }

    return $this->retrievedPages[$pageNumber];
  }

  /**
   * @throws \Exception
   *
   * @return CollectionDocument
   */
  public function current(): CollectionDocument {
    return $this->getPage($this->currentPageNumber);
  }

  public function next() {
    ++$this->currentPageNumber;
  }

  /**
   * @return int
   */
  public function key(): int {
    return $this->currentPageNumber;
  }

  /**
   * @return bool
   */
  public function valid(): bool {
    return $this->mostRecentResponse->lastPageNumber() > $this->currentPageNumber;
  }

  public function rewind() {
    $this->currentPageNumber = 1;
  }
}
