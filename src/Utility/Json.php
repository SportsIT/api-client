<?php
namespace DashApi\Utility;

/**
 * Class Json
 *
 * @package SIT\Utility
 * @author Nate Strandberg <nate@sports-it.com>
 */
class Json {
  
  /**
   * @param mixed $data
   * @param int $flags Bitmask consisting of:
   *  - JSON_HEX_QUOT
   *  - JSON_HEX_TAG
   *  - JSON_HEX_AMP
   *  - JSON_HEX_APOS
   *  - JSON_NUMERIC_CHECK
   *  - JSON_PRETTY_PRINT
   *  - JSON_UNESCAPED_SLASHES
   *  - JSON_FORCE_OBJECT
   *  - JSON_UNESCAPED_UNICODE
   * @return string
   */
  public static function encode($data, $flags = null) {
    $encoded = json_encode($data, $flags);

    // Handle any JSON encoding errors..
    $error = json_last_error();
    if ($error !== JSON_ERROR_NONE) {
      static::handleDecodeError($error);
    }

    return $encoded;
  }

  /**
   * Decodes a JSON string.
   *
   * @param string $json The json string being decoded.
   * @param bool $associative When TRUE, returned objects will be converted into associative arrays.
   * @return object|array
   *
   * @throws \LogicException If the JSON cannot be decoded.
   */
  public static function decode($json, $associative = false) {
    $decoded = json_decode($json, $associative);

    // Handle any JSON decoding errors..
    $error = json_last_error();
    if ($error !== JSON_ERROR_NONE) {
      static::handleDecodeError($error);
    }

    return $decoded;
  }

  /**
   * @param int $error
   */
  protected static function handleDecodeError($error) {

    switch ($error) {
      case JSON_ERROR_DEPTH:
        $reason = 'The maximum stack depth has been exceeded';
        break;

      case JSON_ERROR_STATE_MISMATCH:
        $reason = 'Invalid or malformed JSON';
        break;

      case JSON_ERROR_CTRL_CHAR:
        $reason = 'Control character error, possibly incorrectly encoded';
        break;

      case JSON_ERROR_SYNTAX:
        $reason = 'Syntax error';
        break;

      case JSON_ERROR_UTF8:
        $reason = 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;

      case JSON_ERROR_RECURSION:
        $reason = 'One or more recursive references in the value to be encoded';
        break;

      case JSON_ERROR_INF_OR_NAN:
        $reason = 'One or more NAN or INF values in the value to be encoded';
        break;

      case JSON_ERROR_UNSUPPORTED_TYPE:
        $reason = 'A value of a type that cannot be encoded was given';
        break;

      default:
        $reason = 'Unknown error has occurred';
        break;
    }

    throw new \LogicException(sprintf('Unable to process JSON: %s', $reason));
  }
}
