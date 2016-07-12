<?php
namespace DashApi\Transport\Token\JWT\Attribute;

use \DashApi\Transport\Token\Attribute\AbstractAttribute;
use DashApi\Transport\Token\JWT\Header;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/11/16]
 */
class HeaderAttribute extends AbstractAttribute
{
  const NAME = 'HEADER';
  
  /**
   * @return string
   */
  public function __toString() {
    return json_encode($this->value);
  }
  
  /**
   * @param string $name
   * @param mixed  $default
   *
   * @return mixed
   */
  public function getParameter($name, $default = null) {
    return (property_exists($this->value, $name) ? $this->value->{$name} : $default);
  }
  
  public function set($parameters) {
    $this->value = new \stdClass();
    $this->addHeader($parameters);
  }
  
  public function addHeader($parameters) {
    if (!is_array($parameters)) {
      throw new \InvalidArgumentException(__CLASS__ . '->' . __FUNCTION__ . ' expected argument of type `array`, but received type `' . gettype($parameters) . '`');
    } else {
      
      foreach ($parameters as $parameterName => $value) {
        
        // Add element as property of parameters object
        $this->setParameter($parameterName, $value);
      }
    }
  }
  
  /**
   * @param string|Header\AbstractParameter $parameter Name of an AbstractParameter or the Abstract Parameter itself
   * @param mixed                           $value
   *
   * @return $this
   */
  public function setParameter($parameter, $value = null) {
    // If a parameter is passed in directly, try to derive parameter name
    if ($value === null) {
      
      if (gettype($parameter) !== 'object' || !defined(get_class($parameter) . '::NAME')) {
        throw new \DomainException(__CLASS__ . '->' . __FUNCTION__ . ' expects object extending AbstractParameter with defined constant `NAME`');
      }
      
      /** @var Header\AbstractParameter $parameter */
      $name = $parameter::NAME;
    } else {
      
      /** @var string $name */
      $name = $parameter;
      
      /** @var mixed $parameter */
      $parameter = $value;
    }
    
    // Sanity check for a few invalid scenarios.
    // Throws if $name results in nonexistent FQCN or for invalid parameter value
    $this->validateParameter($name, $parameter);
    
    // Assign the parameter to this header
    $this->value->{$name} = $parameter;
    
    return $this;
  }
  
  public function toArray() {
    return $this->value;
  }
  
  protected function validateParameter($parameterName, &$parameter) {
    if (empty($parameter)) {
      
      throw new \RangeException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing empty element for key `' . $parameterName . '`');
    } elseif (
      // Soft sanity check, we want to limit scope of custom parameter classes:
      gettype($parameter) !== 'object'                        // Element must be an 'object', i.e. a class.
      || !is_subclass_of($parameter, 'AbstractParameter', false)  // Element must extend AbstractParameter class.
      || strpos(__CLASS__, 'Parameter') === false              // Element must follow naming scheme <ParameterName>Parameter
    ) {
      // Try to lookup a defined AbstractParameter with AbstractParameter::NAME == $parameterName
      if ($parameterClassName = $this->parameterResolver($parameterName)) {
        // Set parameter by reference to correct AbstractParameter with given value
        $parameter = new $parameterClassName($parameter);
      } else {
        throw new \DomainException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing invalid element type for key `' . $parameterName . '`, expected `object` extending AbstractParameter class');
      }
    }
    
    return true;
  }
  
  protected static function parameterResolver($name) {
    $className = false;
  
    switch ($name) {
      case Header\AlgorithmParameter::NAME:
        $className = 'Header\AlgorithmParameter';
        break;
    
      case Header\TypeParameter::NAME:
        $className = 'Header\TypeParameter';
        break;
    }
    
    $className = (substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\')) . '\\' . $className);
    
    // Sanity check - Throw if no defined class exists for resolved FQCN
    if (!class_exists($className)) {
      throw new \DomainException('Resolved FQCN does not exist');
    }
    
    return (empty($className)
      ? $className
      //: (substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,'\\')) . '\\' . $className));
      : $className);
  }
}