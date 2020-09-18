<?php

namespace Dash\Tests\Concerns;

use Dash\Concerns\HasId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasIdTest extends TestCase {
  /**
   * @test
   */
  public function it_can_get_and_set_an_id() {
    /** @var MockObject&HasId $mock */
    $mock = $this->getMockForTrait(HasId::class);
    $id = '123';

    $mock->setId($id);

    $this->assertSame($id, $mock->getId());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_an_id() {
    /** @var MockObject&HasId $mock */
    $mock = $this->getMockForTrait(HasId::class);

    $this->assertFalse($mock->hasId());

    $mock->setId('123');

    $this->assertTrue($mock->hasId());
  }
}
