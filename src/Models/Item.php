<?php

namespace Dash\Models;

use Dash\Builders\IndexRequestBuilder;
use Dash\Concerns\HasAttributes;
use Dash\Concerns\HasId;
use Dash\Concerns\HasLinks;
use Dash\Concerns\HasMeta;
use Dash\Concerns\HasRelations;
use Dash\Concerns\HasType;
use Dash\DocumentClient;
use Dash\DocumentClientFactory;
use Dash\DocumentFactory;
use Dash\Interfaces\DataInterface;
use Dash\Interfaces\ItemDocumentInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;
use Exception;
use Illuminate\Support\Str;
use Jenssegers\Model\Model;

/**
 * Class Item.
 *
 * @mixin IndexRequestBuilder
 */
class Item extends Model implements ItemInterface {
  use HasId;
  use HasType;
  use HasAttributes;
  use HasMeta;
  use HasLinks;
  use HasRelations;

  /**
   * @var DocumentClient|null
   */
  protected static $documentClient;

  /**
   * @return array
   */
  public function toJsonApiArray(): array {
    $data = [
      'type' => $this->getType(),
    ];

    if ($this->hasId()) {
      $data['id'] = $this->getId();
    }

    $attributes = $this->toArray();

    if (!empty($attributes)) {
      $data['attributes'] = $attributes;
    }

    $relationships = $this->getRelationships();

    if (!empty($relationships)) {
      $data['relationships'] = $relationships;
    }

    $links = $this->getLinks();

    if ($links !== null) {
      $data['links'] = $links->toArray();
    }

    $meta = $this->getMeta();

    if ($meta !== null) {
      $data['meta'] = $meta->toArray();
    }

    return $data;
  }

  /**
   * @return bool
   */
  public function isNew(): bool {
    return !$this->hasId();
  }

  /**
   * @param string $key
   *
   * @return DataInterface|mixed
   */
  public function getAttribute($key) {
    if ($this->hasAttribute($key) || $this->hasGetMutator($key)) {
      return parent::getAttribute($key);
    }

    return $this->getRelationValue($key);
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function hasAttribute($key): bool {
    return array_key_exists($key, $this->attributes);
  }

  /**
   * @return bool
   */
  public function hasAttributes(): bool {
    return !empty($this->toArray());
  }

  /**
   * @return array
   */
  public function getRelationships(): array {
    $relationships = [];

    foreach ($this->getRelations() as $name => $relation) {
      if (!$relation->hasIncluded()) {
        continue;
      }

      if ($relation instanceof HasOneRelation) {
        $relationships[$name]['data'] = null;

        if ($relation->getIncluded() !== null) {
          $relationships[$name]['data'] = [
            'type' => $relation->getIncluded()->getType(),
            'id' => $relation->getIncluded()->getId(),
          ];
        }
      } elseif ($relation instanceof HasManyRelation) {
        $relationships[$name]['data'] = [];

        foreach ($relation->getIncluded() as $item) {
          $relationships[$name]['data'][] = [
            'type' => $item->getType(),
            'id' => $item->getId(),
          ];
        }
      }

      $links = $relation->getLinks();

      if ($links !== null) {
        $relationships[$name]['links'] = $links->toArray();
      }

      $meta = $relation->getMeta();

      if ($meta !== null) {
        $relationships[$name]['meta'] = $meta->toArray();
      }
    }

    return $relationships;
  }

  /**
   * @return bool
   */
  public function hasRelationships(): bool {
    return !empty($this->getRelationships());
  }

  /**
   * @return IndexRequestBuilder
   */
  public function newBuilder(): IndexRequestBuilder {
    if ($this->getType() === null) {
      throw new \RuntimeException('Type must be set before a builder can be made');
    }

    return new IndexRequestBuilder(static::getDocumentClient(), $this->getType());
  }

  public static function ofType(string $resourceType) {
    return (new static())->setType($resourceType);
  }

  public static function setDocumentClient(DocumentClient $client) {
    static::$documentClient = $client;
  }

  public static function getDocumentClient() {
    // if the document client hasn't been set by the Client yet, we'll just build
    // an unauthenticated document client for now
    if (static::$documentClient === null) {
      static::$documentClient = (new DocumentClientFactory())->make();
    }

    return static::$documentClient;
  }

  public function getDocumentFactory() {
    return new DocumentFactory();
  }

  public function toDocument($withRelations = true): ItemDocumentInterface {
    return $this->getDocumentFactory()->make($this, $withRelations);
  }

  /**
   * Find an item by its id.
   *
   * @param string|int $id
   *
   * @throws Exception
   *
   * @return ItemDocumentInterface
   */
  public function find($id): ItemDocumentInterface {
    return $this->newBuilder()->one($id)->get();
  }

  /**
   * @throws Exception
   *
   * @return ItemDocumentInterface
   */
  public function save(): ItemDocumentInterface {
    if ($this->hasId()) {
      return $this->performUpdate(false);
    }

    return $this->performCreate(false);
  }

  /**
   * @throws Exception
   *
   * @return ItemDocumentInterface
   */
  public function push(): ItemDocumentInterface {
    if ($this->hasId()) {
      return $this->performUpdate(true);
    }

    return $this->performCreate(true);
  }

  /**
   * @param bool $withRelations
   *
   * @throws Exception
   *
   * @return ItemDocumentInterface
   */
  protected function performCreate($withRelations = true): ItemDocumentInterface {
    return $this->newBuilder()->create($this->toDocument($withRelations));
  }

  /**
   * @param false $withRelations
   *
   * @throws Exception
   *
   * @return ItemDocumentInterface
   */
  protected function performUpdate($withRelations = false): ItemDocumentInterface {
    return $this->newBuilder()
      ->one($this->getId())
      ->update($this->toDocument($withRelations));
  }

  /**
   * @param string $key
   *
   * @return mixed
   */
  public function __get($key) {
    if ($key === 'id') {
      return $this->getId();
    }

    return parent::__get($key);
  }

  /**
   * @param string $key
   * @param mixed  $value
   */
  public function __set($key, $value) {
    if ($key === 'id') {
      $this->setId($value);

      return;
    }

    parent::__set($key, $value);
  }

  /**
   * Determine if an attribute exists on the model.
   *
   * @param string $key
   *
   * @return bool
   */
  public function __isset($key) {
    if ($key === 'id') {
      return $this->hasId();
    }

    return parent::__isset($key) || $this->hasRelation($key) || $this->hasRelation(Str::snake($key));
  }

  /**
   * Unset an attribute on the model.
   *
   * @param string $key
   */
  public function __unset($key) {
    if ($key === 'id') {
      $this->id = null;
    }

    unset($this->attributes[$key]);
  }

  /**
   * Proxy all unknown method calls to a new IndexRequestBuilder
   *
   * @param $method
   * @param $args
   * @return mixed
   */
  public function __call($method, $args) {
    return $this->newBuilder()->{$method}(...$args);
  }
}
