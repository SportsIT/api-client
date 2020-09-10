<?php

namespace Dash\Concerns;

use Dash\Models\FilterGroup;
use Dash\Utils\Filters;
use Illuminate\Contracts\Support\Arrayable;

trait HasFilters {
  protected $filters = [];

  /**
   * @return array
   */
  public function getFilters(): array {
    $filters = [];

    foreach ($this->getFilters() as $field => $filter) {
      if ($filter instanceof Arrayable) {
        $filters[$field] = $filter->toArray();
      } else {
        $filters[$field] = $filter;
      }
    }

    return $filters;
  }

  /**
   * @param array $filters
   *
   * @return $this
   */
  public function withFilters(array $filters) {
    foreach ($filters as $filter) {
      if (!is_array($filter)) {
        throw new \InvalidArgumentException('Each filter needs to be an array '.gettype($filter).' given');
      }

      $this->withFilter(...$filter);
    }

    return $this;
  }

  /**
   * @param array  $filters
   * @param string $operator
   *
   * @return $this
   */
  public function withFilterGroup(array $filters, string $operator = FilterGroup::OPERATOR_AND) {
    return $this->withFilter((new FilterGroup($operator))->withFilters($filters));
  }

  /**
   * @param string|array|FilterGroup $field
   * @param mixed|null               $operator
   * @param mixed|null               $value
   *
   * @return $this
   */
  public function withFilter($field, $operator = null, $value = null) {
    if (is_array($field)) {
      return $this->withFilters($field);
    }

    if ($field instanceof FilterGroup) {
      $this->filters[] = $field;

      return $this;
    }

    if (!isset($operator)) {
      throw new \InvalidArgumentException('Operator must be set when not passing in a FilterGroup object or array');
    }

    // if missing value, means operator was omitted
    if (!isset($value)) {
      $value = $operator;
      $operator = Filters::OPERATOR_EQUALS;
    }

    if (!in_array($operator, Filters::AVAILABLE_OPERATORS)) {
      throw new \InvalidArgumentException("Invalid operator: {$operator}");
    }

    switch ($operator) {
      case Filters::OPERATOR_IS_NULL:
      case Filters::OPERATOR_IS_NOT_NULL:
        $value = true;
        break;

      case Filters::OPERATOR_IN_ARRAY:
      case Filters::OPERATOR_NOT_IN_ARRAY:
        $value = is_array($value) ? implode(',', $value) : $value;
        break;
    }

    $this->filters["{$field}{$operator}"] = $value;

    return $this;
  }
}
