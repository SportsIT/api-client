<?php

namespace Dash\Tests\Concerns;

use Dash\Concerns\HasLinks;
use Dash\Models\Links;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasLinksTest extends TestCase {
  /**
   * @test
   */
  public function it_can_get_and_set_links() {
    /** @var MockObject&HasLinks $mock */
    $mock = $this->getMockForTrait(HasLinks::class);
    $links = new Links([]);

    $mock->setLinks($links);

    $this->assertSame($links, $mock->getLinks());
  }
}
