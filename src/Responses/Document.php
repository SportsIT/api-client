<?php

namespace Dash\Responses;

use Dash\Builders\BaseRequestBuilder;
use Dash\Concerns\HasLinks;
use Dash\Concerns\HasMeta;
use Dash\Interfaces\DataInterface;
use Dash\Interfaces\DocumentInterface;
use Dash\Models\Collection;
use Dash\Models\ErrorCollection;
use Dash\Models\JsonApi;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Response.
 */
class Document implements \JsonSerializable, DocumentInterface {
  use HasLinks;
  use HasMeta;

  /**
   * @var BaseRequestBuilder|null
   */
  protected $originalRequest;

  /**
   * @var ResponseInterface|null
   */
  protected $response;

  /**
   * @var DataInterface
   */
  protected $data;

  protected $included;

  protected $errors;

  protected $jsonapi;

  public function __construct() {
    $this->included = new Collection();
    $this->errors = new ErrorCollection();
  }

  /**
   * @return BaseRequestBuilder|null
   */
  public function getRequest(): ?BaseRequestBuilder {
    return $this->originalRequest;
  }

  /**
   * @param BaseRequestBuilder|null $request
   *
   * @return $this
   */
  public function setRequest(?BaseRequestBuilder $request) {
    $this->originalRequest = $request;

    return $this;
  }

  public function getData() {
    return $this->data;
  }

  /**
   * @param DataInterface $data
   *
   * @return $this
   */
  public function setData(DataInterface $data) {
    $this->data = $data;

    return $this;
  }

  public function hasErrors(): bool {
    return $this->errors->isNotEmpty();
  }

  public function getErrors(): ErrorCollection {
    return $this->errors;
  }

  /**
   * @param ErrorCollection $errors
   *
   * @return $this
   */
  public function setErrors(ErrorCollection $errors) {
    $this->errors = $errors;

    return $this;
  }

  public function isSuccess(): bool {
    return !$this->hasErrors();
  }

  /**
   * @return Collection
   */
  public function getIncluded(): Collection {
    return $this->included;
  }

  /**
   * @param Collection $included
   *
   * @return $this
   */
  public function setIncluded(Collection $included) {
    $this->included = $included;

    return $this;
  }

  /**
   * @return ResponseInterface|null
   */
  public function getResponse(): ?ResponseInterface {
    return $this->response;
  }

  /**
   * @param ResponseInterface|null $response
   *
   * @return $this
   */
  public function setResponse(?ResponseInterface $response) {
    $this->response = $response;

    return $this;
  }

  /**
   * @return JsonApi|null
   */
  public function getJsonapi(): ?JsonApi {
    return $this->jsonapi;
  }

  /**
   * @param JsonApi|null $jsonapi
   *
   * @return $this
   */
  public function setJsonapi(?JsonApi $jsonapi) {
    $this->jsonapi = $jsonapi;

    return $this;
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $document = [];

    if ($this->getLinks() !== null) {
      $document['links'] = $this->getLinks()->toArray();
    }

    if ($this->getData() !== null) {
      $document['data'] = $this->data->toJsonApiArray();
    }

    if ($this->getIncluded()->isNotEmpty()) {
      $document['included'] = $this->getIncluded()->toJsonApiArray();
    }

    if ($this->getMeta() !== null) {
      $document['meta'] = $this->getMeta()->toArray();
    }

    if ($this->hasErrors()) {
      $document['errors'] = $this->getErrors()->toArray();
    }

    if ($this->getJsonapi() !== null) {
      $document['jsonapi'] = $this->getJsonapi()->toArray();
    }

    return $document;
  }

  /**
   * {@inheritdoc}
   *
   * @return object
   */
  public function jsonSerialize() {
    $document = [];

    if ($this->getLinks() !== null) {
      $document['links'] = $this->getLinks();
    }

    if ($this->getData() !== null) {
      $document['data'] = $this->data->toJsonApiArray();
    }

    if ($this->getIncluded()->isNotEmpty()) {
      $document['included'] = $this->getIncluded()->toJsonApiArray();
    }

    if ($this->getMeta() !== null) {
      $document['meta'] = $this->getMeta();
    }

    if ($this->hasErrors()) {
      $document['errors'] = $this->getErrors();
    }

    if ($this->getJsonapi() !== null) {
      $document['jsonapi'] = $this->getJsonapi();
    }

    return (object) $document;
  }
}
