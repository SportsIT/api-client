<?php

namespace Dash\Tests\Relations;

use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Models\Item;
use Dash\Relations\HasManyRelation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasManyTest extends TestCase {
  /**
   * @var MockObject&ItemInterface
   */
  protected $parent;

  protected function setUp(): void {
    $this->parent = $this->createMock(ItemInterface::class);
    parent::setUp();
  }

  /**
   * @test
   */
  public function it_can_associate_a_collection_and_get_the_included() {
    $relation = new HasManyRelation($this->parent, 'test');
    $collection = new Collection([new Item()]);

    $relation->associate($collection);

    $this->assertSame($collection, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_dissociate_a_collection() {
    $relation = new HasManyRelation($this->parent, 'test');
    $collection = new Collection([new Item()]);

    $relation->associate($collection);
    $this->assertNotNull($relation->getIncluded());

    $relation->dissociate();

    $this->assertEquals($relation->getIncluded(), new Collection());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_included() {
    $relation = new HasManyRelation($this->parent, 'test');
    $collection = new Collection([new Item()]);

    $this->assertFalse($relation->hasIncluded());
    $relation->associate($collection);

    $this->assertTrue($relation->hasIncluded());
  }

  /**
   * @test
   */
  public function it_can_set_and_get_omit_included() {
    $relation = new HasManyRelation($this->parent, 'test');

    $this->assertFalse($relation->shouldOmitIncluded());
    $relation->setOmitIncluded(true);

    $this->assertTrue($relation->shouldOmitIncluded());
  }

  /**
   * @test
   */
  public function it_can_sort_the_included() {
    $relation = new HasManyRelation($this->parent, 'test');
    /** @var MockObject&Collection $collectionMock */
    $collectionMock = $this->createMock(Collection::class);

    $collectionMock->expects($this->once())
      ->method('sortBy')
      ->with('foo', SORT_NATURAL, true);

    $relation->associate($collectionMock);

    $relation->sortBy('foo', SORT_NATURAL, true);
  }
}
