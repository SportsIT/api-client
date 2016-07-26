<?php
namespace DashApi\Transport\Token\OAuth2;

use DashApi\Transport\Token\JsonWebToken;
use DashApi\Transport\Token\JWT\Claim;

use InvalidArgumentException;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */

final class Access extends JsonWebToken
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
      if (!array_key_exists($key, $this->getClaims())) {
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