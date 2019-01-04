<?php

namespace Drupal\Tests\group\Kernel;

/**
 * Tests the generation of permission hashes.
 *
 * @coversDefaultClass \Drupal\group\Access\GroupPermissionsHashGenerator
 * @group group
 */
class GroupPermissionsHashGeneratorTest extends GroupKernelTestBase {

  /**
   * The group permissions hash generator service.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface
   */
  protected $hashGenerator;

  /**
   * The persistent cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * The static cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheStatic;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->hashGenerator = $this->container->get('group.permissions_hash_generator');
    $this->cacheDefault = $this->container->get('cache.default');
    $this->cacheStatic = $this->container->get('cache.static');
  }

  /**
   * Tests the generation of the anonymous hash.
   *
   * @covers ::generateAnonymousHash
   */
  public function testGenerateAnonymousHash() {
    $cid = 'group_anonymous_permissions_hash';
    $this->assertCacheIsEmpty($cid, TRUE, 'The cache has no data before generating a hash.');

    $initial_hash = $this->hashGenerator->generateAnonymousHash();
    $this->assertCacheIsEmpty($cid, FALSE, 'The cache has data after generating a hash.');
    $this->assertSame($this->cacheDefault->get($cid)->data, $initial_hash, 'Generating the anonymous hash stores it in the cache.');

    $group_type = $this->createGroupType(['id' => 'hash_test']);
    $this->assertCacheIsEmpty($cid, TRUE, 'Creating a new group type clears the cache.');
    $this->assertNotSame($this->hashGenerator->generateAnonymousHash(), $initial_hash, 'Creating a new group type affects the anonymous hash.');

    $group_role = $group_type->getAnonymousRole();
    $group_role->grantPermission('join group')->save();
    $this->assertCacheIsEmpty($cid, TRUE, 'Updating an existing anonymous group role clears the cache.');
    $this->assertNotSame($this->hashGenerator->generateAnonymousHash(), $initial_hash, "Updating an anonymous group role's permissions affects the anonymous hash.");
  }

