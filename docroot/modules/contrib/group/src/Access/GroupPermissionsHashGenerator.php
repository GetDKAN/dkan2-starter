<?php

namespace Drupal\group\Access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\group\GroupRoleSynchronizerInterface;

/**
 * Generates and caches the permissions hash for a group membership.
 */
class GroupPermissionsHashGenerator implements GroupPermissionsHashGeneratorInterface {

  /**
   * The private key service.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * The cache backend interface to use for the persistent cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The cache backend interface to use for the static cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $static;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The group role synchronizer service.
   *
   * @var \Drupal\group\GroupRoleSynchronizerInterface
   */
  protected $groupRoleSynchronizer;

  /**
   * The membership loader service.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $membershipLoader;

  /**
   * Constructs a GroupPermissionsHashGenerator object.
   *
   * @param \Drupal\Core\PrivateKey $private_key
   *   The private key service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend interface to use for the persistent cache.
   * @param \Drupal\Core\Cache\CacheBackendInterface $static
   *   The cache backend interface to use for the static cache.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\group\GroupRoleSynchronizerInterface $group_role_synchronizer
   *   The group role synchronizer service.
   * @param \Drupal\group\GroupMembershipLoaderInterface $membership_loader
   *   The group membership loader service.
   */
  public function __construct(PrivateKey $private_key, CacheBackendInterface $cache, CacheBackendInterface $static, EntityTypeManagerInterface $entity_type_manager, GroupRoleSynchronizerInterface $group_role_synchronizer, GroupMembershipLoaderInterface $membership_loader) {
    $this->privateKey = $private_key;
    $this->cache = $cache;
    $this->static = $static;
    $this->entityTypeManager = $entity_type_manager;
    $this->groupRoleSynchronizer = $group_role_synchronizer;
    $this->membershipLoader = $membership_loader;
  }

  /**
   * {@inheritdoc}
   *
   * Cached by role, invalidated whenever permissions change.
   */
  public function generate(GroupInterface $group, AccountInterface $account) {
    // If the user can bypass group access we return a unique hash.
    if ($account->hasPermission('bypass group access')) {
      return $this->hash('bypass-group-access');
    }

    // Retrieve all of the group roles the user may get for the group.
    $group_roles = $this->groupRoleStorage()->loadByUserAndGroup($account, $group);

    // Sort the group roles by ID.
    ksort($group_roles);

    // Create a cache ID based on the role IDs.
    $role_list = implode(',', array_keys($group_roles));
    $cid = "group_permissions_hash:$role_list";

    // Retrieve the hash from the static cache if available.
    if ($static_cache = $this->static->get($cid)) {
      return $static_cache->data;
    }
    else {
      // Build cache tags for the individual group roles.
      $tags = Cache::buildTags('config:group.role', array_keys($group_roles), '.');

      // Retrieve the hash from the persistent cache if available.
      if ($cache = $this->cache->get($cid)) {
        $permissions_hash = $cache->data;
      }
      // Otherwise generate the hash and store it in the persistent cache.
      else {
        $permissions_hash = $this->doGenerate($group_roles);
        $this->cache->set($cid, $permissions_hash, Cache::PERMANENT, $tags);
      }

      // Store the hash in the static cache.
      $this->static->set($cid, $permissions_hash, Cache::PERMANENT, $tags);
    }

    return $permissions_hash;
  }

