<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Interfaces\DataInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Models\Item;

class ItemParser {
  /**
   * @var LinksParser
   */
  private $linksParser;

  /**
   * @var MetaParser
   */
  private $metaParser;

  /**
   * @param LinksParser $linksParser
   * @param MetaParser  $metaParser
   */
  public function __construct(LinksParser $linksParser, MetaParser $metaParser) {
    $this->linksParser = $linksParser;
    $this->metaParser = $metaParser;
  }

  /**
   * @param mixed $data
   *
   * @return ItemInterface
   */
  public function parse($data): ItemInterface {
    if (!is_object($data)) {
      throw new ValidationException(sprintf('Resource MUST be an object, "%s" given.', gettype($data)));
    }

    if (!property_exists($data, 'type')) {
      throw new ValidationException('Resource object MUST contain a type.');
    }

    if (!property_exists($data, 'id')) {
      throw new ValidationException('Resource object MUST contain an id.');
    }

    if (!is_string($data->type)) {
      throw new ValidationException(sprintf('Resource property "type" MUST be a string, "%s" given.', gettype($data->type)));
    }

    if (!is_string($data->id) && !is_numeric($data->id)) {
      throw new ValidationException(sprintf('Resource property "id" MUST be a string, "%s" given.', gettype($data->id)));
    }

    if (property_exists($data, 'attributes')) {
      if (!is_object($data->attributes)) {
        throw new ValidationException(sprintf('Resource property "attributes" MUST be an object, "%s" given.', gettype($data->attributes)));
      }

      if (property_exists($data->attributes, 'type') || property_exists($data->attributes, 'id') || property_exists($data->attributes, 'relationships') || property_exists($data->attributes, 'links')) {
        throw new ValidationException('These properties are not allowed in attributes: `type`, `id`, `relationships`, `links`.');
      }
    }

    $item = $this->getItemInstance($data->type);

    if (property_exists($data, 'id')) {
      $item->setId($data->id);
    }

    if (property_exists($data, 'attributes')) {
      $item->fill((array) $data->attributes);
    }

    if (property_exists($data, 'relationships')) {
      $this->setRelations($item, $data->relationships);
    }

    if (property_exists($data, 'links')) {
      $item->setLinks($this->linksParser->parse($data->links, LinksParser::SOURCE_ITEM));
    }

    if (property_exists($data, 'meta')) {
      $item->setMeta($this->metaParser->parse($data->meta));
    }

    return $item;
  }

  /**
   * @param string $type
   *
   * @return ItemInterface
   */
  private function getItemInstance(string $type): ItemInterface {
    return (new Item())->setType($type);
  }

  /**
   * @param ItemInterface $item
   * @param mixed         $data
   */
  private function setRelations(ItemInterface $item, $data) {
    if (!is_object($data)) {
      throw new ValidationException(sprintf('Resource property "relationships" MUST be an object, "%s" given.', gettype($data)));
    }

    if (property_exists($data, 'type') || property_exists($data, 'id')) {
      throw new ValidationException('These properties are not allowed in relationships: `type`, `id`.');
    }

    foreach ($data as $name => $relationship) {
      if ($item->hasAttribute($name)) {
        throw new ValidationException(sprintf('Relationship "%s" cannot be set because it already exists in Resource object.', $name));
      }

      if (!is_object($relationship)) {
        throw new ValidationException(sprintf('Relationship MUST be an object, "%s" given.', gettype($relationship)));
      }

      if (!property_exists($relationship, 'links') && !property_exists($relationship, 'data') && !property_exists($relationship, 'meta')) {
        throw new ValidationException('Relationship object MUST contain at least one of the following properties: `links`, `data`, `meta`.');
      }

      $value = false;

      if (property_exists($relationship, 'data')) {
        $value = null;

        if ($relationship->data !== null) {
          $value = $this->parseRelationshipData($relationship->data);
        }
      }

      $links = null;

      if (property_exists($relationship, 'links')) {
        $links = $this->linksParser->parse($relationship->links, LinksParser::SOURCE_RELATIONSHIP);
      }

      $meta = null;

      if (property_exists($relationship, 'meta')) {
        $meta = $this->metaParser->parse($relationship->meta);
      }

      $item->setRelation($name, $value, $links, $meta);
    }
  }

  /**
   * @param mixed $data
   *
   * @throws \InvalidArgumentException
   *
   * @return DataInterface
   */
  private function parseRelationshipData($data): DataInterface {
    if (is_array($data)) {
      $result = new Collection();

      foreach ($data as $identifier) {
        $result[] = $this->parseRelationshipData($identifier);
      }

      return $result;
    }

    if (!is_object($data)) {
      throw new ValidationException(sprintf('ResourceIdentifier MUST be an object, "%s" given.', gettype($data)));
    }

    if (!property_exists($data, 'type')) {
      throw new ValidationException('ResourceIdentifier object MUST contain a type.');
    }

    if (!property_exists($data, 'id')) {
      throw new ValidationException('ResourceIdentifier object MUST contain an id.');
    }

    if (!is_string($data->type)) {
      throw new ValidationException(sprintf('ResourceIdentifier property "type" MUST be a string, "%s" given.', gettype($data->type)));
    }

    if (!is_string($data->id) && !is_numeric($data->id)) {
      throw new ValidationException(sprintf('ResourceIdentifier property "id" MUST be a string, "%s" given.', gettype($data->id)));
    }

    return $this->getItemInstance($data->type)->setId($data->id);
  }
}