  /**
   * Tests the generation of the authenticated hash.
   *
   * @covers ::generateAuthenticatedHash
   * @uses \Drupal\group\Access\GroupPermissionsHashGenerator::buildOutsiderPermissions
   * @uses \Drupal\group\Access\GroupPermissionsHashGenerator::buildMemberPermissions
   */
  public function testGenerateAuthenticatedHash() {
    $account = $this->createUser();
    $roles = $account->getRoles(TRUE);
    sort($roles);

    $outsider_cid = 'group_outsider_permissions_' . md5(serialize($roles));
    $member_cid = 'group_member_permissions_' . $account->id();
    $this->assertCacheIsEmpty($outsider_cid, TRUE, 'The outsider cache has no data before generating a hash.');
    $this->assertCacheIsEmpty($member_cid, TRUE, 'The member cache has no data before generating a hash.');

    $initial_hash = $this->hashGenerator->generateAuthenticatedHash($account);
    $this->assertCacheIsEmpty($outsider_cid, FALSE, 'The outsider cache has data after generating a hash.');
    $this->assertCacheIsEmpty($member_cid, FALSE, 'The member cache has data after generating a hash.');

    $outsider_permissions = [];
    foreach ($this->entityTypeManager->getStorage('group_type')->loadMultiple() as $group_type) {
      $outsider_permissions[$group_type->id()] = $group_type->getOutsiderRole()->getPermissions();
    }
    ksort($outsider_permissions);
    $this->assertSame($this->cacheDefault->get($outsider_cid)->data, $outsider_permissions, 'Generating the authenticated hash stores the outsider permissions in the cache.');

    $member_permissions = [];
    $this->assertSame($this->cacheDefault->get($member_cid)->data, $member_permissions, 'Generating the authenticated hash stores the member permissions in the cache.');

    $role_storage = $this->entityTypeManager->getStorage('user_role');
    $role_storage->save($role_storage->create(['id' => 'editor']));
    $account->addRole('editor');
    $account->save();

    // @todo We need cache tags that are cleared for an account when they join
    // or leave a group for this to work. ::buildMemberPermissions() currently
    // uses the account's cache tags.
    // $this->assertCacheIsEmpty($member_cid, FALSE, "Updating a user's roles does not clear the member cache.");

    $roles = $account->getRoles(TRUE);
    sort($roles);
    $outsider_cid = 'group_outsider_permissions_' . md5(serialize($roles));

    $this->assertCacheIsEmpty($outsider_cid, TRUE, 'The outsider cache has no data before generating a hash for different roles.');
    $hash_after_user_role = $this->hashGenerator->generateAuthenticatedHash($account);
    $this->assertSame($hash_after_user_role, $initial_hash, "Updating a user's roles but not group roles does not affect the authenticated hash.");

    /** @var \Drupal\group\GroupRoleSynchronizerInterface $group_role_synchronizer */
    $group_role_synchronizer = $this->container->get('group_role.synchronizer');
    $group_role_id = $group_role_synchronizer->getGroupRoleId('default', 'editor');
    $group_role = $this->entityTypeManager->getStorage('group_role')->load($group_role_id);
    $group_role->grantPermission('edit group')->save();

    $this->assertCacheIsEmpty($outsider_cid, TRUE, "Updating a user's outsider group role clears the outsider cache.");
    $this->assertCacheIsEmpty($member_cid, FALSE, "Updating a user's outsider group role does not clear the member cache.");

    $hash_after_group_role = $this->hashGenerator->generateAuthenticatedHash($account);
    $this->assertNotSame($hash_after_group_role, $hash_after_user_role, "Updating a user's outsider group role affects the authenticated hash.");

    $group = $this->createGroup();
    $group->addMember($account);

    $this->assertCacheIsEmpty($outsider_cid, FALSE, "Updating a user's group memberships does not clear the outsider cache.");
    $hash_after_membership = $this->hashGenerator->generateAuthenticatedHash($account);
    $this->assertNotSame($hash_after_membership, $hash_after_group_role, "Updating a user's group memberships affects the authenticated hash.");
  }

  /**
   * Tests whether anonymous users and outsiders can share a hash.
   *
   * @covers ::generateAnonymousHash
   * @covers ::generateAuthenticatedHash
   * @uses \Drupal\group\Access\GroupPermissionsHashGenerator::buildOutsiderPermissions
   * @uses \Drupal\group\Access\GroupPermissionsHashGenerator::buildMemberPermissions
   */
  public function testAnonymousOutsiderHashReusability() {
    $account = $this->createUser();
    $outsider_hash = $this->hashGenerator->generateAuthenticatedHash($account);
    $this->assertNotSame($outsider_hash, $this->hashGenerator->generateAnonymousHash(), 'Hashes for an anonymous and outsider user with different group permissions differ.');

    $group_type = $this->entityTypeManager->getStorage('group_type')->load('default');
    $group_role = $group_type->getAnonymousRole();
    $group_role->grantPermissions(['view group', 'join group'])->save();
    $this->assertSame($outsider_hash, $this->hashGenerator->generateAnonymousHash(), 'Hashes for an anonymous and outsider user with the same group permissions are the same.');
  }

  /**
   * Asserts whether the persistent and static caches are empty.
   *
   * @param string $cid
   *   The cache ID.
   * @param bool $is_empty
   *   Whether the cache is empty.
   * @param string $message
   *   The message to use in the assertions.
   */
  protected function assertCacheIsEmpty($cid, $is_empty, $message = '') {
    $persistent_cache = $this->cacheDefault->get($cid);
    $static_cache = $this->cacheStatic->get($cid);

    if ($is_empty) {
      $this->assertFalse($persistent_cache, $message);
      $this->assertFalse($static_cache, $message);
    }
    else {
      $this->assertNotFalse($persistent_cache, $message);
      $this->assertNotFalse($static_cache, $message);
    }
  }

}
