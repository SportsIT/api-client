<?php
namespace DashApi\Transport\Token\OAuth2;

use Carbon\Carbon;
use DashApi\Transport\Token\JsonWebToken;
use DashApi\Transport\Token\JWT\Claim;

use InvalidArgumentException;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */

class Access extends JsonWebToken
{
  const REQUIRED_CLAIMS = [
    Claim\JWTIDClaim::NAME,
    Claim\AudienceClaim::NAME,
    Claim\IssuerClaim::NAME,
    Claim\SubjectClaim::NAME,
    Claim\IssuedAtClaim::NAME,
    Claim\NotBeforeClaim::NAME,
    Claim\ExpirationTimeClaim::NAME,
    // @todo: deprecate after switch to using Claim\SubjectClaim
    Claim\CompanyCodeClaim::NAME,
    Claim\FacilityIDClaim::NAME,
    Claim\EmployeeIDClaim::NAME
  ];
  
  public function validate() {
  
    foreach (static::REQUIRED_CLAIMS as $key) {
      if (!array_key_exists($key, $this->getClaims())) {
        throw new InvalidArgumentException(
          sprintf(
            "Invalid Access Token - Access token missing required `claims.$key` claim. Claims received: %s",
            print_r($this->getClaims(), true)
          )
        );
      }
    }
    
    parent::validate();
    
    // (exp) Expiration Claim handled already in parent class
    
    if (Carbon::now()->lt(
      Carbon::createFromTimestamp(
        $this->getClaim(Claim\NotBeforeClaim::NAME)->value
      )
    )) {
      throw new InvalidArgumentException("Invalid Token - Token is not currently valid");
    }
  }
}