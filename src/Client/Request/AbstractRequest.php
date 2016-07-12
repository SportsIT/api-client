<?php
namespace DashApi\Client\Request;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */

abstract class AbstractRequest {
  protected $uri;
  protected $header;
  protected $content;
}