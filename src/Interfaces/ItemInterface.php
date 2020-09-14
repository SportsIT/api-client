<?php

namespace Dash\Interfaces;

use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;

interface ItemInterface extends DataInterface {
  /**
   * @return string|null
   */
  public function getId();

  /**
   * @return bool
   */
  public function hasId(): bool;

  /**
   * @return bool
   */
  public function isNew(): bool;

  /**
   * @param string|null $id
   *
   * @return $this
   */
  public function setId(string $id = null);

  /**
   * @return string
   */
  public function getType(): string;

  /**
   * @return bool
   */
  public function hasType(): bool;

  /**
   * @param string $type
   *
   * @return $this
   */
  public function setType(string $type);

  /**
   * @param Links|null $links
   *
   * @return $this
   */
  public function setLinks(Links $links = null);

  /**
   * @return Links|null
   */
  public function getLinks();

  /**
   * @param Meta|null $meta
   *
   * @return $this
   */
  public function setMeta(Meta $meta = null);

  /**
   * @return Meta|null
   */
  public function getMeta();

  /**
   * @param array $attributes
   *
   * @return $this
   */
  public function fill(array $attributes);

  /**
   * @param array $attributes
   *
   * @return mixed
   */
  public function forceFill(array $attributes);

  /**
   * @return array
   */
  public function getAttributes();

  /**
   * @param $key
   *
   * @return mixed
   */
  public function getAttribute($key);

  /**
   * @param $key
   * @param $value
   */
  public function setAttribute($key, $value);

  /**
   * @param $key
   *
   * @return bool
   */
  public function hasAttribute($key): bool;

  /**
   * @return bool
   */
  public function hasAttributes(): bool;

  /**
   * @return bool
   */
  public function hasRelationships(): bool;

  /**
   * Set the specific relationship in the model.
   *
   * @param string                   $relation
   * @param DataInterface|false|null $value
   * @param Links|null               $links
   * @param Meta|null                $meta
   *
   * @return $this
   */
  public function setRelation(string $relation, $value = false, Links $links = null, Meta $meta = null);

  /**
   * @return HasOneRelation[]|HasManyRelation[]
   */
  public function getRelations(): array;

  /**
   * @param string $name
   * @return HasOneRelation|HasManyRelation|null
   */
  public function getRelation(string $name);

  /**
   * @param string $name
   * @return DataInterface|null
   */
  public function getRelationValue(string $name);

  /**
   * @param string $name
   * @return bool
   */
  public function hasRelation(string $name): bool;

  /**
   * @param string $name
   * @return $this
   */
  public function unsetRelation(string $name);

  /**
   * @param string $name
   * @return HasOneRelation
   */
  public function hasOne(string $name): HasOneRelation;

  /**
   * @param string $name
   * @return HasManyRelation
   */
  public function hasMany(string $name): HasManyRelation;
}
