<?php

namespace Dash\Parsers;

use Dash\Exceptions\ValidationException;
use Dash\Interfaces\DocumentInterface;
use Dash\Interfaces\DocumentParserInterface;
use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;
use Dash\Responses\CollectionDocument;
use Dash\Responses\Document;
use Dash\Responses\ItemDocument;

class DocumentParser implements DocumentParserInterface {
  /**
   * @var ItemParser
   */
  private $itemParser;

  /**
   * @var CollectionParser
   */
  private $collectionParser;

  /**
   * @var ErrorCollectionParser
   */
  private $errorCollectionParser;

  /**
   * @var LinksParser
   */
  private $linksParser;

  /**
   * @var JsonapiParser
   */
  private $jsonapiParser;

  /**
   * @var MetaParser
   */
  private $metaParser;

  /**
   * @param ItemParser            $itemParser
   * @param CollectionParser      $collectionParser
   * @param ErrorCollectionParser $errorCollectionParser
   * @param LinksParser           $linksParser
   * @param JsonapiParser         $jsonapiParser
   * @param MetaParser            $metaParser
   */
  public function __construct(
    ItemParser $itemParser,
    CollectionParser $collectionParser,
    ErrorCollectionParser $errorCollectionParser,
    LinksParser $linksParser,
    JsonapiParser $jsonapiParser,
    MetaParser $metaParser
  ) {
    $this->itemParser = $itemParser;
    $this->collectionParser = $collectionParser;
    $this->errorCollectionParser = $errorCollectionParser;
    $this->linksParser = $linksParser;
    $this->jsonapiParser = $jsonapiParser;
    $this->metaParser = $metaParser;
  }

  /**
   * @param string $json
   *
   * @return DocumentInterface
   */
  public function parse(string $json): DocumentInterface {
    $data = $this->decodeJson($json);

    if (!is_object($data)) {
      throw new ValidationException(sprintf('Document MUST be an object, "%s" given.', gettype($data)));
    }

    if (!property_exists($data, 'data') && !property_exists($data, 'errors') && !property_exists($data, 'meta')) {
      throw new ValidationException('Document MUST contain at least one of the following properties: `data`, `errors`, `meta`.');
    }

    if (property_exists($data, 'data') && property_exists($data, 'errors')) {
      throw new ValidationException('The properties `data` and `errors` MUST NOT coexist in Document.');
    }

    if (!property_exists($data, 'data') && property_exists($data, 'included')) {
      throw new ValidationException('If Document does not contain a `data` property, the `included` property MUST NOT be present either.');
    }

    if (property_exists($data, 'data') && !is_object($data->data) && !is_array($data->data) && $data->data !== null) {
      throw new ValidationException(sprintf('Document property "data" MUST be null, an array or an object, "%s" given.', gettype($data->data)));
    }

    if (property_exists($data, 'included') && !is_array($data->included)) {
      throw new ValidationException(sprintf('Document property "included" MUST be an array, "%s" given.', gettype($data->included)));
    }

    $document = $this->getDocument($data);

    if (property_exists($data, 'links')) {
      $document->setLinks($this->linksParser->parse($data->links, LinksParser::SOURCE_DOCUMENT));
    }

    if (property_exists($data, 'errors')) {
      $document->setErrors($this->errorCollectionParser->parse($data->errors));
    }

    if (property_exists($data, 'meta')) {
      $document->setMeta($this->metaParser->parse($data->meta));
    }

    if (property_exists($data, 'jsonapi')) {
      $document->setJsonapi($this->jsonapiParser->parse($data->jsonapi));
    }

    return $document;
  }

  /**
   * @param string $json
   *
   * @return mixed
   */
  private function decodeJson(string $json) {
    $data = json_decode($json, false);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new ValidationException(sprintf('Unable to parse JSON data: %s', json_last_error_msg()), json_last_error());
    }

    return $data;
  }

  /**
   * @param mixed $data
   *
   * @return DocumentInterface
   */
  private function getDocument($data): DocumentInterface {
    if (!property_exists($data, 'data') || $data->data === null) {
      return new Document();
    }

    if (is_array($data->data)) {
      $document = (new CollectionDocument())
        ->setData($this->collectionParser->parse($data->data));
    } else {
      $document = (new ItemDocument())
        ->setData($this->itemParser->parse($data->data));
    }

    if (property_exists($data, 'included')) {
      $document->setIncluded($this->collectionParser->parse($data->included));
    }

    $allItems = Collection::wrap($document->getData())
      ->concat($document->getIncluded());

    $duplicateItems = $this->getDuplicateItems($allItems);

    if ($duplicateItems->isNotEmpty()) {
      throw new ValidationException(sprintf('Resources MUST be unique based on their `type` and `id`, %d duplicate(s) found.', $duplicateItems->count()));
    }

    $this->linkRelationships($allItems);

    return $document;
  }

  /**
   * @param Collection $items
   */
  private function linkRelationships(Collection $items) {
    $keyedItems = $items->keyBy(
      function (ItemInterface $item) {
        return $this->getItemKey($item);
      }
    );

    $items->each(
      function (ItemInterface $item) use ($keyedItems) {
        foreach ($item->getRelations() as $name => $relation) {
          if ($relation instanceof HasOneRelation) {
            /** @var ItemInterface|null $relatedItem */
            $relatedItem = $relation->getIncluded();

            if ($relatedItem === null) {
              continue;
            }

            $includedItem = $this->getItem($keyedItems, $relatedItem);

            if ($includedItem !== null) {
              $relation->associate($includedItem);
            }
          } elseif ($relation instanceof HasManyRelation) {
            /** @var Collection $relatedCollection */
            $relatedCollection = $relation->getIncluded();

            /** @var ItemInterface $relatedItem */
            foreach ($relatedCollection as $key => $relatedItem) {
              $includedItem = $this->getItem($keyedItems, $relatedItem);

              if ($includedItem !== null) {
                $relatedCollection->put($key, $includedItem);
              }
            }
          }
        }
      }
    );
  }

  /**
   * @param Collection    $included
   * @param ItemInterface $item
   *
   * @return ItemInterface|null
   */
  private function getItem(Collection $included, ItemInterface $item) {
    return $included->get($this->getItemKey($item));
  }

  /**
   * @param ItemInterface $item
   *
   * @return string
   */
  private function getItemKey(ItemInterface $item): string {
    return sprintf('%s:%s', $item->getType(), $item->getId());
  }

  /**
   * @param Collection $items
   *
   * @return Collection
   */
  private function getDuplicateItems(Collection $items): Collection {
    $valueRetriever = function (ItemInterface $item) {
      return $this->getItemKey($item);
    };

    // Collection->duplicates was introduced in Laravel 5.8
    if (method_exists($items, 'duplicates')) {
      return $items->duplicates($valueRetriever);
    }

    /*
     * Duplicates code copied, and simplified for our use case, from Laravel 6.
     *
     * @see https://github.com/laravel/framework/blob/v6.1.0/src/Illuminate/Support/Collection.php#L275
     */
    $values = $items->map($valueRetriever);

    $uniqueValues = $values->unique();

    $compare = static function ($a, $b) {
      return $a === $b;
    };

    $duplicates = new Collection();

    foreach ($values as $key => $value) {
      if ($uniqueValues->isNotEmpty() && $compare($value, $uniqueValues->first())) {
        $uniqueValues->shift();
      } else {
        $duplicates[$key] = $value;
      }
    }

    return $duplicates;
  }
}
