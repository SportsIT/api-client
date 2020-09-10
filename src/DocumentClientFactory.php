<?php

namespace Dash;

use Dash\Parsers\CollectionParser;
use Dash\Parsers\DocumentParser;
use Dash\Parsers\ErrorCollectionParser;
use Dash\Parsers\ErrorParser;
use Dash\Parsers\ItemParser;
use Dash\Parsers\JsonApiParser;
use Dash\Parsers\LinksParser;
use Dash\Parsers\MetaParser;
use Dash\Parsers\ResponseParser;

class DocumentClientFactory {
  /**
   * @param string|null $accessToken
   *
   * @return DocumentClient
   */
  public function make($accessToken = null): DocumentClient {
    return new DocumentClient((new GuzzleFactory())->make($accessToken), new ResponseParser($this->buildDocumentParser()));
  }

  /**
   * @return DocumentParser
   */
  protected function buildDocumentParser(): DocumentParser {
    $metaParser = new MetaParser();
    $linksParser = new LinksParser($metaParser);
    $itemParser = new ItemParser($linksParser, $metaParser);
    $collectionParser = new CollectionParser($itemParser);
    $errorCollectionParser = new ErrorCollectionParser(new ErrorParser($linksParser, $metaParser));
    $jsonApiParser = new JsonApiParser($metaParser);

    return new DocumentParser($itemParser, $collectionParser, $errorCollectionParser, $linksParser, $jsonApiParser, $metaParser);
  }
}
