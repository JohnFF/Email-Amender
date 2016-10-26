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
class CRM_Emailamender_IntegrationTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    CRM_Core_BAO_Setting::setItem(1, 'uk.org.futurefirst.networks.emailamender', 'emailamender.email_amender_enabled');
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Utility function that uses the API to create a contact.
   *
   * @param string $emailAddress
   * @return int created contact id
   */
  public function createTestContact($emailAddress) {
    $createContactResults = civicrm_api('Contact', 'create', array(
      'version' => 3,
      'sequential' => 1,
      'contact_type' => 'individual',
      'email' => $emailAddress,
    ));

    $getEmailAddressResults = civicrm_api('Email', 'get', array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $createContactResults['id'],
    ));

    return $getEmailAddressResults['values'][0];
  }

  public function getCorrectedEmailAddressActivityCount($contactId) {
    return civicrm_api('Activity', 'getcount', array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $contactId,
      'activity_type_id' => 'corrected_email',
    ));    
  }
  
  /**
   * Test for email addresses on contacts created via the API.
   */
  public function testContactCreateApi() {
    $testEmailCorrections = array(
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
    );

    foreach($testEmailCorrections as $incorrectEmailAddress => $expectedOutput){
      $emailDetails = self::createTestContact($incorrectEmailAddress);
      
      // Test the email is correct.
      $this->assertEquals($expectedOutput, $emailDetails['email']);
      
      // Test the expected activity is present.

      $this->assertEquals(1, self::getCorrectedEmailAddressActivityCount($emailDetails['contact_id']));
    }
  }

  /**
   * Test that email addresses aren't updated if the setting is disabled.
   */
  public function testEnabledSettingOff() {
    CRM_Core_BAO_Setting::setItem(0, 'uk.org.futurefirst.networks.emailamender', 'emailamender.email_amender_enabled');
    $emailDetails = self::createTestContact('john@yaho.com');
    $this->assertEquals('john@yaho.com', $emailDetails['email']);
    $this->assertEquals(0, self::getCorrectedEmailAddressActivityCount($emailDetails['contact_id']));
  }

  /**
   * Ensures that two corrections of the same type doesn't cause a problem.
   */
  public function testTwoCorrections() {
    $emailDetails = self::createTestContact('john@yaho.com');
    civicrm_api('Email', 'create', array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $emailDetails['contact_id'],
      'location_type_id' => 1,
      'email' => 'john@yaho.com',
    ));

    $getEmailResults = civicrm_api('Email', 'get', array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $emailDetails['contact_id'],
      'location_type_id' => 1,
      'email' => 'john@yahoo.com',
    ));
    
    $this->assertEquals(2, $getEmailResults['count']);
  }
  
  public function testApiEmailAddressUpdateApi(){}

  public function testCsvImportCreateContact(){}

  public function testCsvImportUpdateContact(){}

  public function testContactCreateEmailProcess(){}

  public function testContactUpdateEmailProcess(){}

  public function testOnDedupe(){}
}
