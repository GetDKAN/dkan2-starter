<?php

namespace Drupal\group\Access;

use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the group permissions hash generator interface.
 */
interface GroupPermissionsHashGeneratorInterface {

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
