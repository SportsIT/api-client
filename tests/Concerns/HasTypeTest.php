<?php

namespace Dash\Tests\Concerns;

use Dash\Concerns\HasType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasTypeTest extends TestCase {
  /**
   * @test
   */
  public function it_can_get_and_set_a_type() {
    /** @var MockObject&HasType $mock */
    $mock = $this->getMockForTrait(HasType::class);
    $type = 'foo-bar';

    $mock->setType($type);

    $this->assertSame($type, $mock->getType());
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_a_type() {
    /** @var MockObject&HasType $mock */
    $mock = $this->getMockForTrait(HasType::class);

    $this->assertFalse($mock->hasType());

    $mock->setType('foo-bar');

    $this->assertTrue($mock->hasType());
  }
}
