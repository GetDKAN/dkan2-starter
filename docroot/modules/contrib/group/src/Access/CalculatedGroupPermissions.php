<?php

namespace Drupal\group\Access;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Represents a calculated set of group permissions with cacheable metadata.
 *
 * @see \Drupal\group\Access\GroupPermissionCalculator
 */
class CalculatedGroupPermissions implements CalculatedGroupPermissionsInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * A list of anonymous permissions per group type.
   *
   * @var array
   */
  protected $anonymousPermissions = [];

  /**
   * A list of outsider permissions per group type.
   *
   * @var array
   */
  protected $outsiderPermissions = [];

  /**
   * A list of member permissions per group.
   *
   * @var array
   */
  protected $memberPermissions = [];

  /**
   * {@inheritdoc}
   */
  public function merge(CalculatedGroupPermissions $other) {
    foreach ($other->getAnonymousPermissions() as $group_type_id => $permissions) {
      $merged = array_merge($this->getAnonymousPermissions($group_type_id), $permissions);
      $this->addAnonymousPermissions($group_type_id, array_unique($merged));
    }
    foreach ($other->getOutsiderPermissions() as $group_type_id => $permissions) {
      $merged = array_merge($this->getOutsiderPermissions($group_type_id), $permissions);
      $this->addOutsiderPermissions($group_type_id, array_unique($merged));
    }
    foreach ($other->getMemberPermissions() as $group_id => $permissions) {
      $merged = array_merge($this->getMemberPermissions($group_id), $permissions);
      $this->addMemberPermissions($group_id, array_unique($merged));
    }
    $this->addCacheableDependency($other);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnonymousPermissions($group_type_id = NULL) {
    if (isset($group_type_id)) {
      // @todo Switch to ?? operator when minimum PHP support > 7.0.
      return isset($this->anonymousPermissions[$group_type_id])
        ? $this->anonymousPermissions[$group_type_id]
        : [];
    }
    return $this->anonymousPermissions;
  }

  /**
   * {@inheritdoc}
   */
  public function addAnonymousPermissions($group_type_id, array $permissions) {
    $this->anonymousPermissions[$group_type_id] = $permissions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAnonymousPermissions(array $permissions) {
    $this->anonymousPermissions = $permissions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutsiderPermissions($group_type_id = NULL) {
    if (isset($group_type_id)) {
      // @todo Switch to ?? operator when minimum PHP support > 7.0.
      return isset($this->outsiderPermissions[$group_type_id])
        ? $this->outsiderPermissions[$group_type_id]
        : [];
    }
    return $this->outsiderPermissions;
  }

  /**
   * {@inheritdoc}
   */
  public function addOutsiderPermissions($group_type_id, array $permissions) {
    $this->outsiderPermissions[$group_type_id] = $permissions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOutsiderPermissions(array $permissions) {
    $this->outsiderPermissions = $permissions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMemberPermissions($group_id = NULL) {
    if (isset($group_id)) {
      // @todo Switch to ?? operator when minimum PHP support > 7.0.
      return isset($this->memberPermissions[$group_id])
        ? $this->memberPermissions[$group_id]
        : [];
    }
    return $this->memberPermissions;
  }

  /**
   * {@inheritdoc}
   */
  public function addMemberPermissions($group_id, array $permissions) {
    $this->memberPermissions[$group_id] = $permissions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemberPermissions(array $permissions) {
    $this->memberPermissions = $permissions;
    return $this;
  }

}
