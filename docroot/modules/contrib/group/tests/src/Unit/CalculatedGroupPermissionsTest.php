<?php

namespace Drupal\Tests\group\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\group\Access\CalculatedGroupPermissions;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the CalculatedGroupPermissions value object.
 *
 * @coversDefaultClass \Drupal\group\Access\CalculatedGroupPermissions
 * @group group
 */
class CalculatedGroupPermissionsTest extends UnitTestCase {

  /**
   * The calculated group permissions object.
   *
   * @var \Drupal\group\Access\CalculatedGroupPermissions
   */
  protected $calculatedPermissions;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->calculatedPermissions = new CalculatedGroupPermissions();
  }

  /**
   * Tests the setter for anonymous permissions.
   *
   * @covers ::setAnonymousPermissions
   * @covers ::getAnonymousPermissions
   */
  public function testSetAnonymousPermissions() {
    $permissions = ['foo' => ['baz'], 'alice' => ['bob', 'charlie']];
    $this->calculatedPermissions->setAnonymousPermissions($permissions);
    $this->assertSame($permissions, $this->calculatedPermissions->getAnonymousPermissions(), 'Managed to retrieve the full permission set.');
    $this->assertSame($permissions['alice'], $this->calculatedPermissions->getAnonymousPermissions('alice'), 'Managed to retrieve a subset of permissions.');
    $this->assertSame([], $this->calculatedPermissions->getAnonymousPermissions('404-key-not-found'), 'Requesting a non-existent set returns an empty array.');
  }

  /**
   * Tests that additional anonymous permissions can be added to the list.
   *
   * @covers ::addAnonymousPermissions
   * @depends testSetAnonymousPermissions
   */
  public function testAddAnonymousPermissions() {
    $permissions = ['foo' => ['baz'], 'alice' => ['bob', 'charlie']];
    $this->calculatedPermissions->setAnonymousPermissions($permissions);
    $permissions['additional'] = ['cat', 'dog'];
    $this->calculatedPermissions->addAnonymousPermissions('additional', $permissions['additional']);
    $this->assertSame($permissions, $this->calculatedPermissions->getAnonymousPermissions(), 'Permissions were properly added to the list.');
  }

  /**
   * Tests the setter for outsider permissions.
   *
   * @covers ::setOutsiderPermissions
   * @covers ::getOutsiderPermissions
   */
  public function testSetOutsiderPermissions() {
    $permissions = ['foo' => ['baz'], 'alice' => ['bob', 'charlie']];
    $this->calculatedPermissions->setOutsiderPermissions($permissions);
    $this->assertSame($permissions, $this->calculatedPermissions->getOutsiderPermissions(), 'Managed to retrieve the full permission set.');
    $this->assertSame($permissions['alice'], $this->calculatedPermissions->getOutsiderPermissions('alice'), 'Managed to retrieve a subset of permissions.');
    $this->assertSame([], $this->calculatedPermissions->getOutsiderPermissions('404-key-not-found'), 'Requesting a non-existent set returns an empty array.');
  }

  /**
   * Tests that additional outsider permissions can be added to the list.
   *
   * @covers ::addOutsiderPermissions
   * @depends testSetOutsiderPermissions
   */
  public function testAddOutsiderPermissions() {
    $permissions = ['foo' => ['baz'], 'alice' => ['bob', 'charlie']];
    $this->calculatedPermissions->setOutsiderPermissions($permissions);
    $permissions['additional'] = ['cat', 'dog'];
    $this->calculatedPermissions->addOutsiderPermissions('additional', $permissions['additional']);
    $this->assertSame($permissions, $this->calculatedPermissions->getOutsiderPermissions(), 'Permissions were properly added to the list.');
  }

  /**
   * Tests the setter for member permissions.
   *
   * @covers ::setMemberPermissions
   * @covers ::getMemberPermissions
   */
  public function testSetMemberPermissions() {
    $permissions = [24 => ['baz'], 10 => ['bob', 'charlie']];
    $this->calculatedPermissions->setMemberPermissions($permissions);
    $this->assertSame($permissions, $this->calculatedPermissions->getMemberPermissions(), 'Managed to retrieve the full permission set.');
    $this->assertSame($permissions[10], $this->calculatedPermissions->getMemberPermissions(10), 'Managed to retrieve a subset of permissions.');
    $this->assertSame([], $this->calculatedPermissions->getMemberPermissions(404), 'Requesting a non-existent set returns an empty array.');
  }

  /**
   * Tests that additional member permissions can be added to the list.
   *
   * @covers ::addMemberPermissions
   * @depends testSetMemberPermissions
   */
  public function testAddMemberPermissions() {
    $permissions = [24 => ['baz'], 10 => ['bob', 'charlie']];
    $this->calculatedPermissions->setMemberPermissions($permissions);
    $permissions[1986] = ['cat', 'dog'];
    $this->calculatedPermissions->addMemberPermissions(1986, $permissions[1986]);
    $this->assertSame($permissions, $this->calculatedPermissions->getMemberPermissions(), 'Permissions were properly added to the list.');
  }

  /**
   * Tests merging in another CalculatedGroupPermissions object.
   *
   * @covers ::merge
   */
  public function testMerge() {
    $cache_context_manager = $this->prophesize(CacheContextsManager::class);
    $cache_context_manager->assertValidTokens(Argument::any())->willReturn(TRUE);
    $container = $this->prophesize(ContainerInterface::class);
    $container->get('cache_contexts_manager')->willReturn($cache_context_manager->reveal());
    \Drupal::setContainer($container->reveal());

    $this->calculatedPermissions
      ->setAnonymousPermissions(['foo' => ['baz']])
      ->setOutsiderPermissions(['foo' => ['baz']])
      ->setMemberPermissions([1 => ['baz']])
      ->addCacheContexts(['foo'])
      ->addCacheTags(['foo']);

    $other = new CalculatedGroupPermissions();
    $other
      ->setAnonymousPermissions(['bar' => ['baz']])
      ->setOutsiderPermissions(['bar' => ['baz']])
      ->setMemberPermissions([5 => ['baz']])
      ->addCacheContexts(['bar'])
      ->addCacheTags(['bar']);

    $this->calculatedPermissions->merge($other);
    $this->assertSame(['foo' => ['baz'], 'bar' => ['baz']], $this->calculatedPermissions->getAnonymousPermissions(), 'Anonymous permissions were merged properly.');
    $this->assertSame(['foo' => ['baz'], 'bar' => ['baz']], $this->calculatedPermissions->getOutsiderPermissions(), 'Outsider permissions were merged properly.');
    $this->assertSame([1 => ['baz'], 5 => ['baz']], $this->calculatedPermissions->getMemberPermissions(), 'Member permissions were merged properly.');
    $this->assertSame(['bar', 'foo'], $this->calculatedPermissions->getCacheContexts(), 'Cache contexts were merged properly');
    $this->assertSame(['bar', 'foo'], $this->calculatedPermissions->getCacheTags(), 'Cache tags were merged properly');
  }

}
