<?php

namespace Dash\Tests\Concerns;

use Dash\Concerns\HasMeta;
use Dash\Models\Meta;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasMetaTest extends TestCase {
  /**
   * @test
   */
  public function it_can_get_and_set_meta() {
    /** @var MockObject&HasMeta $mock */
    $mock = $this->getMockForTrait(HasMeta::class);
    $meta = new Meta([]);

    $mock->setMeta($meta);

    $this->assertSame($meta, $mock->getMeta());
  }
}
