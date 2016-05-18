<?php
/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/11/16]
 */

namespace DashApi\Transport\Token;


interface TokenInterface extends \Serializable
{
  public function __toString();
  
  public function getID();
  
  //public function getRoles();
  
  //public function getCredentials();
  
  //public function getClient();
  
  //public function setClient();
  
  //public function getUsername();
  
  //public function isAuthenticated();
  
  //public function setAuthenticated($isAuthenticated);
  
  //public function eraseCredentials();
  
  public function getAttributes();
  
  public function setAttributes(array $attributes);
  
  public function hasAttribute($name);
  
  public function getAttribute($name);
  
  public function setAttribute($name, $value);
}