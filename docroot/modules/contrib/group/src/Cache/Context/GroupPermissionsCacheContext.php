<?php

namespace Drupal\group\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Access\GroupPermissionCalculatorInterface;
use Drupal\group\Access\GroupPermissionsHashGeneratorInterface;

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
   * The permissions hash generator.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface
   */
  protected $permissionsHashGenerator;

  /**
   * The group permission calculator.
   *
   * @var \Drupal\group\Access\GroupPermissionCalculatorInterface
   */
  protected $groupPermissionCalculator;

  /**
   * Constructs a new GroupMembershipPermissionsCacheContext class.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\group\Access\GroupPermissionsHashGeneratorInterface $hash_generator
   *   The permissions hash generator.
   * @param \Drupal\group\Access\GroupPermissionCalculatorInterface $permission_calculator
   *   The group permission calculator.
   */
  public function __construct(AccountProxyInterface $current_user, GroupPermissionsHashGeneratorInterface $hash_generator, GroupPermissionCalculatorInterface $permission_calculator) {
    $this->currentUser = $current_user;
    $this->permissionsHashGenerator = $hash_generator;
    $this->groupPermissionCalculator = $permission_calculator;
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
    // @todo Take bypass permission into account, delete permission in 8.2.x.
    if ($this->currentUser->isAnonymous()) {
      return $this->permissionsHashGenerator->generateAnonymousHash();
    }
    return $this->permissionsHashGenerator->generateAuthenticatedHash($this->currentUser);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    // @todo Take bypass permission into account, delete permission in 8.2.x.
    // The permission hash generator should use the calculated permissions to
    // generate the permission hash with. Because we already define cacheable
    // metadata while calculating the permissions, we can simply return said
    // information here.
    return CacheableMetadata::createFromObject($this->groupPermissionCalculator->calculatePermissions($this->currentUser));
  }

}
