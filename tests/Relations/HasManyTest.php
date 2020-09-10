<?php

namespace Dash\Tests\Relations;

use Dash\Models\Collection;
use Dash\Models\Item;
use Dash\Relations\HasManyRelation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasManyTest extends TestCase {
  /**
   * @test
   */
  public function it_can_associate_a_collection_and_get_the_included() {
    /** @var MockObject&HasManyRelation $mock */
    $mock = $this->createMock(HasManyRelation::class);
    $collection = new Collection([new Item()]);

    $mock->associate($collection);

    $this->assertSame($collection, $mock->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_dissociate_a_collection() {
    /** @var MockObject&HasManyRelation $mock */
    $mock = $this->createMock(HasManyRelation::class);
    $collection = new Collection([new Item()]);

    $mock->associate($collection);
    $this->assertNotNull($mock->getIncluded());

    $mock->dissociate();

    $this->assertEquals($mock->getIncluded(), new Collection());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_included() {
    /** @var MockObject&HasManyRelation $mock */
    $mock = $this->createMock(HasManyRelation::class);
    $collection = new Collection([new Item()]);

    $this->assertFalse($mock->hasIncluded());
    $mock->associate($collection);

    $this->assertTrue($mock->hasIncluded());
  }

  /**
   * @test
   */
  public function it_can_set_and_get_omit_included() {
    /** @var MockObject&HasManyRelation $mock */
    $mock = $this->createMock(HasManyRelation::class);

    $this->assertFalse($mock->shouldOmitIncluded());
    $mock->setOmitIncluded(true);

    $this->assertTrue($mock->shouldOmitIncluded());
  }

  /**
   * @test
   */
  public function it_can_sort_the_included() {
    /** @var MockObject&HasManyRelation $mock */
    $mock = $this->createMock(HasManyRelation::class);
    /** @var MockObject&Collection $collectionMock */
    $collectionMock = $this->createMock(Collection::class);

    $collectionMock->expects($this->once())
      ->method('sortBy')
      ->with('foo', SORT_NATURAL, true);

    $mock->associate($collectionMock);

    $mock->sortBy('foo', SORT_NATURAL, true);
  }
}
