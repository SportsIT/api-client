<?php
/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/9/16]
 */
namespace DashApi\Transport\Token\OAuth2\Bearer;

/**
 * Class Bearer
 *
 * @package SIT\Auth\OAuth2\Token
 * @author Nate Strandberg <nate@sports-it.com>
 */
class Bearer extends AbstractToken
{
  const NAME = 'BEARER';
  /**
   * The request header to parse for the token.
   */
  const HEADER_NAME = 'Authorization';
  /**
   * The request query to parse for the token; this is only used if the above header is not set.
   */
  const QUERY_TOKEN = 'access_token';
  
  /**
   * Extracts the Authorization header from a request & instantiates a new Bearer class.
   *
   * @param $request
   * @return static
   */
  /*public static function fromRequest(ServerRequestInterface $request) {
    $authorizationHeader = $request->getHeaderLine(Bearer::HEADER_NAME);
    $token = '';
    
    if (!empty($authorizationHeader)) {
      
      // Parse the token from the authorization header..
      if (preg_match('/^Bearer:{0,1}\s*(?<token>\S+)$/i', $authorizationHeader, $matches)) {
        $token = $matches['token'];
      }
    } else {
      
      // Attempt to get the access token from query params..
      $params = $request->getQueryParams();
      $token = (isset($params[Bearer::QUERY_TOKEN]) ? $params[Bearer::QUERY_TOKEN] : '');
    }
    
    return new static($token);
  }*/
  
  /**
   * @param string $token
   */
  public function __construct($token) {
    $this->token = $token;
  }
  
  /**
   * @return bool
   */
  public function validate() {
    return !empty($this->token);
  }
  
  /**
   * Gets the token type.
   *
   * @return string
   */
  public function getType() {
    return strtolower(self::NAME);
  }
  
  public function getExpiresIn() {
    return (string)$this->getExpireDateInSeconds();
  }
  
  /**
   * Returns the token ready to be inserted into an Authorization header.
   *
   * @return string
   * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
   */
  public function __toString() {
    return "Bearer {$this->token}";
  }
}