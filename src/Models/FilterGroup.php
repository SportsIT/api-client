<?php

namespace Dash\Models;

use Dash\Concerns\HasFilters;
use Illuminate\Contracts\Support\Arrayable;

class FilterGroup implements Arrayable {
  use HasFilters;

  const OPERATOR_AND = 'and';
  const OPERATOR_OR = 'or';

  protected $operator;

  public function __construct($operator = self::OPERATOR_AND) {
    if (!in_array($operator, [static::OPERATOR_AND, static::OPERATOR_OR])) {
      throw new \InvalidArgumentException("Invalid filter group operator: {$operator}");
    }

    $this->operator = $operator;
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

    list($operator, $value) = $this->resolveFilterOptions($operator, $value);

    // the or operator needs to support multiple keys that are the same so
    // if the key already exists, remap both to be under numeric keys and delete old entry
    if ($this->operator === static::OPERATOR_OR && array_key_exists($this->getFilterKey($field, $operator), $this->filters)) {
      $this->filters[][$this->getFilterKey($field, $operator)] = $this->filters[$this->getFilterKey($field, $operator)];
      $this->filters[][$this->getFilterKey($field, $operator)] = $this->resolveFilterValue($operator, $value);
      unset($this->filters[$this->getFilterKey($field, $operator)]);
    } else {
      $this->filters[$this->getFilterKey($field, $operator)] = $this->resolveFilterValue($operator, $value);
    }

    return $this;
  }

  public function toArray(): array {
    return [
      $this->operator => $this->getFilters(),
    ];
  }
}
