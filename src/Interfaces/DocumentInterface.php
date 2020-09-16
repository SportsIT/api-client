<?php

namespace Dash\Interfaces;

use Dash\Builders\BaseRequestBuilder;
use Dash\Models\Collection;
use Dash\Models\ErrorCollection;
use Dash\Models\JsonApi;
use Dash\Models\Links;
use Dash\Models\Meta;
use Psr\Http\Message\ResponseInterface;

interface DocumentInterface {
  /**
   * @return BaseRequestBuilder|null
   */
  public function getRequest(): ?BaseRequestBuilder;

  /**
   * @param BaseRequestBuilder|null $request
   * @return $this
   */
  public function setRequest(?BaseRequestBuilder $request);

  /**
   * @return \Psr\Http\Message\ResponseInterface|null
   */
  public function getResponse(): ?ResponseInterface;

  /**
   * @param \Psr\Http\Message\ResponseInterface|null $response
   *
   * @return $this
   */
  public function setResponse(?ResponseInterface $response);

  /**
   * @return DataInterface
   */
  public function getData();

  /**
   * @param DataInterface $data
   *
   * @return $this
   */
  public function setData(DataInterface $data);

  /**
   * @return ErrorCollection
   */
  public function getErrors(): ErrorCollection;

  /**
   * @param ErrorCollection $errors
   *
   * @return $this
   */
  public function setErrors(ErrorCollection $errors);

  /**
   * @return bool
   */
  public function hasErrors(): bool;

  /**
   * @return Meta|null
   */
  public function getMeta(): ?Meta;

  /**
   * @param Meta|null $meta
   *
   * @return $this
   */
  public function setMeta(?Meta $meta);

  /**
   * @return Links|null
   */
  public function getLinks(): ?Links;

  /**
   * @param Links|null $links
   *
   * @return $this
   */
  public function setLinks(?Links $links);

  /**
   * @return Collection
   */
  public function getIncluded(): Collection;

  /**
   * @param Collection $included
   *
   * @return $this
   */
  public function setIncluded(Collection $included);

  /**
   * @return Jsonapi|null
   */
  public function getJsonapi(): ?JsonApi;

  /**
   * @param JsonApi|null $jsonapi
   *
   * @return $this
   */
  public function setJsonapi(?Jsonapi $jsonapi);

  /**
   * @return array
   */
  public function toArray(): array;
}
