<?php

namespace Dash;

use Dash\Exceptions\UnsupportedDataException;
use Dash\Interfaces\DataInterface;
use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;
use Dash\Responses\CollectionDocument;
use Dash\Responses\ItemDocument;

class DocumentFactory {
  /**
   * @param DataInterface $data
   *
   * @param bool $withRelations
   * @return ItemDocument|CollectionDocument
   */
  public function make(DataInterface $data, bool $withRelations = true): DocumentInterface {
    if ($data instanceof ItemInterface) {
      $document = new ItemDocument();
    } elseif ($data instanceof Collection) {
      $document = new CollectionDocument();
    } else {
      throw new UnsupportedDataException(sprintf('%s is not supported as input', get_class($data)));
    }

    if ($withRelations) {
      $document->setIncluded($this->getIncluded($data));
    }

    return $document->setData($data);
  }

  /**
   * @param DataInterface $data
   *
   * @return Collection
   */
  private function getIncluded(DataInterface $data): Collection {
    return Collection::wrap($data)
      ->flatMap(
        function (ItemInterface $item) {
          return $this->getIncludedFromItem($item);
        }
      )
      ->unique(
        static function (ItemInterface $item) {
          return sprintf('%s:%s', $item->getType(), $item->getId());
        }
      )
      ->values();
  }

  /**
   * @param ItemInterface $item
   *
   * @return Collection
   */
  private function getIncludedFromItem(ItemInterface $item): Collection {
    return Collection::make($item->getRelations())
      ->reject(
        static function ($relationship) {
          /* @var HasOneRelation|HasManyRelation $relationship */
          return $relationship->shouldOmitIncluded() || !$relationship->hasIncluded();
        }
      )
      ->flatMap(
        static function ($relationship) {
          /* @var HasOneRelation|HasManyRelation $relationship */
          return Collection::wrap($relationship->getIncluded());
        }
      )
      ->flatMap(
        function (ItemInterface $item) {
          return Collection::wrap($item)->merge($this->getIncludedFromItem($item));
        }
      )
      ->filter(
        function (ItemInterface $item) {
          return $this->itemCanBeIncluded($item);
        }
      );
  }

  /**
   * @param ItemInterface $item
   *
   * @return bool
   */
  private function itemCanBeIncluded(ItemInterface $item): bool {
    return $item->hasType()
      && $item->hasId()
      && ($item->hasAttributes() || $item->hasRelationships());
  }
}
