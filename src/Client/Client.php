<?php
namespace DashApi\Client;

use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleClient;

use DashApi\Utility\Json;
use DashApi\Transport\Token;
use DashApi\Transport\Token\OAuth2;
use DashApi\Security\Signature\JsonWebSignature;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * ### Overview
 *
 * @package DashApi\Client
 * @author Tim Turner <tim.turner@sports-it.com>
 */
final class Client {

  const REQUEST_SCHEMA = 'https';
  const REQUEST_HOST = 'apps.dashplatform.com';
  const REQUEST_PATH_SEG_API = '/dash/api';
  const REQUEST_PATH_SEG_AUTH_TOKENS = '/auth/tokens';
  const REQUEST_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
  const REQUEST_EXPIRE_TIME = 86400;
  const REQUEST_TOKEN_TYPE = 'JWS'; // Allowed: JWT | JWS | JWE
  
  /**
   * @var string
   */
  protected $companyCode;
  
  /**
   * @var string
   */
  protected $secret;

  /**
   * @var Carbon|string|null
   */
  protected $expireDate;
  
  /**
   * @var string|int
   */
  protected $employeeID;
  
  /**
   * @var string|int
   */
  protected $customerID;
  
  /**
   * @var string|int
   */
  protected $facilityID;
  
  /**
   * Set on initialization to incoming $apiUrl if passed.
   * Otherwise, set to: static::REQUEST_SCHEMA . '://' . static::REQUEST_HOST . static::REQUEST_PATH_SEG_API;
   *
   * @var string|null
   */
  protected $apiUrl;
  
  /**
   * @var string
   */
  protected $header;
  
  /**
   * @var string
   */
  protected $claims;
  
  /**
   * @var \DashApi\Transport\Token\JsonWebToken
   */
  protected $jsonWebToken;
  
  /**
   * @var string
   */
  protected $jsonWebSignature;
  
  /**
   * @var string
   */
  protected $token;
  
  /**
   * @var string
   */
  protected $accessToken;
  
  /**
   * @var string
   */
  protected $authTokenType = 'bearer';

  /**
   * Used to control how much information is exposed via exceptions & messages.
   *
   * @var bool
   */
  protected $debugMode = false;
  
