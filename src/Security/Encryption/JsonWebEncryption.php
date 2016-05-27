<?php
namespace Security\Encryption;

use DashApi\Transport\Token\JsonWebToken;

/**
 * Class JsonWebEncryption
 *
 * @see https://tools.ietf.org/html/rfc7516 JSON Web Encryption
 *
 * @package SIT\Security\Encryption\Json
 * @author Tim Turner <tim.turner@sports-it.com>
 */
class JsonWebEncryption {
  const NAME = 'JSONWEBENCRYPTION';

  /**
   * The request header to parse for the token.
   */
  const HEADER_NAME   = 'JsonWebEncryption';

  /**
   * The request query to parse for the token; this is only used if the above header is not set.
   */
  const QUERY_TOKEN   = 'jwe';
  
  //const HMAC_KEY = 'AyM1SysPpbyDfgZld3umj1qzKObwVMkoqQ-EstJQLr_T-1qS0gZH75aKtMN3Yj0iPS4hcgUuTwjAzZr1Z9CAow';
  /**
   * @var string $protected BASE64URL(UTF8(JWE Protected Header))
   * @see https://tools.ietf.org/html/rfc7516#section-4 JOSE Header
   */
  public $protected;
  /**
   * @var string $unprotected JWE Shared Unprotected Header
   */
  public $unprotected;
  /**
   * @var string $header JWE Per-Recipient Unprotected Header
   */
  public $header;
  /**
   * @var string $encrypted_key BASE64URL(JWE Encrypted Key)
   */
  public $encrypted_key;
  /**
   * @var string $iv BASE64URL(JWE Initialization Vector)
   */
  public $iv;
  /**
   * @var string $ciphertext BASE64URL(JWE Ciphertext)
   */
  public $ciphertext;
  /**
   * @var string $tag BASE64URL(JWE Authentication Tag)
   */
  public $tag;
  /**
   * @var string $aad BASE64URL(JWE AAD)
   */
  public $aad;

  /**
   * @param string $token
   */
  public function __construct(JsonWebToken $token) {
    /**
     * Init JWE following rfc steps
     * @see https://tools.ietf.org/html/rfc7516#section-3.3 Example JWE
     */
    
    $this->header = json_encode($token->getHeaders());
    $this->ciphertext = $this->encrypt(json_encode($token->getClaims()));
  }

  /**
   * @return bool
   */
  public function isValid() {
    /* @TODO: Implement validation based on rfc.
     * TL;DR: If any decryption step fails, JWE is invalid.
     * @see https://tools.ietf.org/html/rfc7516#section-5.2 Message Decryption
     */
  }

  /**
   * Gets the token type.
   *
   * @return string
   */
  public function getType() {
    return strtolower(self::NAME);
  }

  /**
   * JWE Compact Serialization
   * 
   * Returns the token ready to be inserted into an Authorization header.
   *
   * @return string
   * @see 
   * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
   */
  public function __toString() {
    /**
     * @TODO Serialization
     * @see https://tools.ietf.org/html/rfc7516#section-3.1 JWE Compact Serialization Overview
     */
    return 'Serialization not implemented!';
    //return $this->token;
  }
  
  private function encrypt($plaintext) {
    //$ciphertext = false;
    // @TODO: Implement
    $ciphertext = $plaintext;
    return $ciphertext;
  }
  
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
