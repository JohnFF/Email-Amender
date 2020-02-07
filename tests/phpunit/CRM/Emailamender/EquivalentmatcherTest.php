<?php

use CRM_Emailamender_ExtensionUtil as E;
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
class CRM_Emailamender_EquivalentmatcherTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Example: Test that a version is returned.
   */
  public function testEquivalentMatch() {

    // Test contact with gmail address receiving a googlemail email.
    $gmailContactDetails = civicrm_api3('contact', 'create', array('contact_type' => 'Individual', 'email' => 'gmailtest@gmail.com'));
    $gmailContactResult = NULL;
    CRM_Emailamender_Equivalentmatcher::processHook('gmailtest@googlemail.com', NULL, $gmailContactResult);
    $this->assertEquals($gmailContactDetails['id'], $gmailContactResult['contactID']);
    $this->assertEquals(CRM_Utils_Mail_Incoming::EMAILPROCESSOR_OVERRIDE, $gmailContactResult['action']);

    // Test contact with googlemail address receiving a gmail email.
    $googlemailContactDetails = civicrm_api3('contact', 'create', array('contact_type' => 'Individual', 'email' => 'googlemailtest@googlemail.com'));
    $googlemailResult = NULL;
    CRM_Emailamender_Equivalentmatcher::processHook('googlemailtest@gmail.com', NULL, $googlemailResult);
    $this->assertEquals($googlemailContactDetails['id'], $googlemailResult['contactID']);
    $this->assertEquals(CRM_Utils_Mail_Incoming::EMAILPROCESSOR_OVERRIDE, $googlemailResult['action']);

    // Test contact with a present and valid email address.
    $presentContactDetails = civicrm_api3('contact', 'create', array('contact_type' => 'Individual', 'email' => 'present@gmail.com'));
    $presentResult = NULL;
    CRM_Emailamender_Equivalentmatcher::processHook('present@gmail.com', $presentContactDetails['id'], $presentResult);
    $this->assertNull($presentResult); // Want normal process, not overridden.

    // Test that the result is null if there's a non-equivalent email and an unknown domain.
    $nonequivalentUnknownDomainResult = NULL;
    CRM_Emailamender_Equivalentmatcher::processHook('uniqueemail@test.test', NULL, $nonequivalentUnknownDomainResult);
    $this->assertNull($nonequivalentUnknownDomainResult);

    // Test that the result is null if there's a non-equivalent email and a known domain.
    $nonequivalentKnownDomainResult = NULL;
    CRM_Emailamender_Equivalentmatcher::processHook('uniqueemail@gmail.com', NULL, $nonequivalentKnownDomainResult);
    $this->assertNull($nonequivalentKnownDomainResult);
  }

}
