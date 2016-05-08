<?php
namespace Transport\JWT;

/**
 * Class JSONWebToken
 *
 * @package SIT\Transport\Token\Json\JWT
 * @author Tim Turner <tim.turner@sports-it.com>
 */
class JSONWebToken {
  const NAME = 'JSONWEBTOKEN';

  /**
   * The request header to parse for the token.
   */
  const HEADER_NAME   = 'JSONWebToken';

  /**
   * The request query to parse for the token; this is only used if the above header is not set.
   */
  const QUERY_TOKEN   = 'jwt';

  /**
   * @var string|array $header JOSE Header
   * @see https://tools.ietf.org/html/rfc7519#section-5 JOSE Header
   */
  public $header;
  /**
   * @var Claim\ClaimSet $claims JWT Claims
   * @see https://tools.ietf.org/html/rfc7519#section-4 JWT Claims
   */
  public $claims;

  /**
   * Initialize JSON Web Token
   * 
   * @see https://tools.ietf.org/html/rfc7519#section-7 Creating and Validating JWTs
   * 
   * @param mixed $tokenData
   */
  public function __construct($tokenData) {
    $this->header = new Header\Header();
    $this->claims = new Claim\ClaimSet();
    
    // Assume parsed token
    if (is_string($tokenData) && strpos($tokenData, '.') !== false) {

      $tokenData = explode('.', $tokenData);

      // Init JWT Header
      $header = $this->decode($tokenData[0]);

      // Init JWT Claims Set
      $payload = $this->decode($tokenData[1]);

    } elseif (is_array($tokenData)) {

      if (!isset($tokenData['header']) || !isset($tokenData['payload'])) {
        throw new \Exception('Invalid Token Structure', 'Invalid token structure on JSONWebToken initialization');
      } else {
        $header  = $tokenData['header'];
        $payload = $tokenData['payload'];
      }

    } elseif ($tokenData instanceof \stdClass) {

      if (!isset($tokenData->header) || !isset($tokenData->payload)) {
        throw new \Exception('Invalid Token Structure', 'Invalid token structure on JSONWebToken initialization');
      } else {
        $header = $tokenData->header;
        $payload = $tokenData->payload;
      }

    } elseif ($tokenData instanceof JSONWebToken) {
      $header = $tokenData->header;
      $payload = $tokenData->claims;

    } else {
      throw new \Exception('Invalid DataType to create token from', 'Unable to handle token type on JSONWebToken initialization');
    }
  
    foreach ($header as $name => $value) {
      if ($className = $this->jwtClassResolver($name, 'Header')) {
        $this->header->set($name, new $className($value));
      }
    }

    // Init JWT Claims Set
    foreach ($payload as $name => $value) {
      if ($className = $this->jwtClassResolver($name, 'Claim')) {
        // Claim names must be unique. Refer to spec: Public, Registered, Private claims
        $this->claims->set($name, new $className($value));
      }
    }

    $expiration = $this->claims->get('exp', null);
    $this->setExpireDate(($expiration ? $expiration->value : ''));
    $this->token = $this->encode($this->header) . '.' . $this->encode($this->claims);
  }

  /**
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getClaim($name, $default = null) {
    return $this->claims->get($name, $default);
  }

  /**
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getHeader($name, $default = null) {
    return $this->header->get($name, $default);
  }

  /**
   * @return bool
   * @see https://tools.ietf.org/html/rfc7519#section-7.2 Validating a JWT
   */
  public function validate() {
    if (empty($this->token)) {
      throw new \Exception('Invalid Token - Token is empty or not set');
    } elseif (empty($this->getExpireDate()) || $this->isExpired()) {

      throw new \Exception('Expired Token - Token is not currently valid');
      // @TODO: Add in lookup for issuer

    } elseif ($this->claims->iss->value != 'apps.dashplatform.com') {
      throw new \Exception('Unrecognized Issuer - Issuing principle not recognized. DashPlatform is only valid issuer currently.');
    }
    return true;
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
   * Returns the token ready to be inserted into an Authorization header.
   *
   * @return string
   * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
   */
  public function __toString() {
    return (string)$this->token;
  }
  
  /**
   * @param $string
   * @param bool $utf8
   * @return mixed
   */
  private function encode($string, $utf8 = true) {
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
      explode(
        '=', // rtrim might also work here if preferred
        base64_encode(
          $utf8 !== true
            ? $string
            : utf8_encode($string)
        )
      )[0]
    );
  
    return $output;
  }
  
  /**
   * @param $string
   * @param bool $utf8
   * @param bool $json
   * @return mixed
   */
  private function decode($string, $utf8 = true, $json = true) {
    // In php and other encoding specs for base64, we actually don't need to re-pad the string before decoding.
    // We'll do it anyways to reduce confusion and for completeness' sake.
    // Switch on length residue, pad last chunk of base64 characters to length of 4 with '=' if needed
    switch (strlen($string) % 4) {
      case 0:
        // Last chunk at length of 4 characters, no padding needed.
        break;
      case 2:
        $string .= '=='; // Pad by two, for 4 total
        break;
      case 3:
        $string .= '=';
        break;
      default:
        throw new \Exception('Invalid base64 string - Could not decode string from base64');
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
    $output = ($json !== true)
      ? $decoded
      : json_decode(
        ($utf8 !== true)
          ? $decoded
          : utf8_decode($decoded)
      );
  
    return $output;
  }

  /**
   * @param $name
   * @param $subNamespace
   * @return bool|string
   */
  public static function jwtClassResolver($name, $subNamespace) {
    $className = false;

    if ($subNamespace == 'Claim') {

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

    } elseif ($subNamespace == 'Header') {

      switch ($name) {
        case Header\AlgorithmParameter::NAME:
          $className = 'Header\AlgorithmParameter';
          break;

        case Header\TypeParameter::NAME:
          $className = 'Header\TypeParameter';
          break;
      }

    } else {
      throw new \Exception('JWT sub-namespace not found - Could not resolve class for unknown sub-namespace: ' . $subNamespace);
    }

    return (empty($className)
      ? $className
      : (__NAMESPACE__ . '\\' . $className));
  }
}
