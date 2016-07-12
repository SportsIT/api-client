<?php
namespace DashApi\Client;

use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleClient;

use DashApi\Utility\Json;
use DashApi\Transport\Token;
use DashApi\Transport\Token\OAuth2;
use DashApi\Client\Request;
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
   * DASH Platform Company Code
   * 
   * This is the same company code used to login to DASH Platform.
   * 
   * @var string
   */
  protected $companyCode;
  
  /**
   * DASH Platform API Key
   *
   * API secret key used to sign requests to the DASH API service.
   * A company's key can be found in DASH Platform account.
   * After logging in, navigate to: Settings > Company > API (tab)
   * 
   * @var string
   */
  protected $secret;

  /**
   * The datetime at and after which this token is expired.
   * 
   * @var Carbon|string|null
   */
  protected $expireDatetime;
  
  /**
   * Used to specify scope when requesting access token from DASH API authorization service.
   * (NOTE: Unavailable for use in public domain. 
   * Employee ID maps to that employee's set of authorizations.
   * 
   * @var string|int
   */
  protected $employeeID;
  
  /**
   * Used to specify scope when requesting access token from DASH API authorization service.
   * (NOTE: Unavailable for use in public domain.
   * Customer ID maps to that customer's set of authorizations.
   * 
   * @var string|int
   */
  protected $customerID;
  
  /**
   * Database facility ID to get requested data for.
   * 
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
   * @var object 
   * 
   * @var string
   */
  protected $defaultTokenRequest;
  
  /**
   * @var string
   */
  protected $defaultRequestClaims;
  
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
   * DASH API Client constructor.
   * 
   * Please note:
   *  - Client `secret` is in your DASH Platform account at Settings > Company > API (tab).
   *  - Employee ID (eid), Customer ID (cid) claims are not available for auth in public domain.
   *
   * @param string $companyCode       This is the same company code used to login to DASH Platform.
   *                                  
   * @param int $facilityID           Database ID for facility.
   *                                  
   * @param string $secret            API Secret key found in DASH Platform: Settings > Company > API (tab)
   *                                  
   * @param array|null $authorization Array matching set of the following:
   *                                  Scope Auth:    ['scope' => (string) CSV api resource dotpaths]
   *                                  
   * @param string|null $apiUrl       The DASH API URL where requests should be sent.
   *                                  Default is: `https://apps.dashplatform.com/dash/api`
   *                                  
   * @param string|array|null $header JWT JOSE Header
   *                                  Valid types are JSON object as a string or associative array.
   *                                  Default: `['typ' => 'JWT', 'alg' => 'HS256']`
   *                                  More information: {@link https://tools.ietf.org/html/rfc7519#section-5 JOSE Header}
   *                                  
   * @param array|array|null $claims  JWT Claims Set
   *                                  Valid types are JSON object as a string or associative array.
   *                                  Default claims set:
   *                                    Registered Claims:
   *                                      'iat' - (int)    Current timestamp.
   *                                      'jti' - (string) Random 32 chars (256 bits) of binary encoded in base64.
   *                                      'iss' - (string) SERVER_NAME provided by superglobal $_SERVER.
   *                                      'exp' - (int)    Current timestamp + 86400 seconds (1 day).
   *                                    Private Claims:
   *                                      'cco' - (string) Company code of organization (same as DASH Platform login).
   *                                      'fid' - (int)    Facility ID to get requested data for.
   *                                      'eid' - (int)    Employee ID (NOTE: Not available for public use)
   *                                      'cid' - (int)    Customer ID (NOTE: Not available for public use)
   *                                  More information: {@link https://tools.ietf.org/html/rfc7519#section-4 JWT Claims}
   * 
   * Examples:
   * 
   * $api = new \DashApi\Client\Client('mycompanycode', 123, 'companysecretAPIkey', ['scope' => 'products.read,events.read']);
   *
   * $api = new \DashApi\Client\Client('companyX', 1, '0a1b2c3d4e5f0a1b2c3d4e5f0a1b2c3d4e5f0a1b2c3d4e5f0a1b2c3d4e5f0a1b', ['scope' => 'products']);
   * 
   */
  public function __construct($companyCode, $facilityID, $secret, $authorization = null, $apiUrl = null, $header = null, $claims = null) {
    $this->companyCode = $companyCode;
    $this->facilityID = $facilityID;
    $this->secret = $secret;
    
    if ($authorization['employee'] !== null) {
      if (is_numeric($authorization['employee'])) {
        $this->employeeID = $authorization['employee'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.employee parameter. Expected (int) or (string) numeric, got: %s", print_r($authorization['employee'], true)));
      }
    }
    
    if ($authorization['customer'] !== null) {
      if (is_numeric($authorization['customer'])) {
        $this->customerID = $authorization['customer'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.customer parameter. Expected (int) or (string) numeric, got: %s", print_r($authorization['customer'], true)));
      }
    }
  
    if ($authorization['scope'] !== null) {
      if (is_string($authorization['scope'])) {
        $this->scope = explode(',', $authorization['scope']);
      } elseif (is_array($authorization['scope'])) {
        $this->scope = $authorization['scope'];
      } else {
        throw new \RuntimeException(sprintf("Invalid format for authorization.scope parameter. Expected (string) csv or (array) of dot paths, got: %s", print_r($authorization['scope'], true)));
      }
    }
  
    if ($authorization['auth'] !== null) {
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
    
    // Default JOSE header
    if ($header !== null) {
      $this->header = $header;
    } else {
      $this->header = [
        // Type - Header parameter declaring token type (from IANA.MediaTypes).
        'typ' => 'JWT',
        
        // Algorithm - Header parameter identifying cryptographic algorithm used for JWS (signature)
        'alg' => 'HS256'
      ];
    }
    
    // Default claims set
    if ($claims !== null) {
      $this->claims = $claims;
    } else {
      $this->claims = [
        // Registered Claims:
        
        // Issued At Time - (int) Current timestamp specifying when token was created.
        'iat' => time(), 
        
        // JWT ID - (string) Random 32 characters (256 bits) of binary encoded in base64.
        'jti' => base64_encode(mcrypt_create_iv(32)),
        
        // Issuer - (string) SERVER_NAME provided by superglobal $_SERVER.
        'iss' => $_SERVER['SERVER_NAME'],
        
        // Expiration Time - (int) Current timestamp + 86400 seconds (1 day).
        'exp' => time() + static::REQUEST_EXPIRE_TIME,
        
        // Private Claims:
        
        // Company Code - (string) Company code of organization (same as DASH Platform login).
        'cco' => $this->companyCode,
        
        // Facility ID - (int) Company code of organization (same as DASH Platform login).
        'fid' => $this->facilityID,
      ];
  
      // Employee ID - (int) Employee ID (NOTE: Not available for public use).
      if ($this->employeeID) {
        $this->claims['eid'] = $this->employeeID;
      }
      
      // Customer ID - (int) Customer ID (NOTE: Not available for public use).
      if ($this->customerID) {
        $this->claims['cid'] = $this->customerID;
      }
  
      // Scope - (string) Comma separated list of api resource dotpaths.
      if ($this->scope) {
        $this->claims['scope'] = $this->scope;
      }
      
      // Authorizations - (string) Comma separated list of SIT_Authorization flags.
      if ($this->auth) {
        // @todo: rename claim 'authorizations' -> 'auth'
        $this->claims['authorizations'] = $this->auth;
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
   * Example of how a server-side GET request is constructed.
   * 
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
   * Gets an access token from the API authentication service.
   * Use received access token in future resource requests via HTTP Authorization header:
   * `Authorization: Bearer <JWS Access Token>`
   *
   * @return string A compact JWS in the format `base64(header).base64(payload).base64(signature)`.
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
  
    if (empty($this->jsonWebSignature)) {
      $this->jsonWebSignature = new JsonWebSignature(
        $this->jsonWebToken,
        $this->secret
      );
    }
  
    $authCode = new OAuth2\AuthorizationCode($this->jsonWebToken, $this->secret);
    $tokenRequest = new Request\OAuth2\Token($authCode);
    //$tokenRequest = new Request\OAuth2\Token(
    //  $this->header,
    //  $this->claims,
    //  $this->jsonWebSignature->getSignature()
    //);
    
    $result = (new GuzzleClient)->post(
      $this->getTokenCreateUrl(),
      [
        'headers'     => $tokenRequest->getHeader(),
        'body'        => (string) $tokenRequest,
        'verify'      => false, // @todo: fix for self-signed cert
        'http_errors' => false  // Set to false to disable throwing exceptions on HTTP protocol errors
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

      // Add the response data to the exception if we're debugging...
      if ($this->debugMode) {
        $message .= "\r\n" . print_r($responseData, true);
      }
      throw new \RuntimeException($message);
    }
    
    $this->accessToken = $responseData->data[0]->attributes->access_token;
    return $responseData->data[0]->attributes->access_token;
  }
  
  /**
   * Get JWS token used for requesting an access token from DASH API authentication service.
   *
   * This JWT request token looks similar to the JWT access token, but serves a different purpose.
   * This is only used once in initial request to get access token.
   * 
   * @param array|string|null $header JWT JOSE Header
   * @param array|string|null $claims JWT Claims Set
   * 
   * @return string A compact JWS in the format `base64(header).base64(payload).base64(signature)`.
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
    if (empty($this->expireDatetime)) {
      $this->expireDatetime = Carbon::now()->addSeconds(Token\AbstractToken::EXPIRE_TIME_DEFAULT);
    }
    return $this->expireDatetime;
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
   * Get the expire date in a human readable format in the current locale.
   *
   * When comparing expire datetime in the past to default now:
   * 1 hour ago
   * 5 months ago
   *
   * When comparing expire datetime in the future to default now:
   * 1 hour from now
   * 5 months from now
   * 
   * @return string
   */
  public function getExpireDateForHumans() {
    return $this->getExpireDate()->diffForHumans();
  }
  
  /**
   * Set the datetime at and after which this token is expired.
   * 
   * @param Carbon|string $expireDatetime
   * @return $this
   */
  public function setExpireDate($expireDatetime) {
    if (is_int($expireDatetime)) {
      // DateTime construct will cast to string on init
      $expireDatetime = Carbon::createFromTimestamp($expireDatetime);
    } elseif (is_string($expireDatetime)) {
      $expireDatetime = Carbon::parse($expireDatetime);
    }
    
    $this->expireDatetime = $expireDatetime;
    return $this;
  }
  
  /**
   * Builds the body used in request to DASH API authorization service for access token.
   * Format of request body follows JSON API specification:
   * @link http://jsonapi.org/format/
   * 
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
   * Get configured URL for DASH API service.
   * 
   * @return string
   */
  protected function getApiUrl() {
    return $this->apiUrl;
  }
  
  /**
   * Get configured URL for DASH API authorization service.
   * 
   * @return string
   */
  protected function getApiAuthUrl() {
    return $this->getApiUrl() . static::REQUEST_PATH_SEG_AUTH_TOKENS;
  }
  
  /**
   * Get configured URL to POST request for a new access token.
   * 
   * @return string 
   */
  protected function getTokenCreateUrl() {
    return $this->getApiAuthUrl() . '/create';
  }
  
  /**
   * Check if a valid access token has been set. If not, request a new access token.
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
