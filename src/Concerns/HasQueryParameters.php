<?php

namespace Dash\Concerns;

use Dash\Models\FilterGroup;
use Dash\Models\Parameters;
use Dash\Utils\SortDirections;

trait HasQueryParameters {
  /**
   * @return Parameters
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * @param Parameters $parameters
   *
   * @return $this
   */
  public function withParameters(Parameters $parameters) {
    $this->parameters = $parameters;

    return $this;
  }

  /**
   * Applies one or more filters to the current request.
   *
   * @param string|array|FilterGroup $field
   * @param mixed|null               $operator
   * @param mixed|null               $value
   *
   * @return $this
   */
  public function where($field, $operator = null, $value = null) {
    $this->parameters->withFilter($field, $operator, $value);

    return $this;
  }

  /**
   * Applies a filter group to the current request.
   *
   * @param array  $filters
   * @param string $operator
   *
   * @return $this
   */
  public function whereGroup(array $filters, string $operator = FilterGroup::OPERATOR_AND) {
    $this->parameters->withFilterGroup($filters, $operator);

    return $this;
  }

  /**
   * Sets include paths for the current request.
   *
   * @param string|string[] $includePaths
   *
   * @return $this
   */
  public function including($includePaths) {
    $this->parameters->withIncludePaths(is_array($includePaths) ? $includePaths : func_get_args());

    return $this;
  }

  /**
   * Sets fields to be returned for a given resource type in the current request.
   *
   * @param string   $resourceType
   * @param string[] $fields
   *
   * @return $this
   */
  public function fields(string $resourceType, array $fields) {
    $this->parameters->withFields($resourceType, $fields);

    return $this;
  }

  /**
   * Adds a sort to the current request.
   *
   * @param string $field
   * @param string $direction
   *
   * @return $this
   */
  public function sort(string $field, string $direction = SortDirections::ASC) {
    $this->parameters->withSort($field, $direction);

    return $this;
  }

  /**
   * Adds a custom query parameter to the current request.
   *
   * @param string $name
   * @param mixed  $value
   *
   * @return $this
   */
  public function customParameter(string $name, $value) {
    $this->parameters->withCustomParameter($name, $value);

    return $this;
  }

  /**
   * Sets the page size for the current request.
   *
   * @param int $pageSize
   *
   * @return $this
   */
  public function setPageSize(int $pageSize) {
    $this->parameters->setPageSize($pageSize);

    return $this;
  }

  /**
   * Sets the page number for the current request.
   *
   * @param int $pageNumber
   *
   * @return $this
   */
  public function setPageNumber(int $pageNumber) {
    $this->parameters->setPageNumber($pageNumber);

    return $this;
  }
}
