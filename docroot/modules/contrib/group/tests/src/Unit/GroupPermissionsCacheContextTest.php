<?php

namespace Drupal\Tests\group\Unit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Access\CalculatedGroupPermissionsInterface;
use Drupal\group\Access\GroupPermissionCalculatorInterface;
use Drupal\group\Access\GroupPermissionsHashGeneratorInterface;
use Drupal\group\Cache\Context\GroupPermissionsCacheContext;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the user.group_permissions cache context.
 *
 * @coversDefaultClass \Drupal\group\Cache\Context\GroupPermissionsCacheContext
 * @group group
 */
class GroupPermissionsCacheContextTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $currentUser;

  /**
   * The permissions hash generator.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $permissionsHashGenerator;

  /**
   * The group permission calculator.
   *
   * @var \Drupal\group\Access\GroupPermissionCalculatorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $permissionCalculator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->currentUser = $this->prophesize(AccountProxyInterface::class);
    $this->permissionsHashGenerator = $this->prophesize(GroupPermissionsHashGeneratorInterface::class);
    $this->permissionCalculator = $this->prophesize(GroupPermissionCalculatorInterface::class);
  }

  /**
   * Tests getting the context value for an anonymous user.
   *
   * @covers ::getContext
   */
  public function testGetContextForAnonymous() {
    $this->currentUser->isAnonymous()->willReturn(TRUE);
    $this->permissionsHashGenerator->generateAnonymousHash()->willReturn('anonymous');

    $cache_context = new GroupPermissionsCacheContext(
      $this->currentUser->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->permissionCalculator->reveal()
    );
    $this->assertSame('anonymous', $cache_context->getContext());
  }

  /**
   * Tests getting the context value for an authenticated user.
   *
   * @covers ::getContext
   */
  public function testGetContextForAuthenticated() {
    $this->currentUser->isAnonymous()->willReturn(FALSE);
    $this->permissionsHashGenerator->generateAuthenticatedHash($this->currentUser->reveal())->willReturn('authenticated');

    $cache_context = new GroupPermissionsCacheContext(
      $this->currentUser->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->permissionCalculator->reveal()
    );
    $this->assertSame('authenticated', $cache_context->getContext());
  }

  /**
   * Tests getting the cacheable metadata from the calculated permissions.
   *
   * @covers ::getCacheableMetadata
   */
  public function testGetCacheableMetadata() {
    $calculated_permissions = $this->prophesize(CalculatedGroupPermissionsInterface::class);
    $calculated_permissions->getCacheContexts()->willReturn([]);
    $calculated_permissions->getCacheTags()->willReturn(["config:group.role.foo-bar"]);
    $calculated_permissions->getCacheMaxAge()->willReturn(-1);
    $calculated_permissions = $calculated_permissions->reveal();
    $this->permissionCalculator->calculatePermissions($this->currentUser->reveal())->willReturn($calculated_permissions);

    $cache_context = new GroupPermissionsCacheContext(
      $this->currentUser->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->permissionCalculator->reveal()
    );
    $this->assertEquals(CacheableMetadata::createFromObject($calculated_permissions), $cache_context->getCacheableMetadata());
  }

}
