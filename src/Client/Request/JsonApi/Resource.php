<?php
namespace DashApi\Client\Request\JsonApi;

use DashApi\Client\Request\AbstractRequest;
use DashApi\Utility\Json;

use InvalidArgumentException;

/**
 * Created by Tim Turner <tim.turner@sports-it.com>
 * Created on: [7/11/16]
 */

class Resource extends AbstractRequest
{
  const SCHEMA_DATA_KEY_STRING = 'data';
  const SCHEMA_TYPE_KEY_STRING = 'type';
  const SCHEMA_ID_KEY_STRING = 'id';
  const SCHEMA_ATTRIBUTES_KEY_STRING = 'attributes';
  
  const SCHEMA_DATA_FORMAT = 'json';
  
  const SCHEMA_HEADER_VALID_KEYS = ['Content-Type', 'Authorization'];
  
  const DEFAULT_HEADER = [
    'Content-Type' => 'application/json'
  ];
  
  protected $resource;
  protected $body;
  
  protected $type;
  protected $id;
  protected $attributes;
  
  // @todo: What should $id resolve to, how is it used internally?
  public function __construct($resource, $content, $header = []) {
    
    $this->validate($resource, $content, $header);
    
    // Build header from static::DEFAULT_HEADER.
    // If `authorization` passed, set `Authorization` header.
    // Merge in any passed header fields.
    $this->header = array_merge(static::DEFAULT_HEADER, $header);
    
    $this->type = $content['type'];
    $this->id = $content['id'];
    $this->attributes = $content['attributes'];
    
    $this->body = $this->buildBody();
  }
  
  public function __toString() {
    return $this->getBody(static::SCHEMA_DATA_FORMAT);
  }
  
  public function authorize($authorization) {
    
    if (substr($authorization, 0, 7 != 'Bearer ')) {
      throw new InvalidArgumentException(
        sprintf(
          "JsonApi resource request invalid `authorization` argument. Expected HTTP Authorization Scheme: (OAuth2) Bearer of the format 'Bearer `ACCESS_TOKEN`', received: %s",
          print_r($authorization, true)
        )
      );
    }
    
    // Set HTTP Authorization header field to 'Bearer `ACCESS_TOKEN`'
    $this->header['Authorization'] = 'Bearer ' . substr($authorization, 7);
  }
  
  public function getHeader() {
    return $this->header;
  }
  
  public function getBody($format = 'array') {
    $output = "";
    
    if ($format == 'json') {
      $output = json_encode($this->body);
    } else if ($format == 'array') {
      $output = $this->body;
    }
    
    return $output;
  }
  
  protected function buildBody() {
    return [
      static::SCHEMA_DATA_KEY_STRING => [
        static::SCHEMA_TYPE_KEY_STRING       => $this->type,
        static::SCHEMA_ID_KEY_STRING         => $this->id,
        static::SCHEMA_ATTRIBUTES_KEY_STRING => $this->attributes
      ]
    ];
  }
  
  protected function validate(&$uriResourcePath, &$content, &$header) {
    try {
      
      // Validate URI Resource Path, only allow alphanumeric, '.', '/'
      // Valid examples: '/products', 'products', '/products/categories', 'products.categories'
      // Invalid examples: 'http://server/products', 'products?filter=myfilter'
      $uriResourcePath = str_replace('.', '/', $uriResourcePath);
      if (empty($uriResourcePath) || !ctype_alnum(str_replace('/', '', $uriResourcePath))) {
        throw new InvalidArgumentException(
          sprintf(
            "JsonApi resource request invalid `resource` argument. Expected resource dotpath or URI path, received: %s",
            print_r($content, true)
          )
        );
      } else {
        if (substr($uriResourcePath, 0, 1) !== '/') {
          $uriResourcePath = '/' . $uriResourcePath;
        }
      }
      
      // Validate Header - For now only allowing specific set of headers to be set. @see static::SCHEMA_HEADER_VALID_KEYS
      foreach ($header as $key => $value) {
        if (!in_array($key, static::SCHEMA_HEADER_VALID_KEYS)) {
          throw new InvalidArgumentException(
            sprintf(
              "JsonApi resource request invalid `header` argument. Expected valid header field name from the set SCHEMA_HEADER_VALID_KEYS, received: %s",
              print_r($key, true)
            )
          );
        }
      }
      
      if (!is_array($content) || empty($content)) {
        throw new InvalidArgumentException(
          sprintf(
            "JsonApi resource request invalid `data` argument. Expected non-empty (array) value, received: %s",
            print_r($content, true)
          )
        );
      }
      
      foreach ([static::SCHEMA_TYPE_KEY_STRING, static::SCHEMA_ID_KEY_STRING, static::SCHEMA_ATTRIBUTES_KEY_STRING] as $key) {
        if (!array_key_exists($key, $content)) {
          throw new InvalidArgumentException(
            sprintf(
              "JsonApi resource request invalid `data` argument. Expected `data` to contain keys: `type`, `id`, `attributes`, received: %s",
              print_r($content, true)
            )
          );
        }
      }
      
      if (empty($content['type']) || !ctype_alnum(str_replace([':','-'], '', $content['type']))) {
        throw new InvalidArgumentException(sprintf("JsonApi resource request invalid `data.type` argument, expected value 'json', received: %s", print_r($content['type'], true)));
      }
      
      if (empty($content) || !is_numeric($content['id'])) {
        throw new InvalidArgumentException(sprintf("JsonApi resource request invalid `data.id` argument. Expected (int) or (string) numerical value, received: %s", print_r($content['id'], true)));
      }
      
      if (!is_array($content['attributes']) || empty($content['attributes'])) {
        throw new InvalidArgumentException(sprintf("JsonApi resource request invalid `data.attributes` argument. Expected non-empty (array) value, received: %s", print_r($content['attributes'], true)));
      }
      
    } catch (InvalidArgumentException $e) {
      // @todo: Add additional logging, etc here. For now, just re-throwing.
      //throw new InvalidArgumentException(...);
      throw $e;
    }
  }

}