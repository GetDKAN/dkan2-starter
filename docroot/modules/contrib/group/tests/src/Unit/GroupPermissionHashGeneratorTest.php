<?php

namespace Drupal\Tests\group\Unit;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\group\Access\CalculatedGroupPermissions;
use Drupal\group\Access\GroupPermissionCalculatorInterface;
use Drupal\group\Access\GroupPermissionsHashGenerator;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Tests the group permission hash generator service.
 *
 * @coversDefaultClass \Drupal\group\Access\GroupPermissionsHashGenerator
 * @group group
 */
class GroupPermissionHashGeneratorTest extends UnitTestCase {

  /**
   * The group permissions hash generator service.
   *
   * @var \Drupal\group\Access\GroupPermissionsHashGeneratorInterface
   */
  protected $hashGenerator;

  /**
   * The group permission calculator.
   *
   * @var \Drupal\group\Access\GroupPermissionCalculatorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $permissionCalculator;

  /**
   * The static cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $static;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    new Settings(['hash_salt' => 'SALT']);
    $private_key = $this->prophesize(PrivateKey::class);
    $private_key->get()->willReturn('');
    $this->static = $this->prophesize(CacheBackendInterface::class);
    $this->permissionCalculator = $this->prophesize(GroupPermissionCalculatorInterface::class);
    $this->hashGenerator = new GroupPermissionsHashGenerator($private_key->reveal(), $this->static->reveal(), $this->permissionCalculator->reveal());
  }

  /**
   * Tests the generation of the anonymous hash.
   *
   * @covers ::generateAnonymousHash
   */
  public function testGenerateAnonymousHash() {
    $cid = 'group_anonymous_permissions_hash';
    $original_permissions = [
      'foo' => ['baz', 'bar'],
      'alice' => ['bob'],
    ];
    $sorted_permissions = [
      'alice' => ['bob'],
      'foo' => ['bar', 'baz'],
    ];
    $expected_hash = hash('sha256', 'SALT' . serialize($sorted_permissions));

    $this->static->get($cid)->willReturn(FALSE);
    $this->static->set($cid, $expected_hash, Cache::PERMANENT, [])->shouldBeCalledTimes(1);
    $calculated_permissions = new CalculatedGroupPermissions();
    $calculated_permissions->setAnonymousPermissions($original_permissions);
    $this->permissionCalculator->calculateAnonymousPermissions()->willReturn($calculated_permissions);
    $this->assertEquals($expected_hash, $this->hashGenerator->generateAnonymousHash(), 'The anonymous hash was generated based on the sorted calculated permissions.');

    $cache = (object) ['data' => 'foobar'];
    $this->static->get($cid)->willReturn($cache);
    $this->static->set($cid, 'foobar', Cache::PERMANENT, [])->shouldNotBeCalled();
    $this->assertEquals('foobar', $this->hashGenerator->generateAnonymousHash(), 'The anonymous hash was retrieved from the static cache.');
  }

  /**
   * Tests the generation of the authenticated hash.
   *
   * @covers ::generateAuthenticatedHash
   */
  public function testGenerateAuthenticatedHash() {
    $account = $this->prophesize(AccountInterface::class);
    $account->id()->willReturn(24101986);
    $account = $account->reveal();

    $cid = 'group_authenticated_permissions_24101986';
    $outsider_permissions = [
      'foo' => ['baz', 'bar'],
      'alice' => ['bob'],
    ];
    $member_permissions = [
      16 => ['sweet'],
    ];
    $sorted_permissions = [
      'alice' => ['bob'],
      'foo' => ['bar', 'baz'],
      16 => ['sweet'],
    ];
    $expected_hash = hash('sha256', 'SALT' . serialize($sorted_permissions));

    $this->static->get($cid)->willReturn(FALSE);
    $this->static->set($cid, $expected_hash, Cache::PERMANENT, [])->shouldBeCalledTimes(1);
    $calculated_permissions = new CalculatedGroupPermissions();
    $calculated_permissions->setOutsiderPermissions($outsider_permissions);
    $calculated_permissions->setMemberPermissions($member_permissions);
    $this->permissionCalculator->calculateAuthenticatedPermissions($account)->willReturn($calculated_permissions);
    $this->assertEquals($expected_hash, $this->hashGenerator->generateAuthenticatedHash($account), 'The authenticated hash was generated based on the sorted calculated permissions.');

    $cache = (object) ['data' => 'foobar'];
    $this->static->get($cid)->willReturn($cache);
    $this->static->set($cid, 'foobar', Cache::PERMANENT, [])->shouldNotBeCalled();
    $this->assertEquals('foobar', $this->hashGenerator->generateAuthenticatedHash($account), 'The authenticated hash was retrieved from the static cache.');
  }

  /**
   * Tests whether anonymous users and 'pure' outsiders can share a hash.
   *
   * @depends testGenerateAnonymousHash
   * @depends testGenerateAuthenticatedHash
   */
  public function testAnonymousOutsiderHashReusability() {
    $account = $this->prophesize(AccountInterface::class);
    $account->id()->willReturn(24101986);
    $account = $account->reveal();

    $permissions_ao = [
      'foo' => ['baz', 'bar'],
      'alice' => ['bob'],
    ];
    $permissions_m = [
      16 => ['sweet'],
    ];

    $calculated_permissions_anon = new CalculatedGroupPermissions();
    $calculated_permissions_anon->setAnonymousPermissions($permissions_ao);
    $calculated_permissions_auth = new CalculatedGroupPermissions();
    $calculated_permissions_auth->setOutsiderPermissions($permissions_ao);
    $calculated_permissions_auth->setMemberPermissions($permissions_m);
    $this->permissionCalculator->calculateAnonymousPermissions()->willReturn($calculated_permissions_anon);
    $this->permissionCalculator->calculateAuthenticatedPermissions($account)->willReturn($calculated_permissions_auth);

    $this->static->get(Argument::any())->willReturn(FALSE);
    $this->static->set(Argument::cetera())->willReturn(NULL);

    $this->assertNotEquals(
      $this->hashGenerator->generateAnonymousHash(),
      $this->hashGenerator->generateAuthenticatedHash($account),
      'Hashes for an anonymous and outsider user with different group permissions differ.'
    );

    $calculated_permissions_auth->setMemberPermissions([]);
    $this->assertEquals(
      $this->hashGenerator->generateAnonymousHash(),
      $this->hashGenerator->generateAuthenticatedHash($account),
      'Hashes for an anonymous and outsider user with the same group permissions are the same.'
    );
  }

}
