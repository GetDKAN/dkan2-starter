<?php

namespace Drupal\group\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Calculates group permissions for an account.
 */
class GroupPermissionChecker implements GroupPermissionCheckerInterface {

  /**
   * The group permission calculator.
   *
   * @var \Drupal\group\Access\GroupPermissionCalculatorInterface
   */
  protected $groupPermissionCalculator;

  /**
   * Constructs a GroupPermissionChecker object.
   *
   * @param \Drupal\group\Access\GroupPermissionCalculatorInterface $permission_calculator
   *   The group permission calculator.
   */
  public function __construct(GroupPermissionCalculatorInterface $permission_calculator) {
    $this->groupPermissionCalculator = $permission_calculator;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermissionInGroup($permission, AccountInterface $account, GroupInterface $group) {
    // If the account can bypass all group access, return immediately.
    if ($account->hasPermission('bypass group access')) {
      return TRUE;
    }

    // Before anything else, check if the user can administer the group.
    if ($permission != 'administer group' && $this->hasPermissionInGroup('administer group', $account, $group)) {
      return TRUE;
    }

    $calculated_permissions = $this->groupPermissionCalculator->calculatePermissions($account);
    if ($account->isAnonymous()) {
      return in_array($permission, $calculated_permissions->getAnonymousPermissions($group->bundle()), TRUE);
    }
    else {
      // If the user has member permissions for this group, check those.
      if (array_key_exists($group->id(), $calculated_permissions->getMemberPermissions())) {
        return in_array($permission, $calculated_permissions->getMemberPermissions($group->id()), TRUE);
      }
      // Otherwise, we need to check the outsider permissions instead.
      else {
        return in_array($permission, $calculated_permissions->getOutsiderPermissions($group->bundle()), TRUE);
      }
    }
  }

}