  /**
   * {@inheritdoc}
   */
  public function generateAnonymousHash() {
    $cid = 'group_anonymous_permissions_hash';

    // Retrieve the hash from the static cache if available.
    if ($static_cache = $this->static->get($cid)) {
      return $static_cache->data;
    }
    // Retrieve the hash from the persistent cache if available.
    elseif ($cache = $this->cache->get($cid)) {
      $permissions_hash = $cache->data;
      $tags = $cache->tags;
    }
    // Otherwise generate the hash and store it in the persistent cache.
    else {
      $permissions = [];

      // If a new group type is introduced, we need to recalculate the anonymous
      // permissions hash. Therefore, we need to introduce the group type list
      // cache tag.
      $tags = ['config:group_type_list'];

      /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
      $storage = $this->entityTypeManager->getStorage('group_type');
      foreach ($storage->loadMultiple() as $group_type_id => $group_type) {
        $group_role = $group_type->getAnonymousRole();
        $permissions[$group_type_id] = $group_role->getPermissions();
        $tags = Cache::mergeTags($tags, $group_role->getCacheTags());

        // Sort the permissions by name to ensure we don't get mismatching
        // hashes for people with the same permissions, just because the order
        // of the permissions happened to differ.
        sort($permissions[$group_type_id]);
      }

      // Sort the result by key to ensure we don't get mismatching hashes for
      // people with the same permissions, just because the order of the keys
      // happened to differ.
      ksort($permissions);

      $permissions_hash = $this->hash(serialize($permissions));
      $this->cache->set($cid, $permissions_hash, Cache::PERMANENT, $tags);
    }

    // Store the hash in the static cache.
    $this->static->set($cid, $permissions_hash, Cache::PERMANENT, $tags);

    return $permissions_hash;
  }

  /**
   * Builds an authenticated user's outsider permissions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to build the outsider permissions.
   *
   * @return array
   *   A list of permissions, keyed by group type.
   */
  protected function buildOutsiderPermissions(AccountInterface $account) {
    // The permissions you have for each group type as an outsider are the same
    // for anyone with the same user roles. So it's safe to cache the complete
    // set of outsider permissions you have per group type and re-use that cache
    // for anyone else with the same user roles.
    $roles = $account->getRoles(TRUE);
    sort($roles);

    $cid = 'group_outsider_permissions_' . md5(serialize($roles));

    // Retrieve the permissions from the static cache if available.
    if ($static_cache = $this->static->get($cid)) {
      return $static_cache->data;
    }
    // Retrieve the permissions from the persistent cache if available.
    elseif ($cache = $this->cache->get($cid)) {
      $permissions = $cache->data;
      $tags = $cache->tags;
    }
    // Otherwise build the permissions and store them in the persistent cache.
    else {
      $permissions = [];

      // If a new group type is introduced, we need to recalculate the outsider
      // permissions. Therefore, we need to introduce the group type list cache
      // tag.
      $tags = ['config:group_type_list'];

      $group_type_storage = $this->entityTypeManager->getStorage('group_type');
      $group_role_storage = $this->entityTypeManager->getStorage('group_role');

      /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
      foreach ($group_type_storage->loadMultiple() as $group_type_id => $group_type) {
        $group_role = $group_type->getOutsiderRole();
        $permissions[$group_type_id] = $group_role->getPermissions();
        $tags = Cache::mergeTags($tags, $group_role->getCacheTags());

        $group_role_ids = [];
        foreach ($roles as $role_id) {
          $group_role_ids[] = $this->groupRoleSynchronizer->getGroupRoleId($group_type_id, $role_id);
        }

        if (!empty($group_role_ids)) {
          /** @var \Drupal\group\Entity\GroupRoleInterface $group_role */
          foreach ($group_role_storage->loadMultiple($group_role_ids) as $group_role) {
            $permissions[$group_type_id] = array_merge($permissions[$group_type_id], $group_role->getPermissions());
            $tags = Cache::mergeTags($tags, $group_role->getCacheTags());
          }
        }

        // Make sure the permissions only appear once per group type.
        $permissions[$group_type_id] = array_unique($permissions[$group_type_id]);

        // Sort the permissions by name to ensure we don't get mismatching
        // hashes for people with the same permissions, just because the order
        // of the permissions happened to differ.
        sort($permissions[$group_type_id]);
      }

      // Sort the result by key to ensure we don't get mismatching hashes for
      // people with the same permissions, just because the order of the keys
      // happened to differ.
      ksort($permissions);

      $this->cache->set($cid, $permissions, Cache::PERMANENT, $tags);
    }

    // Store the permissions in the static cache.
    $this->static->set($cid, $permissions, Cache::PERMANENT, $tags);

    return $permissions;
  }

