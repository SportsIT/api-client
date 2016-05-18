<?php
namespace DashApi\Transport\Token\Attribute;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/11/16]
 */
abstract class AbstractAttribute implements AttributeInterface
{
  const NAME = null;
  protected $value;
  
  public function __construct($value) {
    if (static::NAME === null) {
      throw new \DomainException(__CLASS__ . ' missing or bad definition for `name` property, string required');
    }
    
    if ($value === null) {
      throw new \DomainException(__CLASS__.'->'.__FUNCTION__ . ' received null value parameter');
    }
    
    $this->set($value);
  }
  
  abstract public function __toString();
  
  public function serialize() {
    return serialize($this->value);
  }
  
  public function unserialize($serialized) {
    $this->value = unserialize($serialized);
  }
  
  public function get() {
    return $this->value;
  }
  public function set($value) {
    $this->value = $value;
  }
}