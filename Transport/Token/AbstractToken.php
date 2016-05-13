<?php
namespace DashApi\Transport\Token;

//use SIT\Auth\OAuth2\Grant\AbstractGrant;
//use SIT\Auth\OAuth2\Scope;

use Carbon\Carbon;

/**
 * Class AbstractToken
 *
 *
 * @package DashApi\Transport\OAuth2\Token
 * @author Tim Turner <tim.turner@sports-it.com>
 */
abstract class AbstractToken implements TokenInterface {

  /**
   * The default number of seconds an access token is valid for.
   */
  const EXPIRE_TIME_DEFAULT_SECONDS = '86400'; // 24hrs
  
  /**
   * @var int $ID Immutable unique token ID.
   */
  protected $ID;

  /**
   * @var string Transport ready object that will survive transfer context.
   */
  protected $token;

  /**
   * @var Carbon
   */
  protected $expireDate;
  
  protected $attributes = [];
  
  public function __toString() {
    if ($this->token === null) {
      throw new \LogicException("Expected property `token` to be set, assign default in extending class or in ");
    }
  }
  
  public function serialize() {
    return serialize($this);
  }
  
  public function unserialize($serialized) {
    return unserialize($serialized);
  }
  
  public function getID() {
    if (empty($this->ID)) {
      throw new \LogicException("Expected non-empty property `ID`, all classes extending AbstractToken must set immutable `ID` on initialization.");
    }
    return $this->ID;
  }
  
  public function getAttributes() {
    return $this->attributes;
  }
  
  public function setAttributes(array $attributes) {
    
      foreach ($attributes as $name => $value) {
        $this->setAttribute($name,$value);
      }
  }
  
  public function hasAttribute($name) {
    return array_key_exists($name,$this->attributes);
  }
  
  public function getAttribute($name) {
    
    if(!$this->hasAttribute($name)) {
      throw new \InvalidArgumentException(sprintf('Attribute does not exist for key `%s`.', $name));
    }
    
    return $this->attributes[$name];
  }
  
  public function setAttribute($name, $value) {
    
    try {
      $this->attributes[$name] = $value;
    } catch ($e) {
      throw new \OutOfBoundsException(sprintf("Error setting attribute, received key: `%s` and value: `%s`.", $name, $value), null, $e);
    }
  }
  
  
  
  
  
  
  
  
  
  /**
   * @return string
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * @param string $token
   * @return $this
   */
  public function setToken($token) {
    $this->token = $token;
    return $this;
  }

  /**
   * @return bool
   */
  public function isExpired() {
    return Carbon::now()->gt($this->getExpireDate());
  }

  /**
   * @return Carbon
   */
  public function getExpireDate() {
    if (empty($this->expireDate)) {
      $this->expireDate = Carbon::now()->addSeconds(self::EXPIRE_TIME_DEFAULT_SECONDS);
    }
    return $this->expireDate;
  }

  /**
   * @TODO: Need to account for negative, 'expires in' vs. 'expired for', etc
   * @return int
   */
  public function getExpireDateInSeconds($abs = true) {
    return Carbon::now()->diffInSeconds($this->getExpireDate(), $abs);
  }

  /**
   * @return string
   */
  public function getExpireDateForHumans() {
    return $this->getExpireDate()->diffForHumans();
  }

  /**
   * @param Carbon|string $expireDate
   * @return $this
   */
  public function setExpireDate($expireDate) {
    if (is_int($expireDate)) {
      // DateTime construct will cast to string on init
      $expireDate = Carbon::createFromTimestamp($expireDate);
    } elseif (is_string($expireDate)) {
      $expireDate = Carbon::parse($expireDate);
    }

    $this->expireDate = $expireDate;
    return $this;
  }

  /**
   * Gets the token type.
   *
   * @return string
   */
  public abstract function getType();

  /**
   * @return bool
   */
  public abstract function validate();

}
