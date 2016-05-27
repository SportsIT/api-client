<?php
namespace DashApi\Transport\Token\JWT\Claim;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [12/29/15]
 */
class AbstractClaim implements \JsonSerializable
{
  const NAME = null; // Set on extension
  public $value;
  
  public function __construct($value) {
    $this->value = $value;
  }
  
  public function __toString() {
    return json_encode(
      [
        static::NAME => $this->value
      ]
    );
  }
  
  public function jsonSerialize() {
    return $this->value;
  }
}