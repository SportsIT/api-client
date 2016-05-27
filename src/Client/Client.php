<?php
namespace DashApi\Client;

//use DashApi\Transport\Token\AbstractToken;
use DashApi\Utility\Json;

use DashApi\Transport\Token;
use DashApi\Security\Signature\JSONWebSignature;
use DashApi\Transport\Token\OAuth2;

use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Client
 *
 * ### Overview
 *
 * @package DashApi\Client
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
   * @var \DashApi\Transport\Token\JSONWebToken
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
        'authorizations' => \SIT_Authority::employeeAuthorityActions($employeeID, 1 /** @todo: Tim Turner -> why is this defaulting to facility ID # 1? */),
        'eid' => ($employeeID === null) // Private Claim
          ? '-1'
          : $employeeID
      ];
    }
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
          'Content-Type' => 'application/json',
          'Authorization' => ucfirst($this->authTokenType) . ' ' . $this->accessToken
        ]
      ]
    );
    
    return $result->getBody();
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
        throw new \LogicException('Could not build authorization request token. Error: '.$e->getMessage(),null,$e);
      }
    }
  
    $result = (new GuzzleClient)->post(
      $this->getTokenCreateUrl(),
      [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $this->getJsonAPIRequestBody(),
        'verify' => false // @todo: fix for self-signed cert
      ]
    );
    // @todo: add try-catch for \GuzzleHttp\Exception\RequestException
    //try {
    //  // GuzzleClient request
    //} catch (\GuzzleHttp\Exception\RequestException $e ) {
    //  echo (string) $e->getResponse()->getBody());
    //}
  
    $responseData = Json::decode((string) $result->getBody());
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
    
    $this->jsonWebToken = new Token\JSONWebToken($tokenData);
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
      $JWT = new Token\JSONWebToken($this->accessToken);
      if ($JWT->getExpireDateInSeconds(false) < 0) {
        $this->accessToken = $this->getAccessToken();
      }
    }
  }
}