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
   * @var string
   */
  protected $token;

  /**
   * @var Carbon
   */
  protected $expireDate;
  
  protected $roles = [];
  
  protected $authenticated = false;
  
  protected $attributes = [];
  
  public function serialize() {
    return serialize($this);
  }
  
  public function unserialize($serialized) {
    return unserialize($serialized);
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
   * Gets this token as an array (used for API resources, etc)
   *
   * @return array
   */
  public function toArray() {
    return [
      'access_token'      => $this->getToken(),
      'token_type'        => $this->getType(),
      'expires_in'   => (string)$this->getExpireDateInSeconds(),
      //'scope'             => $this->getScopeAsString(), // getScopeAsString not implemented?
      //'state'             => $this->getState() // Unrecognized, client MUST ignore
      //'expires_friendly'  => $this->getExpireDateForHumans(), // Unrecognized, client MUST ignore
    ];
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
