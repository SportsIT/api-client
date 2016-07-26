<?php
namespace DashApi\Transport\Token\OAuth2;

use DashApi\Transport\Token\JsonWebToken;
use DashApi\Transport\Token\JWT\Claim;

use InvalidArgumentException;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/25/16]
 */

final class AuthorizationToken extends JsonWebToken
{
  const REQUIRED_CLAIMS = [
    Claim\JWTIDClaim::NAME,
    Claim\AudienceClaim::NAME,
    Claim\IssuerClaim::NAME,
    Claim\SubjectClaim::NAME,
    Claim\IssuedAtClaim::NAME,
  ];
  
  public function validate() {
    parent::validate();
  
    foreach (static::REQUIRED_CLAIMS as $key) {
      //$claims = $this->getClaims()->toArray();
      if ($this->getClaim($key) == null) {
        throw new InvalidArgumentException(
          sprintf(
            "Invalid Request Token - Authorization request missing required `claims.$key` claim. Claims received: %s",
            print_r($this->getClaims(), true)
          )
        );
      }
    }
    
    
  }
}