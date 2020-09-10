<?php

namespace Dash\Models;

use Dash\Concerns\HasLinks;
use Dash\Concerns\HasMeta;

class Error {
  use HasLinks;
  use HasMeta;

  /**
   * @var string|null
   */
  protected $id;

  /**
   * @var string|null
   */
  protected $status;

  /**
   * @var string|null
   */
  protected $code;

  /**
   * @var string|null
   */
  protected $title;

  /**
   * @var string|null
   */
  protected $detail;

  /**
   * @var ErrorSource|null
   */
  protected $source;

  /**
   * @param string|null      $id
   * @param Links|null       $links
   * @param string|null      $status
   * @param string|null      $code
   * @param string|null      $title
   * @param string|null      $detail
   * @param ErrorSource|null $source
   * @param Meta|null        $meta
   */
  public function __construct(
    string $id = null,
    Links $links = null,
    string $status = null,
    string $code = null,
    string $title = null,
    string $detail = null,
    ErrorSource $source = null,
    Meta $meta = null
  ) {
    $this->id = $id;
    $this->links = $links;
    $this->status = $status;
    $this->code = $code;
    $this->title = $title;
    $this->detail = $detail;
    $this->source = $source;
    $this->meta = $meta;
  }

  /**
   * @return string|null
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string|null
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @return string|null
   */
  public function getCode() {
    return $this->code;
  }

  /**
   * @return string|null
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @return string|null
   */
  public function getDetail() {
    return $this->detail;
  }

  /**
   * @return ErrorSource|null
   */
  public function getSource() {
    return $this->source;
  }
}
