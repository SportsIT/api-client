<?php

namespace Dash\Concerns;

use Dash\Interfaces\DataInterface;
use Dash\Models\Collection;
use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;

trait HasRelations {
  protected $relations = [];

  /**
   * Create a singular relation to another item.
   *
   * @param string $name
   *
   * @return HasOneRelation
   */
  public function hasOne(string $name): HasOneRelation {
    if (!array_key_exists($name, $this->relations)) {
      $this->relations[$name] = new HasOneRelation($this, $name);
    }

    return $this->relations[$name];
  }

  /**
   * Create a plural relation to another item.
   *
   * @param string $name
   *
   * @return HasManyRelation
   */
  public function hasMany(string $name): HasManyRelation {
    if (!array_key_exists($name, $this->relations)) {
      $this->relations[$name] = new HasManyRelation($this, $name);
    }

    return $this->relations[$name];
  }

  public function getRelations(): array {
    return $this->relations;
  }

  public function getRelation(string $name) {
    return $this->relations[$name] ?? null;
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function hasRelation(string $name): bool {
    return array_key_exists($name, $this->relations);
  }

  /**
   * @param $name
   *
   * @return $this
   */
  public function unsetRelation(string $name) {
    unset($this->relations[$name]);

    return $this;
  }

  /**
   * Get the relationship data (included).
   *
   * @param string $name
   *
   * @return DataInterface|null
   */
  public function getRelationValue(string $name) {
    // If the "attribute" exists as a relationship on the model, we will return
    // the included items in the relationship
    if ($this->hasRelation($name)) {
      return $this->getRelation($name)->getIncluded();
    }

    return null;
  }

  /**
   * Set the specific relationship on the model.
   *
   * @param string                   $name
   * @param DataInterface|false|null $data
   * @param Links|null               $links
   * @param Meta|null                $meta
   *
   * @return $this
   */
  public function setRelation(string $name, $data = false, Links $links = null, Meta $meta = null) {
    if ($data instanceof Collection) {
      $relationObject = $this->hasMany($name);
    } else {
      $relationObject = $this->hasOne($name);
    }

    if ($data !== false) {
      $relationObject->dissociate();

      if ($data !== null) {
        $relationObject->associate($data);
      }
    }

    $relationObject->setLinks($links);
    $relationObject->setMeta($meta);

    return $this;
  }
}
