<?php
namespace SIT\DashApi;

use SIT\DashApi\Utility\Json;

use Guzzle\Http\Client as GuzzleClient;

use Transport\JWT;
use Security\Signature\JSONWebSignature;

/**
 * Class Client
 *
 * ### Overview
 *
 * @package SIT\DashApi
 * @author Tim Turner <tim.turner@sports-it.com>
 */
final class Client
{
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
   * @var string
   */
  protected $employeeID;
  
  /**
   * @var null Set on initialization to incoming $apiUrl if passed.
   *           Otherwise, set to: static::REQUEST_SCHEMA . '://' . static::REQUEST_HOST . static::REQUEST_PATH_SEG_API;
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
   * @var \Transport\JWT\JSONWebToken
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
   * Client constructor.
   *
   * @param string $companyCode
   * @param string $secret
   * @param string|null $employeeID
   * @param string|null $apiUrl
   * @param string|null $header
   * @param array|null $claims
   */
  public function __construct($companyCode, $secret, $employeeID = null, $apiUrl = null, $header = null, $claims = null) {
    $this->companyCode = $companyCode;
    //$this->secret = pack('H*', $secret);
    $this->secret = $secret;
    
    if ($employeeID !== null) {
      $this->employeeID = $employeeID;
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
        
        'iss' => $_SERVER['SERVER_NAME'], // client hostname / domain
        'exp' => time() + static::REQUEST_EXPIRE_TIME,
        'cco' => $companyCode, // Private Claim
        
        'eid' => ($employeeID === null) // Private Claim
          ? '-1'
          : $employeeID
      ];
    }
  }
  
  /**
   * @param string $resourcePath
   * @return \Guzzle\Http\EntityBodyInterface|string
   */
  public function get($resourcePath) {
    $this->validateAccessToken();
    
    $result = (new GuzzleClient)->get(
      $this->apiUrl . $resourcePath,
      [
        'Content-Type' => 'application/json', 
        'Authorization' => ucfirst($this->authTokenType) . ' ' . $this->accessToken
      ]
    );
    
    $response = $result->send();
    return $response->getBody(true);
  }
  
  /**
   * @return mixed
   *
   * @throws \LogicException Error when getting a request token
   * @throws \RuntimeException Response did not contain valid access token.
   */
  public function getAccessToken() {
    
    if (!$this->token) {
      try {
        $this->getAuthRequestToken();
      } catch (\Exception $e) {
        throw new \LogicException('No authorization request token has been created, did you call getAuthRequestToken() first?');
      }
    }
    
    $result = (new GuzzleClient)->post(
      $this->getTokenCreateUrl(), ['Content-Type' => 'application/json'],
      $this->getJsonAPIRequestBody(),
      [
        'verify' => false     // @todo: fix for self-signed cert
      ]
    );
    $response = $result->send();
    
    $responseData = Json::decode($response->getBody());
    if (empty($responseData->data[0]->attributes->access_token)) {
      throw new \RuntimeException('Response did not contain valid access token');
    }
    
    $this->accessToken = $responseData->data[0]->attributes->access_token;
    return $responseData->data[0]->attributes->access_token;
  }
  
  /**
   * @param null $header
   * @param null $claims
   * @return string
   * @throws \Exception
   */
  public function getAuthRequestToken($header = null, $claims = null) {
    $tokenData = new \stdClass();
    
    $tokenData->header = $header ?: $this->header;
    $tokenData->payload = $claims ?: $this->claims;
    
    $this->jsonWebToken = new JWT\JSONWebToken($tokenData);
    $this->jsonWebSignature = new JSONWebSignature($this->jsonWebToken, $this->secret);
    
    switch (static::REQUEST_TOKEN_TYPE) {
      case 'JWT':
        $this->token = (string)$this->jsonWebToken;
        break;
      
      case 'JWS':
        $this->token = (string)$this->jsonWebSignature;
        break;
      
      case 'JWE':
        // @TODO: Not implemented yet!
        throw new \Exception('JSON Web Encryption token requests have not been implemented yet, please reset static::REQUEST_TOKEN_TYPE');
        break;
      
      default:
        throw new \Exception('Invalid Request Token Type - REQUEST_TOKEN_TYPE must be set to a valid type');
        break;
    }
    
    return $this->token;
  }
  
  /**
   * @return mixed
   */
  protected function getJsonAPIRequestBody() {
    if (empty($this->jsonWebSignature)) {
      $this->jsonWebSignature = new JSONWebSignature($this->jsonWebToken, $this->secret);
    }
    return Json::encode(
      [
        'data' => [
          'type' => static::REQUEST_TYPE,
          'id' => 1,
          'attributes' => [
            'header' => $this->header,
            'claims' => $this->claims,
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
   */
  protected function validateAccessToken() {
    if (empty($this->accessToken)) {
      $this->accessToken = $this->getAccessToken();
    } else {
      $JWT = new JWT\JSONWebToken($this->accessToken);
      if ($JWT->getExpireDateInSeconds(false) < 0) {
        $this->accessToken = $this->getAccessToken();
      }
    }
  }
}