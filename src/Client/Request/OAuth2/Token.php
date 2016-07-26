<?php
namespace DashApi\Client\Request\OAuth2;
  
use DashApi\Client\Request\JsonApi\Resource;
use DashApi\Transport\Token\JsonWebToken;
use DashApi\Transport\Token\OAuth2\AuthorizationCode;

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
 * Required JWT Claims for valid Authorization Request Token:
 *   - 
 *
 *
 * @package DashApi\Transport\OAuth2\Token
 * @author  Tim Turner <tim.turner@sports-it.com>
 */
final class Token extends Resource
{
  public function __construct(AuthorizationCode $code) {
    $headers = $code->getJsonWebToken()->getHeaders();
    $claims = $code->getJsonWebToken()->getClaims();
    
    parent::__construct(
      'auth/tokens',
      [
        'type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'id' => 1,
        'attributes' => [
          'header' => $code->getJsonWebToken()->getHeaders()->toArray(),
          'claims' => $code->getJsonWebToken()->getClaims()->toArray(),
          'signature' => $code->getSignature()
        ]
      ]
    );
  }
  
  public static function fromArguments($header, $claims, $secret) {
    $JWT = new JsonWebToken(['header' => $header, 'payload' => $claims]);
    $code = new AuthorizationCode($JWT, $secret);
    return new static($code);
  }
  
  public static function fromJsonWebToken(JsonWebToken $JWT, $secret) {
    $code = new AuthorizationCode($JWT, $secret);
    return new static($code);
  }
}