  /**
   * Builds an authenticated user's member permissions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to build the member permissions.
   *
   * @return array
   *   A list of permissions, keyed by group ID.
   */
  protected function buildMemberPermissions(AccountInterface $account) {
    $cid = 'group_member_permissions_' . $account->id();

    // Retrieve the permissions from the static cache if available.
    if ($static_cache = $this->static->get($cid)) {
      return $static_cache->data;
    }
    // Retrieve the permissions from the persistent cache if available.
    elseif ($cache = $this->cache->get($cid)) {
      $permissions = $cache->data;
      $tags = $cache->tags;
    }
    // Otherwise build the permissions and store them in the persistent cache.
    else {
      $permissions = [];

      // @todo Use a cache tag for memberships (e.g.: when new one is added).
      // If the user gets added to or removed from a group, their account will
      // be re-saved in GroupContent::postDelete() and GroupContent::postSave().
      // This means we can add the user's cache tags to invalidate this cache
      // whenever the user is saved.
      $user = $this->entityTypeManager->getStorage('user')->load($account->id());
      $tags = $user->getCacheTagsToInvalidate();

      foreach ($this->membershipLoader->loadByUser($account) as $group_membership) {
        $group_id = $group_membership->getGroup()->id();
        $permissions[$group_id] = [];

        // If the membership gets new roles or is stripped from some roles, we
        // need to recalculate the permissions.
        Cache::mergeTags($tags, $group_membership->getCacheTags());

        foreach ($group_membership->getRoles() as $group_role) {
          $permissions[$group_id] = array_merge($permissions[$group_id], $group_role->getPermissions());
          $tags = Cache::mergeTags($tags, $group_role->getCacheTags());
        }

        // Make sure the permissions only appear once per group.
        $permissions[$group_id] = array_unique($permissions[$group_id]);

        // Because we're combining permissions from several roles, we cannot be
        // sure about the order of permissions across different user role
        // combinations. To avoid some serious edge cases, it's safer if we sort
        // the total set of permissions.
        sort($permissions[$group_id]);
      }

      // Sort the result by key to ensure we don't get mismatching hashes for
      // people with the same permissions, just because the order of the keys
      // happened to differ.
      ksort($permissions);

      $this->cache->set($cid, $permissions, Cache::PERMANENT, $tags);
    }

    // Store the hash in the static cache.
    $this->static->set($cid, $permissions, Cache::PERMANENT, $tags);

    return $permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function generateAuthenticatedHash(AccountInterface $account) {
    $permissions = array_merge($this->buildOutsiderPermissions($account), $this->buildMemberPermissions($account));
    return $this->hash(serialize($permissions));
  }

  /**
   * Generates a hash that uniquely identifies the group member's permissions.
   *
   * @param \Drupal\group\Entity\GroupRoleInterface[] $group_roles
   *   The group roles to generate the permission hash for.
   *
   * @return string
   *   The permissions hash.
   */
  protected function doGenerate(array $group_roles) {
    $permissions = [];
    foreach ($group_roles as $group_role) {
      $permissions = array_merge($permissions, $group_role->getPermissions());
    }
    return $this->hash(serialize(array_unique($permissions)));
  }

  /**
   * Hashes the given string.
   *
   * @param string $identifier
   *   The string to be hashed.
   *
   * @return string
   *   The hash.
   */
  protected function hash($identifier) {
    return hash('sha256', $this->privateKey->get() . Settings::getHashSalt() . $identifier);
  }

  /**
   * Gets the group role storage.
   *
   * @return \Drupal\group\Entity\Storage\GroupRoleStorageInterface
   *   The group role storage.
   */
  protected function groupRoleStorage() {
    return $this->entityTypeManager->getStorage('group_role');
  }

}
