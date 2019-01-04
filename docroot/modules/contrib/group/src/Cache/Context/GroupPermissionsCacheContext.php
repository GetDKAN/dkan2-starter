<?php

namespace Drupal\group\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Access\GroupPermissionsHashGeneratorInterface;
use Drupal\group\GroupMembershipLoaderInterface;

/**
 * Defines a cache context for "per group membership permissions" caching.
 *
 * Please read the following guide on how to best use this context:
 * https://www.drupal.org/docs/8/modules/group/turning-off-caching-when-it-doesnt-make-sense.
 *
 * Cache context ID: 'user.group_permissions'.
 */
class GroupPermissionsCacheContext implements CacheContextInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The permissions hash generator.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface
   */
  protected $permissionsHashGenerator;

  /**
   * The membership loader service.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $membershipLoader;

  /**
   * Constructs a new GroupMembershipPermissionsCacheContext class.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\group\Access\GroupPermissionsHashGeneratorInterface $hash_generator
   *   The permissions hash generator.
   * @param \Drupal\group\GroupMembershipLoaderInterface $membership_loader
   *   The group membership loader service.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, GroupPermissionsHashGeneratorInterface $hash_generator, GroupMembershipLoaderInterface $membership_loader) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->permissionsHashGenerator = $hash_generator;
    $this->membershipLoader = $membership_loader;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Group permissions");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    if ($this->currentUser->isAnonymous()) {
      return $this->permissionsHashGenerator->generateAnonymousHash();
    }
    return $this->permissionsHashGenerator->generateAuthenticatedHash($this->currentUser);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    // If the user is anonymous, the result of this cache context may change
    // when any anonymous group role is updated.
    if ($this->currentUser->isAnonymous()) {
      $cacheable_metadata = new CacheableMetadata();

      /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
      $storage = $this->entityTypeManager->getStorage('group_type');
      foreach ($storage->loadMultiple() as $group_type_id => $group_type) {
        $group_role_cacheable_metadata = CacheableMetadata::createFromObject($group_type->getAnonymousRole());
        $cacheable_metadata = $cacheable_metadata->merge($group_role_cacheable_metadata);
      }
    }
    else {
      // An authenticated user's group permissions might change when:
      // - They are updated to have different roles.
      // - They join a group.
      // - They leave a group.
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $cacheable_metadata = CacheableMetadata::createFromObject($user);

      // - Any of the outsider roles are updated.
      /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
      $storage = $this->entityTypeManager->getStorage('group_type');
      foreach ($storage->loadMultiple() as $group_type_id => $group_type) {
        $group_role_cacheable_metadata = CacheableMetadata::createFromObject($group_type->getOutsiderRole());
        $cacheable_metadata = $cacheable_metadata->merge($group_role_cacheable_metadata);
      }

      // - Any of their synchronized outsider roles are updated.
      /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('group_role');
      foreach ($storage->loadSynchronizedByUserRoles($user->getRoles(TRUE)) as $group_role) {
        $group_role_cacheable_metadata = CacheableMetadata::createFromObject($group_role);
        $cacheable_metadata = $cacheable_metadata->merge($group_role_cacheable_metadata);
      }

      // - Any of their member roles are updated.
      // - Any of their memberships are updated.
      foreach ($this->membershipLoader->loadByUser($user) as $group_membership) {
        $membership_cacheable_metadata = CacheableMetadata::createFromObject($group_membership);
        $cacheable_metadata = $cacheable_metadata->merge($membership_cacheable_metadata);

        foreach ($group_membership->getRoles() as $group_role) {
          $group_role_cacheable_metadata = CacheableMetadata::createFromObject($group_role);
          $cacheable_metadata = $cacheable_metadata->merge($group_role_cacheable_metadata);
        }
      }

      // @todo Take bypass permission into account, delete permission in 8.2.x.
    }

    return $cacheable_metadata;
  }

}
