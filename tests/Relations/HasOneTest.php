<?php

namespace Dash\Tests\Relations;

use Dash\Models\Item;
use Dash\Relations\HasOneRelation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasOneTest extends TestCase {
  /**
   * @test
   */
  public function it_can_associate_an_item_and_get_the_included() {
    /** @var MockObject&HasOneRelation $mock */
    $mock = $this->createMock(HasOneRelation::class);
    $item = new Item();

    $mock->associate($item);

    $this->assertSame($item, $mock->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_dissociate_an_item() {
    /** @var MockObject&HasOneRelation $mock */
    $mock = $this->createMock(HasOneRelation::class);
    $item = new Item();

    $mock->associate($item);
    $this->assertNotNull($mock->getIncluded());

    $mock->dissociate();

    $this->assertNull($mock->getIncluded());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_included() {
    /** @var MockObject&HasOneRelation $mock */
    $mock = $this->createMock(HasOneRelation::class);
    $item = new Item();

    $this->assertFalse($mock->hasIncluded());
    $mock->associate($item);

    $this->assertTrue($mock->hasIncluded());
  }

  /**
   * @test
   */
  public function it_can_set_and_get_omit_included() {
    /** @var MockObject&HasOneRelation $mock */
    $mock = $this->createMock(HasOneRelation::class);

    $this->assertFalse($mock->shouldOmitIncluded());
    $mock->setOmitIncluded(true);

    $this->assertTrue($mock->shouldOmitIncluded());
  }
}
