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

  public function toArray(): array {
    return [
      $this->operator => $this->getFilters(),
    ];
  }
}