  /**
   * Client constructor.
   *
   * @param string $companyCode       This is the same company code used to login to DASH Platform
   * @param int $facilityID    Database ID for facility
   * @param string $secret            API Secret key found in DASH Platform: Settings > Company > API (tab)
   * @param array|null $authorization Array matching one of the following:
   *                                  Employee Auth: ['employee' => (int)]
   *                                  Customer Auth: ['customer' => (int)]
   *                                  Facility Auth: ['facility' => (int)]
   *                                  Scope Auth:    ['scope' => (string) CSV api resource dotpaths]
   *                                  SIT Auth:     ['auth' => (string) CSV SIT_Authorization flags]
   * @param string|null $apiUrl
   * @param string|null $header
   * @param array|null $claims
   * 
   * Examples:
   * 
   * $api = new \DashApi\Client\Client('mycompanycode', 123, 'companysecretAPIkey', ['employee' => 1337, 'scope' => 'products.read,events.read']);
   * 
   */
  public function __construct($companyCode, $facilityID, $secret, $authorization = null, $apiUrl = null, $header = null, $claims = null) {
    $this->companyCode = $companyCode;
    $this->facilityID = $facilityID;
    $this->secret = $secret;
    
    if (array_key_exists('employee', $authorization) && $authorization['employee'] !== null) {
      if (is_numeric($authorization['employee'])) {
        $this->employeeID = $authorization['employee'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.employee parameter. Expected (int) or (string) numeric, got: %s", print_r($authorization['employee'], true)));
      }
    }
    
    if (array_key_exists('customer', $authorization) && $authorization['customer'] !== null) {
      if (is_numeric($authorization['customer'])) {
        $this->customerID = $authorization['customer'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.customer parameter. Expected (int) or (string) numeric, got: %s", print_r($authorization['customer'], true)));
      }
    }
  
    if (array_key_exists('scope', $authorization) && $authorization['scope'] !== null) {
      if (is_string($authorization['scope'])) {
        $this->scope = explode(',', $authorization['scope']);
      } elseif (is_array($authorization['scope'])) {
        $this->scope = $authorization['scope'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.scope parameter. Expected (string) csv or (array) of dot paths, got: %s", print_r($authorization['scope'], true)));
      }
    }
  
    if (array_key_exists('auth', $authorization) && $authorization['auth'] !== null) {
      if (is_string($authorization['auth'])) {
        $this->auth = explode(',', $authorization['auth']);
      } elseif (is_array($authorization['auth'])) {
        $this->auth = $authorization['auth'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.auth parameter. Expected (string) csv or (array) of SIT Authorizations, got: %s", print_r($authorization['auth'], true)));
      }
    }
    
    // Resolve URL used to access Dash API
    if ($apiUrl !== null) {
      $this->apiUrl = $apiUrl;
    } else {
      $this->apiUrl = static::REQUEST_SCHEMA . '://' . static::REQUEST_HOST . static::REQUEST_PATH_SEG_API;
    }
    
    // Default Header set
    if ($header !== null) {
      $this->header = $header;
    } else {
      $this->header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
      ];
    }
    
    // Default Claims set
    if ($claims !== null) {
      $this->claims = $claims;
    } else {
      $this->claims = [
        'iat' => time(),
        'jti' => base64_encode(mcrypt_create_iv(32)),
        'iss' => $_SERVER['HTTP_HOST'], // client hostname / domain
        'exp' => time() + static::REQUEST_EXPIRE_TIME,
        'cco' => $this->companyCode, // Private Claim
        'fid' => $this->facilityID, // Private Claim
      ];
  
      if (property_exists($this, 'employeeID') && $this->employeeID) {
        $this->claims['eid'] = $this->employeeID; // Private Claim
      }
  
      if (property_exists($this, 'customerID') && $this->customerID) {
        $this->claims['cid'] = $this->customerID; // Private Claim
      }
  
      if (property_exists($this, 'scope') && $this->scope) {
        $this->claims['scope'] = $this->scope; // Private Claim
      }
      
      if (property_exists($this, 'auth') && $this->auth) {
        // @todo: rename claim 'authorizations' -> 'auth'
        $this->claims['authorizations'] = $this->auth; // Private Claim
      } 
    }
  }

  /**
   * Enables/disables debug mode.
   *
   * Warning: When debug mode is enabled, exceptions may contain potentially
   * sensitive information!
   *
   * @param bool $mode Whether or not to enable debug mode.
   * @return $this
   */
  public function setDebugMode($mode = true) {
    $this->debugMode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);
    return $this;
  }

  /**
   * Checks whether or not debug mode is enabled.
   *
   * @return bool Returns true if debug mode is enabled, false otherwise.
   */
  public function isDebugMode() {
    return $this->debugMode;
  }
  
  /**
   * @param string $resourcePath
   * @return StreamInterface|string Guzzle\Http\EntityBodyInterface -> StreamInterface
   */
  public function get($resourcePath) {
    $this->validateAccessToken();
    
    /** @var ResponseInterface $result */
    $result = (new GuzzleClient)->get(
      $this->apiUrl . $resourcePath, [
        'headers' => [
          'Content-Type'  => 'application/json',
          'Authorization' => ucfirst($this->authTokenType) . ' ' . $this->accessToken
        ],
        'verify'      => false, // @todo: fix for self-signed cert
        'http_errors' => false  // Set to false to disable throwing exceptions on an HTTP protocol errors
      ]
    );
    
    return $result->getBody();
  }
  
  /**
   * Gets an access token from the API server.
   *
   * @return string
   *
   * @throws \LogicException Error when getting a request token
   * @throws \RuntimeException Response did not contain valid access token.
   */
  public function getAccessToken() {
    
    if (!$this->token) {
      try {
        $this->getAuthRequestToken();
      } catch (\Exception $e) {
        throw new \LogicException('Could not build authorization request token. Error: ' . $e->getMessage(), null, $e);
      }
    }
  
    $result = (new GuzzleClient)->post(
      $this->getTokenCreateUrl(),
      [
        'headers'     => ['Content-Type' => 'application/json'],
        'body'        => $this->getJsonAPIRequestBody(),
        'verify'      => false, // @todo: fix for self-signed cert
        'http_errors' => false  // Set to false to disable throwing exceptions on an HTTP protocol errors
      ]
    );

    try {
      $responseData = Json::decode((string)$result->getBody());
    } catch (\RuntimeException $e) {
      $message = 'Unable to decode server response as JSON';

      // Add the response body to the exception if we're debugging..
      if ($this->debugMode) {
        $message .= "\r\n" . print_r((string)$result->getBody(), true);
      }
      throw new \RuntimeException($message, null, $e);
    }

    if (empty($responseData->data[0]->attributes->access_token)) {
      $message = 'Response did not contain valid access token';

      // Add the response data to the exception if we're debugging..
      if ($this->debugMode) {
        $message .= "\r\n" . print_r($responseData, true);
      }
      throw new \RuntimeException($message);
    }
    
    $this->accessToken = $responseData->data[0]->attributes->access_token;
    return $responseData->data[0]->attributes->access_token;
  }
  
  /**
   * @param array|string|null $header
   * @param array|string|null $claims
   * @return string
   *
   * @throws \LogicException
   * @throws \RuntimeException
   */
  public function getAuthRequestToken($header = null, $claims = null) {
    $tokenData = new \stdClass();
    
    $tokenData->header = $header ?: $this->header;
    $tokenData->payload = $claims ?: $this->claims;
    
    $this->jsonWebToken = new Token\JsonWebToken($tokenData);
    $this->jsonWebSignature = new JsonWebSignature($this->jsonWebToken, $this->secret);
    
    switch (static::REQUEST_TOKEN_TYPE) {
      case 'JWT':
        $this->token = (string)$this->jsonWebToken;
        break;
      
      case 'JWS':
        $this->token = (string)$this->jsonWebSignature;
        break;
      
      case 'JWE':
        // @TODO: Not implemented yet!
        throw new \LogicException('JSON Web Encryption token requests have not been implemented yet, please reset static::REQUEST_TOKEN_TYPE');
        break;
      
      default:
        throw new \RuntimeException('Invalid Request Token Type - REQUEST_TOKEN_TYPE must be set to a valid type');
        break;
    }
    
    return $this->token;
  }
  
  /**
   * Checks whether or not the access token is expired.
   *
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
      $this->expireDate = Carbon::now()->addSeconds(Token\AbstractToken::EXPIRE_TIME_DEFAULT_SECONDS);
    }
    return $this->expireDate;
  }
  
  /**
   * @TODO: Need to account for negative, 'expires in' vs. 'expired for', etc
   *
   * @param bool $abs
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
   * @return mixed
   */
  protected function getJsonAPIRequestBody() {
    if (empty($this->jsonWebSignature)) {
      $this->jsonWebSignature = new JsonWebSignature($this->jsonWebToken, $this->secret);
    }
    return Json::encode([
      'data' => [
        'type'  => static::REQUEST_TYPE,
        'id'    => 1,
        'attributes'  => [
          'header'    => $this->header,
          'claims'    => $this->claims,
          'signature' => $this->jsonWebSignature->getSignature()
        ]
      ]
    ]);
  }
  
  /**
   * @return string
   */
  protected function getApiUrl() {
    return $this->apiUrl;
  }
  
  /**
   * @return string
   */
  protected function getApiAuthUrl() {
    return $this->getApiUrl() . static::REQUEST_PATH_SEG_AUTH_TOKENS;
  }
  
  /**
   * @return string 
   */
  protected function getTokenCreateUrl() {
    return $this->getApiAuthUrl() . '/create';
  }
  
  /**
   * Check if valid access token is set and get one if not.
   *
   * @return $this
   */
  protected function validateAccessToken() {
    if (empty($this->accessToken)) {
      $this->accessToken = $this->getAccessToken();
    } else {

      $JWT = new Token\JsonWebToken($this->accessToken);
      if ($JWT->getExpireDateInSeconds(false) < 0) {
        $this->accessToken = $this->getAccessToken();
      }
    }

    return $this;
  }
}
