<?php
namespace Transport\JWT\Claim;

/**
 * Class ClaimSet
 *
 * @package SIT\Transport\Token\Json\JWT\Claim
 * @author Tim Turner <tim.turner@sports-it.com>
 */
class ClaimSet {

  /**
   * ClaimSet constructor.
   * @param array $array
   */
  public function __construct($array = []) {
    if (is_array($array)) {
      foreach ($array as $claim => $value) {
        $this->{$claim} = $value;
      }
    }
  }

  /**
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function get($name, $default = null) {
    return (property_exists($this, $name) ? $this->{$name} : $default);
  }

  /**
   * @param string $name
   * @param mixed $value
   * @return $this
   */
  public function set($name, $value) {
    $this->{$name} = $value;
    return $this;
  }

  /**
   * @return string
   */
  public function __toString() {
    return (string)json_encode(get_object_vars($this));
  }
}
