<?php

namespace Drupal\group\Access;

use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the group permissions hash generator interface.
 */
interface GroupPermissionsHashGeneratorInterface {

  /**
   * Generates a hash that uniquely identifies a group member's permissions.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group for which to get the permissions hash.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to get the permissions hash.
   *
   * @return string
   *   A permissions hash.
   *
   * @deprecated in Group 1.0-rc3, will be removed before Group 1.0. Use the
   * more specific hash generating methods on this interface instead.
   */
  public function generate(GroupInterface $group, AccountInterface $account);

  /**
   * Generates a hash that uniquely identifies the anonymous group permissions.
   *
   * @return string
   *   A permissions hash.
   */
  public function generateAnonymousHash();

  /**
   * Generates a hash for an authenticated user's complete group permissions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account for which to get the permissions hash.
   *
   * @return string
   *   A permissions hash.
   */
  public function generateAuthenticatedHash(AccountInterface $account);

}
