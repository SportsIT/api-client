<?php

namespace Dash\Tests\Relations;

use Dash\Interfaces\ItemInterface;
use Dash\Models\Item;
use Dash\Relations\HasOneRelation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasOneTest extends TestCase {
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
  public function it_can_associate_an_item_and_get_the_included() {
    $relation = new HasOneRelation($this->parent, 'test');
    $item = new Item();

    $relation->associate($item);

    $this->assertSame($item, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_dissociate_an_item() {
    $relation = new HasOneRelation($this->parent, 'test');
    $item = new Item();

    $relation->associate($item);
    $this->assertNotNull($relation->getIncluded());

    $relation->dissociate();

    $this->assertNull($relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_included() {
    $relation = new HasOneRelation($this->parent, 'test');
    $item = new Item();

    $this->assertFalse($relation->hasIncluded());
    $relation->associate($item);

    $this->assertTrue($relation->hasIncluded());
  }

  /**
   * @test
   */
  public function it_can_set_and_get_omit_included() {
    $relation = new HasOneRelation($this->parent, 'test');

    $this->assertFalse($relation->shouldOmitIncluded());
    $relation->setOmitIncluded(true);

    $this->assertTrue($relation->shouldOmitIncluded());
  }
}
