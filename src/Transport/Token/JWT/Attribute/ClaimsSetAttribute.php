<?php
namespace DashApi\Transport\Token\JWT\Attribute;

use DashApi\Transport\Token\Attribute\AbstractAttribute;
use DashApi\Transport\Token\JWT\Claim;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/11/16]
 */
class ClaimsSetAttribute extends AbstractAttribute
{
  const NAME = 'CLAIMSET';
  
  /**
   * @return string
   */
  public function __toString() {
    return json_encode(get_object_vars($this));
  }
  
  /**
   * @param string $name
   * @param mixed  $default
   *
   * @return mixed
   */
  public function getClaim($name, $default = null) {
    return (property_exists($this->value, $name) ? $this->value->{$name} : $default);
  }
  
  public function set($claims) {
    $this->value = new \stdClass();
    $this->addClaims($claims);
  }
  
  public function addClaims($claims) {
    if (!is_array($claims)) {
      throw new \InvalidArgumentException(__CLASS__ . '->' . __FUNCTION__ . ' expected argument of type `array`, but received type `' . gettype($claims) . '`');
    } else {
      
      foreach ($claims as $claimName => $value) {
        
        // Add element as property of claims object
        $this->setClaim($claimName, $value);
        
      }
    }
  }
  
  /**
   * @param string|AbstractClaim  $claim Name of an AbstractClaim or the Abstract Claim itself
   * @param mixed                 $value A
   *
   * @return $this
   */
  public function setClaim($claim, $value = null) {
    // If a claim is passed in directly, try to derive claim name
    if ($value === null) {
      
      if (gettype($claim) !== 'object' || !defined(get_class($claim) . '::NAME')) {
        throw new \DomainException(__CLASS__ . '->' . __FUNCTION__ . ' expects object extending AbstractClaim with defined constant `NAME`');
      }
      
      /** @var Claim\AbstractClaim $claim */
      $name = $claim::NAME;
      
    } else {
      
      /** @var string $name */
      $name = $claim;
      
      /** @var mixed $claim */
      $claim = $value;
      
    }
    
    // Sanity check for a few invalid scenarios.
    // Throws if $name results in nonexistent FQCN or for invalid claim value
    $this->validateClaim($name, $claim);
    
    // Assign the claim to this claim set
    $this->value->{$name} = $claim;
    
    return $this;
  }
  
  protected function validateClaim($claimName, &$claim) {
    if (empty($claim)) {
      
      throw new \RangeException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing empty element for key `' . $claimName . '`');
    
    } elseif (
      // Soft sanity check, we want to limit scope of custom claim classes:
      gettype($claim) !== 'object'                        // Element must be an 'object', i.e. a class.
      || !is_subclass_of($claim, 'AbstractClaim', false)  // Element must extend AbstractClaim class.
      || strpos(__CLASS__, 'Claim') === false              // Element must follow naming scheme <ClaimName>Claim
    ) {
      // Try to lookup a defined AbstractClaim with AbstractClaim::NAME == $claimName
      if ($claimClassName = $this->claimResolver($claimName)) {
        // Set claim by reference to correct AbstractClaim with given value
        $claim = new $claimClassName($claim);
      } else {
        throw new \DomainException(__CLASS__ . '->' . __FUNCTION__ . ' received argument containing invalid element type for key `' . $claimName . '`, expected `object` extending AbstractClaim class');
      }
  
    }
    
    return true;
  }
  
  protected static function claimResolver($name) {
    $className = false;
    
    switch ($name) {
      case Claim\AudienceClaim::NAME:
        $className = 'Claim\AudienceClaim';
        break;
      
      case Claim\ExpirationTimeClaim::NAME:
        $className = 'Claim\ExpirationTimeClaim';
        break;
      
      case Claim\IssuedAtClaim::NAME:
        $className = 'Claim\IssuedAtClaim';
        break;
      
      case Claim\IssuerClaim::NAME:
        $className = 'Claim\IssuerClaim';
        break;
      
      case Claim\JWTIDClaim::NAME:
        $className = 'Claim\JWTIDClaim';
        break;
      
      case Claim\NotBeforeClaim::NAME:
        $className = 'Claim\NotBeforeClaim';
        break;
      
      case Claim\SubjectClaim::NAME:
        $className = 'Claim\SubjectClaim';
        break;
      
      case Claim\CompanyCodeClaim::NAME:
        $className = 'Claim\CompanyCodeClaim';
        break;
      
      case Claim\EmployeeIDClaim::NAME:
        $className = 'Claim\EmployeeIDClaim';
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