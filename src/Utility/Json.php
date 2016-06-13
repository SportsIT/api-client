<?php
namespace DashApi\Utility;

/**
 * Class Json
 *
 * ### Overview
 * This class provides JSON encoding/decoding with simple error handling.
 *
 * @package DashApi\Utility
 * @author Nate Strandberg <nate@dashplatform.com>
 */
final class Json {

  /**
   * Can't touch this.
   *
   */
  private function __construct() {
  }

  /**
   * Can't touch this.
   *
   */
  private function __clone() {
  }

  /**
   * Returns the JSON representation of a value.
   *
   * @param mixed $value The data to encode.
   * @param int $options Bitmask consisting of:
   *  - `JSON_HEX_QUOT`
   *    All " are converted to \u0022.
   *
   *  - `JSON_HEX_TAG`
   *    All < and > are converted to \u003C and \u003E.
   *
   *  - `JSON_HEX_AMP`
   *    All &s are converted to \u0026.
   *
   *  - `JSON_HEX_APOS`
   *    All ' are converted to \u0027.
   *
   *  - `JSON_NUMERIC_CHECK`
   *    Encodes numeric strings as numbers.
   *
   *  - `JSON_PRETTY_PRINT`
   *    Use whitespace in returned data to format it.
   *
   *  - `JSON_UNESCAPED_SLASHES`
   *    Don't escape /.
   *
   *  - `JSON_FORCE_OBJECT`
   *    Outputs an object rather than an array when a non-associative array is used.
   *    Especially useful when the recipient of the output is expecting an object
   *    and the array is empty.
   *
   *  - `JSON_UNESCAPED_UNICODE`
   *    Encode multibyte Unicode characters literally (default is to escape as \uXXXX).
   *
   *  - `JSON_BIGINT_AS_STRING`
   *    Encodes large integers as their original string value.
   *
   *  - `JSON_PARTIAL_OUTPUT_ON_ERROR`
   *    Substitute some unencodable values instead of failing.
   *
   *  - `JSON_PRESERVE_ZERO_FRACTION`
   *    Ensures that float values are always encoded as a float value.
   *
   * @param int $depth The maximum depth. Must be greater than zero.
   * @return string Returns a JSON encoded string.
   *
   * @throws \RuntimeException If the JSON cannot be encoded.
   */
  public static function encode($value, $options = 0, $depth = 512) {
    $encoded = json_encode($value, $options, $depth);

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
   * @param int $depth User specified recursion depth.
   * @param int $options Bitmask consisting of:
   *  - `JSON_BIGINT_AS_STRING`
   * @return object|array
   *
   * @throws \RuntimeException If the JSON cannot be decoded.
   */
  public static function decode($json, $associative = false, $depth = 512, $options = 0) {
    $decoded = json_decode($json, $associative, $depth, $options);

    // Handle any JSON decoding errors..
    $error = json_last_error();
    if ($error !== JSON_ERROR_NONE) {
      static::handleDecodeError($error);
    }

    return $decoded;
  }

  /**
   * Handles JSON encoding/decoding errors.
   *
   * @param int $error The JSON error code.
   *
   * @throws \RuntimeException With error description.
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

    throw new \RuntimeException(sprintf('Unable to process JSON: %s', $reason));
  }
}
