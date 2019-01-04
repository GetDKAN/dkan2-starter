<?php

namespace Drupal\Tests\group\Unit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Access\GroupPermissionsHashGeneratorInterface;
use Drupal\group\Cache\Context\GroupPermissionsCacheContext;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupRoleStorageInterface;
use Drupal\group\GroupMembership;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Prophecy\Argument;

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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityTypeManager;

  /**
   * The permissions hash generator.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $permissionsHashGenerator;

  /**
   * The membership loader service.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $membershipLoader;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->currentUser = $this->prophesize(AccountProxyInterface::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->permissionsHashGenerator = $this->prophesize(GroupPermissionsHashGeneratorInterface::class);
    $this->membershipLoader = $this->prophesize(GroupMembershipLoaderInterface::class);
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
      $this->entityTypeManager->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->membershipLoader->reveal()
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
      $this->entityTypeManager->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->membershipLoader->reveal()
    );
    $this->assertSame('authenticated', $cache_context->getContext());
  }

  /**
   * Tests getting the cacheable metadata for an anonymous user.
   *
   * @covers ::getCacheableMetadata
   */
  public function testGetCacheableMetadataForAnonymous() {
    $this->currentUser->isAnonymous()->willReturn(TRUE);

    $group_role = $this->createGroupRole('foo-anonymous')->reveal();
    $group_type = $this->prophesize(GroupTypeInterface::class);
    $group_type->getAnonymousRole()->willReturn($group_role);
    $group_type = $group_type->reveal();

    $storage = $this->prophesize(ConfigEntityStorageInterface::class);
    $storage->loadMultiple()->willReturn(['foo' => $group_type]);
    $this->entityTypeManager->getStorage('group_type')->willReturn($storage->reveal());

    $cache_context = new GroupPermissionsCacheContext(
      $this->currentUser->reveal(),
      $this->entityTypeManager->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->membershipLoader->reveal()
    );
    $this->assertEquals(CacheableMetadata::createFromObject($group_role), $cache_context->getCacheableMetadata());
  }

  /**
   * Tests getting the cacheable metadata for an authenticated user.
   *
   * @covers ::getCacheableMetadata
   */
  public function testGetCacheableMetadataForAuthenticated() {
    $this->currentUser->isAnonymous()->willReturn(FALSE);
    $this->currentUser->id()->willReturn(1);

    $account = $this->prophesize(UserInterface::class);
    $account->getCacheContexts()->willReturn([]);
    $account->getCacheTags()->willReturn(['user:1']);
    $account->getCacheMaxAge()->willReturn(-1);
    $account->getRoles(TRUE)->willReturn([]);
    $account = $account->reveal();
    $user_storage = $this->prophesize(UserStorageInterface::class);
    $user_storage->load(1)->willReturn($account);
    $this->entityTypeManager->getStorage('user')->willReturn($user_storage->reveal());

    $group_role_outsider = $this->createGroupRole('foo-outsider')->reveal();
    $group_type = $this->prophesize(GroupTypeInterface::class);
    $group_type->getOutsiderRole()->willReturn($group_role_outsider);
    $group_type = $group_type->reveal();
    $group_type_storage = $this->prophesize(ConfigEntityStorageInterface::class);
    $group_type_storage->loadMultiple()->willReturn(['foo' => $group_type]);
    $this->entityTypeManager->getStorage('group_type')->willReturn($group_type_storage->reveal());

    $group_role_synced = $this->createGroupRole('foo-synced')->reveal();
    $group_role_storage = $this->prophesize(GroupRoleStorageInterface::class);
    $group_role_storage->loadSynchronizedByUserRoles(Argument::any())->willReturn(['foo-synced' => $group_role_synced]);
    $this->entityTypeManager->getStorage('group_role')->willReturn($group_role_storage->reveal());

    $group_role_member = $this->createGroupRole('foo-bar')->reveal();
    $group_membership = $this->prophesize(GroupMembership::class);
    $group_membership->getCacheContexts()->willReturn([]);
    $group_membership->getCacheTags()->willReturn(['group_content:1']);
    $group_membership->getCacheMaxAge()->willReturn(-1);
    $group_membership->getRoles()->willReturn([$group_role_member]);
    $group_membership = $group_membership->reveal();
    $this->membershipLoader->loadByUser(Argument::any())->willReturn([$group_membership]);

    $cache_context = new GroupPermissionsCacheContext(
      $this->currentUser->reveal(),
      $this->entityTypeManager->reveal(),
      $this->permissionsHashGenerator->reveal(),
      $this->membershipLoader->reveal()
    );

    $cacheable_metadata = CacheableMetadata::createFromObject($account);
    $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromObject($group_role_outsider));
    $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromObject($group_role_synced));
    $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromObject($group_membership));
    $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromObject($group_role_member));
    $this->assertEquals($cacheable_metadata, $cache_context->getCacheableMetadata());
  }

  /**
   * Creates a GroupRoleInterface prophecy.
   *
   * @param string $group_role_id
   *   The ID for the group role.
   *
   * @return \Drupal\group\Entity\GroupRoleInterface|\Prophecy\Prophecy\ProphecyInterface
   *   The prophesized group role.
   */
  protected function createGroupRole($group_role_id) {
    $prophecy = $this->prophesize(GroupRoleInterface::class);
    $prophecy->getCacheContexts()->willReturn([]);
    $prophecy->getCacheTags()->willReturn(["config:group.role.$group_role_id"]);
    $prophecy->getCacheMaxAge()->willReturn(-1);
    return $prophecy;
  }

}
