<?php

namespace Dash\Concerns;

use Dash\Interfaces\DocumentInterface;
use Dash\Responses\CollectionDocument;

trait BuildsHasManyRelations {
  /**
   * Performs a get on the current resource.
   *
   * @throws \Exception
   *
   * @return CollectionDocument
   */
  public function search(): CollectionDocument {
    return $this->request('get');
  }

  /**
   * Performs a full replace on the relationship with the given data.
   *
   * @param CollectionDocument $data
   *
   * @throws \Exception
   *
   * @return DocumentInterface
   */
  public function replace(CollectionDocument $data): DocumentInterface {
    return $this->request('patch', $data);
  }

  /**
   * Performs an update on the relationship with the given data.
   *
   * @param CollectionDocument $data
   *
   * @throws \Exception
   *
   * @return DocumentInterface
   */
  public function add(CollectionDocument $data): DocumentInterface {
    return $this->request('post', $data);
  }

  /**
   * Performs a full replace on the relationship and clears all relationship members.
   *
   * @throws \Exception
   *
   * @return DocumentInterface
   */
  public function clear() {
    return $this->replace(new CollectionDocument());
  }

  /**
   * Performs a delete on the relationship and removes relationship members specified in the given data.
   *
   * @param CollectionDocument $data
   *
   * @throws \Exception
   *
   * @return DocumentInterface
   */
  public function delete(CollectionDocument $data): DocumentInterface {
    return $this->request('delete', $data);
  }
}
