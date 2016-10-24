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
class CRM_Emailamender_Test extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testCorrection_ContactCreateApi() {

    CRM_Core_BAO_Setting::setItem(1, 'uk.org.futurefirst.networks.emailamender', 'emailamender.email_amender_enabled');
    
    $createContactResults = civicrm_api('Contact', 'create', array(
      'version' => 3,
      'sequential' => 1,
      'contact_type' => 'individual',
      'email' => 'john@yaho.com',
    ));

    $getEmailAddressResults = civicrm_api('Email', 'get', array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $createContactResults['id'],
    ));

    $this->assertEquals('john@yahoo.com', $getEmailAddressResults['values'][0]['email']);
  }


}
