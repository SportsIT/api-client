<?php
namespace DashApi\Security\Signature;

use DashApi\Transport\Token\JsonWebToken;

/**
 * Class JsonWebSignature
 *
 * @see     https://tools.ietf.org/html/rfc7516 JSON Web Signature
 *
 * @package SIT\Security\Signature\Json
 * @author  Tim Turner <tim.turner@sports-it.com>
 */
class JsonWebSignature {
  const NAME = 'JSONWEBENCRYPTION';
  /**
   * The request header to parse for the token.
   */
  const HEADER_NAME = 'JsonWebSignature';
  /**
   * The request query to parse for the token; this is only used if the above header is not set.
   */
  const QUERY_TOKEN = 'jws';
  
  /**
   * @var string $protected BASE64URL(UTF8(JWS Protected Header))
   * @see https://tools.ietf.org/html/rfc7516#section-4 JOSE Header
   */
  protected $protected;
  /**
   * @var string $header JWS Unprotected Header
   */
  //public $header;
  /**
   * @var string $payload BASE64URL(JWS Payload)
   */
  //public $payload;
  
  /**
   * @var JsonWebToken $jwt
   */
  protected $jwt;
  /**
   * @var string $signature BASE64URL(JWS Signature)
   */
  protected $signature;
  
  protected $token;
  
  protected $secret;
  
  /**
   * @param JsonWebToken $token
   * @param string       $secret Base64(urlsafe) encoded key
   */
  public function __construct(JsonWebToken $token, $secret) {
    if (ctype_xdigit($secret) === false) {
      throw new \LogicException(
        sprintf(
          "Expected `secret` parameter to be hexadecimal string, unable to parse given value: `%s`",
          $secret
        )
      );
    } else {
      $this->secret = pack('H*', $secret);
    }
    
    $this->jwt = $token;
    
    /**
     * Init JWS following rfc steps
     *
     * @see https://tools.ietf.org/html/rfc7515#section-3.3 Example JWS
     * @see https://tools.ietf.org/html/rfc7515#section-5.1 Message Signature or MAC Computation
     */
    $this->signature = $this->encode(
      hash_hmac(
        'sha256', 
        $token, 
        $this->secret, 
        true
      ),
      false,
      false
    );
    
    $this->token = $token . "." . $this->signature;
  }
  
  /**
   * @see https://tools.ietf.org/html/rfc7515#section-5.2 Message Signature or MAC Validation
   * 
   * @param null $signature
   */
  public function validate($signature = null) {
    if ($this->getSignature() != $signature) {
      // UnauthorizedException
      throw new \Exception("Invalid Token - Token signatures do not match, possible data corruption or tampering");
    }
  }
  
  /**
   * Gets the token type.
   *
   * @return string
   */
  public function getType() {
    return strtolower(self::NAME);
  }
  
  public function getJsonWebToken() {
    return $this->jwt;
  }
  
  /**
   * @return mixed|string
   */
  public function getSignature() {
    return $this->signature;
  }
  
  /**
   * JWS Compact Serialization
   *
   * Returns the token ready to be inserted into an Authorization header.
   *
   * @return string
   * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
   */
  public function __toString() {
    /**
     * @see  https://tools.ietf.org/html/rfc7515#section-3.1 JWS Compact Serialization Overview
     */;
    return $this->token;
  }
  
  /**
   * @param $string
   * @param bool $utf8
   * @param bool $json
   * @return mixed
   */
  private function encode($string, $utf8 = true, $json = true) {
    $string = ($json !== true)
      ? $string
      : json_encode($string);
    $string = ($utf8 !== true)
      ? $string
      : utf8_encode($string);
    
    // URL and File safe base64
    // - Translate padding
    // - Replace 62nd character (base64) '+' with '-'
    // - Replace 63nd character (base64) '/' with '_'
    // @ref: "Base64url Encoding" - https://tools.ietf.org/html/draft-jones-json-web-token-08#section-2
    // @ref: https://tools.ietf.org/html/rfc4648#section-5
    // @ref: https://tools.ietf.org/html/rfc7515#appendix-C
    $output = str_replace( // strtr is probably faster here, use if preferred
      ['+', '/'],
      ['-', '_'],
      explode("=", base64_encode($string))[0] // rtrim might also work here if preferred
    );
  
    return $output;
  }
  
  private function decode($string, $utf8 = true, $json = true) {
    // In php and other encoding specs for base64, we actually don't need to re-pad the string before decoding.
    // We'll do it anyways to reduce confusion and for completeness' sake.
    // Switch on length residue, pad last chunk of base64 characters to length of 4 with '=' if needed
    switch (strlen($string) % 4) {
      case 0:
        // Last chunk at length of 4 characters, no padding needed.
        break;
      case 2:
        $string .= "=="; // Pad by two, for 4 total
        break;
      case 3:
        $string .= "=";
        break;
      default:
        throw new \Exception("Invalid base64 string - Could not decode string from base64");
    }
  
    // URL and File safe base64
    // - Translate padding
    // - Replace 62nd character (base64) '-' with '+'
    // - Replace 63nd character (base64) '_' with '/'
    // @ref: "Base64url Encoding" - https://tools.ietf.org/html/draft-jones-json-web-token-08#section-2
    // @ref: https://tools.ietf.org/html/rfc4648#section-5
    // @ref: https://tools.ietf.org/html/rfc7515#appendix-C
    //$output = base64_encode(utf8_encode($string));
    $decoded = base64_decode(
      str_replace(
        ['-', '_'],
        ['+', '/'],
        $string
      )
    );
    
    $output = ($utf8 !== true)
      ? $decoded
      : utf8_decode($decoded);
    $output = ($json !== true)
      ? $output
      : json_encode($output);
  
    return $output;
  }
}
