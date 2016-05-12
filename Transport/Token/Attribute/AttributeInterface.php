<?php
namespace DashApi\Transport\Token\Attribute;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [5/11/16]
 */
interface AttributeInterface extends \Serializable
{
  public function get();
  public function set($value);
}