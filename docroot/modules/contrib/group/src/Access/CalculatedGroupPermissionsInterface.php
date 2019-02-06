<?php

namespace Drupal\group\Access;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Defines the calculated group permissions interface.
 */
interface CalculatedGroupPermissionsInterface extends RefinableCacheableDependencyInterface {

  /**
   * Merge another calculated group permissions object into this one.
   *
   * This merges (not replaces) all permissions and cacheable metadata.
   *
   * @param \Drupal\group\Access\CalculatedGroupPermissions $other
   *   The other calculated group permissions object to merge into this one.
   *
   * @return $this
   */
  public function merge(CalculatedGroupPermissions $other);

  /**
   * Retrieves the anonymous permissions.
   *
   * @param string|null $group_type_id
   *   (optional) The group type ID to retrieve the anonymous permissions for.
   *   Leave blank to retrieve all anonymous permissions as a nested array with
   *   permission sets for values and group type IDs for keys.
   *
   * @return array|string[]
   *   A nested array of permissions per group type or a single set of
   *   permission names if $group_type_id was provided.
   */
  public function getAnonymousPermissions($group_type_id = NULL);

  /**
   * Adds a single set of anonymous permissions for a given group type.
   *
   * @param string $group_type_id
   *   The group type ID to add the anonymous permissions for.
   * @param string[] $permissions
   *   The names of the anonymous permissions to add.
   *
   * @return $this
   */
  public function addAnonymousPermissions($group_type_id, array $permissions);

  /**
   * Sets the anonymous permissions all at once.
   *
   * @param array $permissions
   *   All anonymous permissions as a nested array with permission sets for
   *   values and group type IDs for keys.
   *
   * @return $this
   */
  public function setAnonymousPermissions(array $permissions);

  /**
   * Retrieves the outsider permissions.
   *
   * @param string|null $group_type_id
   *   (optional) The group type ID to retrieve the outsider permissions for.
   *   Leave blank to retrieve all anonymous permissions as a nested array with
   *   permission sets for values and group type IDs for keys.
   *
   * @return array|string[]
   *   A nested array of permissions per group type or a single set of
   *   permission names if $group_type_id was provided.
   */
  public function getOutsiderPermissions($group_type_id = NULL);

  /**
   * Adds a single set of outsider permissions for a given group type.
   *
   * @param string $group_type_id
   *   The group type ID to add the outsider permissions for.
   * @param string[] $permissions
   *   The names of the outsider permissions to add.
   *
   * @return $this
   */
  public function addOutsiderPermissions($group_type_id, array $permissions);

  /**
   * Sets the outsider permissions all at once.
   *
   * @param array $permissions
   *   All outsider permissions as a nested array with permission sets for
   *   values and group type IDs for keys.
   *
   * @return $this
   */
  public function setOutsiderPermissions(array $permissions);

  /**
   * Retrieves the member permissions.
   *
   * @param int|null $group_id
   *   (optional) The group ID to retrieve the outsider permissions for. Leave
   *   blank to retrieve all member permissions as a nested array with
   *   permission sets for values and group IDs for keys.
   *
   * @return array|string[]
   *   A nested array of permissions per group or a single set of permission
   *   names if $group_id was provided.
   */
  public function getMemberPermissions($group_id = NULL);

  /**
   * Adds a single set of member permissions for a given group.
   *
   * @param int $group_id
   *   The group ID to add the member permissions for.
   * @param string[] $permissions
   *   The names of the member permissions to add.
   *
   * @return $this
   */
  public function addMemberPermissions($group_id, array $permissions);

  /**
   * Sets the member permissions all at once.
   *
   * @param array $permissions
   *   All member permissions as a nested array with permission sets for values
   *   and group IDs for keys.
   *
   * @return $this
   */
  public function setMemberPermissions(array $permissions);

}
