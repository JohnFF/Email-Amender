<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Emailamender_BatchUpdateTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3TestTrait;

  protected $maxExistingActivityID;

  /**
   * @return \Civi\Test\CiviEnvBuilder
   * @throws \CRM_Extension_Exception_ParseException
   */
  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    $this->callAPISuccess('Setting', 'create', [
      'emailamender.email_amender_enabled' => 'true',
    ]);
    // Cleanup first in  case any values are 'hanging around'
    $this->callApiSuccess('EmailAmender', 'batch_update', [])['values'];
    parent::setUp();
    $activities = $this->callAPISuccess('Activity', 'get', ['return' => 'id', 'activity_type_id' => 'corrected_email_address', 'sequential' => 1, 'options' => ['sort' => 'id DESC', 'limit' => 1]])['values'];
    $this->maxExistingActivityID = $activities[0]['id'] ?? 0;
  }

  public function tearDown(): void {
    $this->callApiSuccess('Activity', 'get', ['activity_type_id' => 'corrected_email_address', 'id' => ['>' => $this->maxExistingActivityID], 'api.Activity.delete' => TRUE]);
    foreach ($this->ids['Contact'] as $contactID) {
      $this->callAPISuccess('Contact', 'delete', ['skip_undelete' => 1, 'id' => $contactID]);
    }
    parent::tearDown();
  }

  /**
   * Test for email addresses on contacts created via the API.
   *
   * @throws \CRM_Core_Exception
   */
  public function testBatchUpdate() {
    $this->callApiSuccess('Setting', 'create', ['emailamender.email_amender_enabled' => FALSE]);
    $testEmailCorrections = [
      // Test contacts with an incorrect top level domain.
      'john@gmail.cpm' => 'john@gmail.com',
      'john@hotmail.con' => 'john@hotmail.com',

      // Test contacts with an incorrect second level domain.
      'john@gmial.com' => 'john@gmail.com',
      'john@hotmial.com' => 'john@hotmail.com',
      'john@hotmil.com' => 'john@hotmail.com',
      'john@yaho.com' => 'john@yahoo.com',

      // Test contacts with both an incorrect top and second level domain.
      'john@gmial.con' => 'john@gmail.com',

      // Test contacts with a compound top level domain.
      'john@gmial.ac.uk' => 'john@gmail.ac.uk',

      // Test contacts with a three level domain.
      'john@gmial.gmial.com' => 'john@gmial.gmail.com',
      // Test contacts with a three level domain and a compound top level domain.
      'john@gmial.gmial.ac.uk' => 'john@gmial.gmail.ac.uk',
    ];

    foreach ($testEmailCorrections as $incorrectEmailAddress => $expectedOutput) {
      $this->ids['Contact'][] = $this->callApiSuccess('Contact', 'create', ['contact_type' => 'Individual', 'email' => $incorrectEmailAddress])['id'];
    }

    $candidates = $this->callApiSuccess('EmailAmender', 'find_candidates', [])['values'];
    $this->assertCount(10, $candidates);

    $clean = $this->callApiSuccess('EmailAmender', 'batch_update', [])['values'];
    $this->assertCount(10, $clean);

    $candidates = $this->callApiSuccess('EmailAmender', 'find_candidates', [])['values'];
    // Still 2 because we aren't catching the sub domain ones yet.
    $this->assertCount(2, $candidates);

    $this->callApiSuccessGetCount('Activity', ['activity_type_id' => 'corrected_email_address', 'id' => ['>' => $this->maxExistingActivityID]], 10);
    $this->assertEquals('john@gmail.com', $this->callAPISuccessGetValue('Contact', ['id' => $this->ids['Contact'][0], 'return' => 'display_name']));
  }

}
