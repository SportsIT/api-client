<?php
namespace Transport\JWT\Header;

/**
 * Class Header
 *
 * @package SIT\Transport\Token\Json\JWT\Header
 * @author Tim Turner <tim.turner@sports-it.com>
 */
class Header {

  /**
   * Header constructor.
   * @param array $array
   */
  public function __construct($array = []) {
    if (is_array($array)) {
      foreach ($array as $parameter => $value) {
        $this->{$parameter} = $value;
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
