<?php
namespace DashApi\Transport\Token\OAuth2;

use DashApi\Security\Signature\JsonWebSignature;
use DashApi\Transport\Token\JWT\Claim;

use InvalidArgumentException;
/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */
final class AuthorizationCode extends JsonWebSignature
{
  const REQUIRED_CLAIMS = [
    Claim\IssuedAtClaim::NAME,
    Claim\JWTIDClaim::NAME,
    Claim\IssuerClaim::NAME,
    Claim\CompanyCodeClaim::NAME,
    Claim\FacilityIDClaim::NAME,
  ];
  
  public function validate() {
    
    $this->jwt->validate();
  
    foreach (static::REQUIRED_CLAIMS as $key) {
      if (!array_key_exists($key, $this->jwt->getClaims())) {
        throw new InvalidArgumentException(
          sprintf(
            "Invalid Request Token - Authorization request missing required `claims.$key` claim. Claims received: %s",
            print_r($this->jwt->getClaims(), true)
          )
        );
      }
    }
    
    // @todo: Signature validation on $this
  }
}