<?php

namespace Dash\Tests\Concerns;

use Dash\Concerns\HasRelations;
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
   * @test
   */
  public function it_can_get_and_set_an_item_as_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();

    $mock->setRelation('foo', $data);

    $relation = $mock->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertSame($data, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_get_and_set_a_collection_as_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Collection();

    $mock->setRelation('foo', $data);

    $relation = $mock->getRelation('foo');
    $this->assertInstanceOf(HasManyRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertSame($data, $relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_can_get_and_set_null_as_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $mock->setRelation('foo', null);

    $relation = $mock->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertTrue($relation->hasIncluded());
    $this->assertNull($relation->getIncluded());
  }

  /**
   * @test
   */
  public function it_does_not_set_false_as_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $mock->setRelation('foo', false);

    $relation = $mock->getRelation('foo');
    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertFalse($relation->hasIncluded());
  }

  /**
   * @test
   */
  public function it_sets_the_links_on_the_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();
    $links = new Links([]);

    $mock->setRelation('foo', $data, $links);

    $relation = $mock->getRelation('foo');
    $this->assertSame($links, $relation->getLinks());
  }

  /**
   * @test
   */
  public function it_sets_the_meta_on_the_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();
    $meta = new Meta([]);

    $mock->setRelation('foo', $data, null, $meta);

    $relation = $mock->getRelation('foo');
    $this->assertSame($meta, $relation->getMeta());
  }

  /**
   * @test
   */
  public function it_can_get_all_relations() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();

    $mock->setRelation('foo', $data);
    $relation = $mock->getRelation('foo');

    $this->assertSame(['foo' => $relation], $mock->getRelations());
  }

  /**
   * @test
   */
  public function it_can_get_a_relation_value() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();

    $mock->setRelation('foo', $data);

    $this->assertSame($data, $mock->getRelationValue('foo'));
  }

  /**
   * @test
   */
  public function it_returns_null_when_getting_an_unexisting_relation_value() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $this->assertNull($mock->getRelationValue('foo'));
  }

  /**
   * @test
   */
  public function it_returns_a_boolean_indicating_if_it_has_a_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();

    $this->assertFalse($mock->hasRelation('foo'));

    $mock->setRelation('foo', $data);

    $this->assertTrue($mock->hasRelation('foo'));
  }

  /**
   * @test
   */
  public function it_can_unset_a_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);
    $data = new Item();

    $mock->setRelation('foo', $data);
    $this->assertNotNull($mock->getRelation('foo'));

    $mock->unsetRelation('foo');

    $this->assertNull($mock->getRelation('foo'));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_one_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $relation = $mock->hasOne('foo-bar');

    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertSame($relation, $mock->getRelation('foo-bar'));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_one_relation_with_the_calling_method_as_fallback_name() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $relation = $mock->hasOne(Item::class);

    $this->assertInstanceOf(HasOneRelation::class, $relation);
    $this->assertSame($relation, $mock->getRelation(__FUNCTION__));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_many_relation() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $relation = $mock->hasMany('foo-bar');

    $this->assertInstanceOf(HasManyRelation::class, $relation);
    $this->assertSame($relation, $mock->getRelation('foo-bar'));
  }

  /**
   * @test
   */
  public function it_can_define_a_has_many_relation_with_the_calling_method_as_fallback_name() {
    /** @var MockObject&HasRelations $mock */
    $mock = $this->getMockForTrait(HasRelations::class);

    $relation = $mock->hasMany(Item::class);

    $this->assertInstanceOf(HasManyRelation::class, $relation);
    $this->assertSame($relation, $mock->getRelation(__FUNCTION__));
  }
}
