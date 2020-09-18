<?php

namespace Dash\Tests\Concerns;

use Dash\Interfaces\ItemInterface;
use Dash\Models\Collection;
use Dash\Models\Item;
use Dash\Models\Links;
use Dash\Models\Meta;
use Dash\Relations\HasManyRelation;
use Dash\Relations\HasOneRelation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasRelationsTest extends TestCase {
  /**
   * @var MockObject&ItemInterface
   */
  protected $hasRelations;

  protected function setUp(): void {
    $this->hasRelations = new Item();
    parent::setUp();
  }

  /**
   * @test
   */
  public function it_can_get_and_set_an_item_as_relation() {
    $data = new Item();

    $this->hasRelations->setRelation('foo', $data);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertSame($data, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_get_and_set_a_collection_as_relation() {
    $data = new Collection();

    $this->hasRelations->setRelation('foo', $data);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertInstanceOf(HasManyRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertSame($data, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_get_and_set_null_as_relation() {
    $this->hasRelations->setRelation('foo', null);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertNull($relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_does_not_set_false_as_relation() {
    $this->hasRelations->setRelation('foo', false);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertFalse($relation->hasIncluded());
  }

  /**
   * @test
   */
  public function it_sets_the_links_on_the_relation() {
    $data = new Item();
    $links = new Links([]);

    $this->hasRelations->setRelation('foo', $data, $links);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertSame($links, $relation->getLinks());
  }

  /**
   * @test
   */
  public function it_sets_the_meta_on_the_relation() {
    $data = new Item();
    $meta = new Meta([]);

    $this->hasRelations->setRelation('foo', $data, null, $meta);

    $relation = $this->hasRelations->getRelation('foo');
    $this->assertSame($meta, $relation->getMeta());
  }

  /**
   * @test
   */
  public function it_can_get_all_relations() {
    $data = new Item();

    $this->hasRelations->setRelation('foo', $data);
    $relation = $this->hasRelations->getRelation('foo');

    $this->assertSame(['foo' => $relation], $this->hasRelations->getRelations());
  }

  /**
   * @test
   */
  public function it_can_get_a_relation_value() {
    $data = new Item();

    $this->hasRelations->setRelation('foo', $data);

    $this->assertSame($data, $this->hasRelations->getRelationValue('foo'));
  }

  /**
   * @test
   */
  public function it_returns_null_when_getting_an_unexisting_relation_value() {
    $this->assertNull($this->hasRelations->getRelationValue('foo'));
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_a_relation() {
    $data = new Item();

    $this->assertFalse($this->hasRelations->hasRelation('foo'));

    $this->hasRelations->setRelation('foo', $data);

    $this->assertTrue($this->hasRelations->hasRelation('foo'));
  }

  /**
   * @test
   */
  public function it_can_unset_a_relation() {
    $data = new Item();

    $this->hasRelations->setRelation('foo', $data);
    $this->assertNotNull($this->hasRelations->getRelation('foo'));

    $this->hasRelations->unsetRelation('foo');

    $this->assertNull($this->hasRelations->getRelation('foo'));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_one_relation() {
    $relation = $this->hasRelations->hasOne('foo-bar');

    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertSame($relation, $this->hasRelations->getRelation('foo-bar'));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_many_relation() {
    $relation = $this->hasRelations->hasMany('foo-bar');

    $this->assertInstanceOf(HasManyRelation::class, $relation);
    $this->assertSame($relation, $this->hasRelations->getRelation('foo-bar'));
  }
}
