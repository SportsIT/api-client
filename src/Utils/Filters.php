<?php

namespace Dash\Utils;

final class Filters {
  const OPERATOR_EQUALS = '';
  const OPERATOR_NOT_EQUALS = '__not';
  const OPERATOR_GREATER_THAN = '__gt';
  const OPERATOR_GREATER_THAN_OR_EQUAL = '__gte';
  const OPERATOR_LESS_THAN = '__lt';
  const OPERATOR_LESS_THAN_OR_EQUAL = '__lte';
  const OPERATOR_IS_NULL = '__isnull';
  const OPERATOR_IS_NOT_NULL = '__notnull';
  const OPERATOR_NULL = '__null';
  const OPERATOR_IN_ARRAY = '__in';
  const OPERATOR_NOT_IN_ARRAY = '__notin';
  const OPERATOR_REGEX = '__regex';
  const OPERATOR_STARTS_LIKE = '__starts';
  const OPERATOR_ENDS_LIKE = '__ends';
  const OPERATOR_TIME_GREATER_THAN = '__time_gt';
  const OPERATOR_TIME_GREATER_THAN_OR_EQUAL = '__time_gte';
  const OPERATOR_TIME_LESS_THAN = '__time_lt';
  const OPERATOR_TIME_LESS_THAN_OR_EQUAL = '__time_lte';

  const AVAILABLE_OPERATORS = [
    self::OPERATOR_EQUALS,
    self::OPERATOR_NOT_EQUALS,
    self::OPERATOR_GREATER_THAN,
    self::OPERATOR_GREATER_THAN_OR_EQUAL,
    self::OPERATOR_LESS_THAN,
    self::OPERATOR_LESS_THAN_OR_EQUAL,
    self::OPERATOR_IS_NULL,
    self::OPERATOR_IS_NOT_NULL,
    self::OPERATOR_NULL,
    self::OPERATOR_IN_ARRAY,
    self::OPERATOR_NOT_IN_ARRAY,
    self::OPERATOR_REGEX,
    self::OPERATOR_STARTS_LIKE,
    self::OPERATOR_ENDS_LIKE,
    self::OPERATOR_TIME_GREATER_THAN,
    self::OPERATOR_TIME_GREATER_THAN_OR_EQUAL,
    self::OPERATOR_TIME_LESS_THAN,
    self::OPERATOR_TIME_LESS_THAN_OR_EQUAL,
  ];
}
