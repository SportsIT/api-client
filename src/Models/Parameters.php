<?php

namespace Dash\Models;

use Dash\Concerns\HasFilters;
use Dash\Utils\SortDirections;
use Illuminate\Contracts\Support\Arrayable;

class Parameters implements Arrayable {
  use HasFilters;

  protected $includePaths = [];
  protected $fields = [];
  protected $sorts = [];
  protected $page = [];
  protected $custom = [];

  /**
   * @param string|string[] $includePaths
   *
   * @return $this
   */
  public function withIncludePaths($includePaths) {
    $this->includePaths = is_array($includePaths) ? $includePaths : func_get_args();

    return $this;
  }

  /**
   * @param string   $resourceType
   * @param string[] $fields
   *
   * @return $this
   */
  public function withFields($resourceType, $fields) {
    $this->fields[$resourceType] = $fields;

    return $this;
  }

  /**
   * @param string $field
   * @param string $direction
   *
   * @return $this
   */
  public function withSort($field, $direction = SortDirections::ASC) {
    if (!in_array($direction, SortDirections::AVAILABLE_DIRECTIONS)) {
      throw new \InvalidArgumentException("Invalid sort direction: {$direction}");
    }

    $this->sorts[] = "{$direction}{$field}";

    return $this;
  }

  /**
   * @param string $name
   * @param mixed  $value
   *
   * @return $this
   */
  public function withCustomParameter($name, $value) {
    $this->custom[$name] = $value;

    return $this;
  }

  /**
   * @return int
   */
  public function getPageSize() {
    return isset($this->page['size']) ? $this->page['size'] : 15;
  }

  /**
   * @return int
   */
  public function getPageNumber() {
    return isset($this->page['number']) ? $this->page['number'] : 1;
  }

  /**
   * @param int $pageSize
   *
   * @return $this
   */
  public function setPageSize($pageSize) {
    $this->page['size'] = $pageSize;

    return $this;
  }

  /**
   * @param int $pageNumber
   *
   * @return $this
   */
  public function setPageNumber($pageNumber) {
    $this->page['number'] = $pageNumber;

    return $this;
  }

  public function toArray() {
    $parameters = [];

    if (isset($this->page)) {
      $parameters['page'] = $this->page;
    }

    if (!empty($this->filters)) {
      $parameters['filter'] = $this->getFilters();
    }

    if (!empty($this->includePaths)) {
      $parameters['include'] = $this->buildIncludes();
    }

    if (!empty($this->fields)) {
      $parameters['fields'] = $this->buildFieldsArray();
    }

    if (!empty($this->sorts)) {
      $parameters['sort'] = $this->buildSorts();
    }

    if (!empty($this->custom)) {
      $parameters = array_merge($parameters, $this->custom);
    }

    return $parameters;
  }

  public function __toString(): string {
    return http_build_query($this->toArray());
  }

  protected function buildIncludes() {
    return implode(',', $this->includePaths);
  }

  protected function buildFieldsArray() {
    $fields = [];

    foreach ($this->fields as $resourceType => $field) {
      $fields[$resourceType] = implode(',', $field);
    }

    return $fields;
  }

  protected function buildSorts() {
    return implode(',', $this->sorts);
  }
}
