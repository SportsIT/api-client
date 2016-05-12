<?php
namespace DashApi\Transport\Token\OAuth2;

//use SIT\Auth\OAuth2\Grant\AbstractGrant;
//use SIT\Auth\OAuth2\Scope;

use Carbon\Carbon;
use DashApi\Transport\Token\AbstractToken;

/**
 * Class AbstractToken
 * 
 * OAuth2 token parameters conform to:
 * Auth Request
 *   - [req] response_type
 *   - [req] client_id
 *   - [opt] redirect_uri
 *   - [opt] scope
 *   - [opt] state (recommended)
 * Auth Response
 *   - [req] code
 *   - [req] state
 * Auth Error Response
 *   - [req] error
 *   - [opt] error_description
 *   - [opt] error_uri
 *   - [req] state
 * Token Request
 *   - [req] client_id
 *   - [req] client_secret
 *   - [req] grant_type
 *   - [req] code
 *   - [req] redirection_uri
 * Token Response
 *   - [req] access_token
 *   - [req] token_type
 *   - [opt] expires_in (recommended)
 *   - [opt] refresh_token
 *   - [req|opt] scope Optional only if identical to client's request scope
 * 
 *
 * @package DashApi\Transport\OAuth2\Token
 * @author Nate Strandberg <nate@sports-it.com>
 */
class OAuth2Token extends AbstractToken {

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

  /**
   * @var Scope
   */
  //protected $scope;

  /**
   * @var string
   */
  protected $state;

  /**
   * @param AbstractGrant $grant
   */
  //public function __construct(AbstractGrant $grant) {
  //
  //}

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
   * @return string
   */
  public function getState() {
    return $this->state;
  }

  /**
   * @param string $state
   * @return $this
   */
  public function setState($state) {
    $this->state = $state;
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
  //public abstract function getType();

  /**
   * @return bool
   */
  //public abstract function validate();

}
