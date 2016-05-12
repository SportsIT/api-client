<?php
namespace DashApi\Transport\Token\JWT\Claim;

/**
 * Class ClaimSet
 *
 * @package \DashApi\Transport\Token\JWT\Claim
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
    // @TODO: Replace with DashApi\Utility\Json::encode()
    return (string)json_encode(get_object_vars($this));
  }
  
  public function addAttributes($attributes = []) {
    if (!is_array($attributes)) {
      throw new \InvalidArgumentException(__CLASS__ . '->' . __FUNCTION__ . ' expected argument of type `array`, but received type `' . gettype($attributes) . '`');
    } else {
      foreach ($attributes as $claimName => $value) {
        if (empty($value)) {
          throw new \RangeException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing empty element for key `' . $claimName . '`');
        } elseif if (
          // Soft sanity check, we want to limit scope of custom claim classes:
          gettype($value) !== 'object'                        // Element must be an 'object', i.e. a class.
          || !is_subclass_of($value, 'AbstractClaim', false)  // Element must extend AbstractClaim class.
          || strpos(__CLASS__,'Claim') === false              // Element must follow naming scheme <ClaimName>Claim
        ) {
          throw new \DomainException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing invalid element type for key `' . $claimName . '`, expected `object` extending AbstractClaim class');
        } else {
          $this->{$claim} = $value;
        }
      }
    }
  }
}
