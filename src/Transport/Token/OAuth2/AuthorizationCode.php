<?php
namespace DashApi\Transport\Token\OAuth2;

use DashApi\Security\Signature\JsonWebSignature;
use DashApi\Transport\Token\JWT\Claim;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */
final class AuthorizationCode extends JsonWebSignature
{
  public function __construct(AuthorizationToken $token, $secret) {
    parent::__construct($token, $secret);
  }
}