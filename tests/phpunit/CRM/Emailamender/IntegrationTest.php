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
  use \Civi\Test\Api3TestTrait;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    civicrm_initialize();
    $this->callApiSuccess('Setting', 'create', [
      'emailamender.email_amender_enabled' => TRUE,
    ]);
    parent::setUp();
    $activities = $this->callAPISuccess('Activity', 'get', ['return' => 'id', 'activity_type_id' => 'corrected_email_address', 'sequential' => 1, 'options' => ['sort' => 'id DESC', 'limit' => 1]])['values'];
    $this->maxExistingActivityID = $activities[0] ?? 0;
  }

  /**
   * Ids of entities created.
   * @var array
   */
  protected $ids = [];

  public function tearDown() {
    $this->callApiSuccess('Activity', 'get', ['activity_type_id' => 'corrected_email_address', 'id' => ['>' => (int) $this->maxExistingActivityID], 'api.Activity.delete' => TRUE]);
    foreach (($this->ids['Contact']  ?? [])as $contactID) {
      $this->callAPISuccess('Contact', 'delete', ['skip_undelete' => 1, 'id' => $contactID]);
    }
    parent::tearDown();
  }

  /**
   * Utility function that uses the API to create a contact.
   *
   * @param string $emailAddress
   *
   * @return array
   *
   * @throws \CRM_Core_Exception
   */
  public function createTestContact($emailAddress) {
    $createContactResults = $this->callApiSuccess('Contact', 'create', [
      'contact_type' => 'individual',
      'email' => $emailAddress,
    ]);

    return $this->callApiSuccessGetSingle('Email', ['contact_id' => $createContactResults['id']]);
  }

  /**
   * @param int $contactId
   *
   * @return array|int
   * @throws \CRM_Core_Exception
   */
  public function getCorrectedEmailAddressActivityCount($contactId) {
    return $this->callApiSuccessGetCount('Activity', [
      'contact_id' => $contactId,
      'activity_type_id' => 'corrected_email_address',
    ]);
  }

  /**
   * Test for email addresses on contacts created via the API.
   *
   * @throws \CRM_Core_Exception
   */
  public function testContactCreateApi() {
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
      $emailDetails = $this->createTestContact($incorrectEmailAddress);

      // Test the email is correct.
      $this->assertEquals($expectedOutput, $emailDetails['email'], print_r($this->callApiSuccess('Setting', 'get', [])['values'], TRUE));

      // Test the expected activity is present.
      $this->assertEquals(1, $this->getCorrectedEmailAddressActivityCount($emailDetails['contact_id']));
    }
  }

  /**
   * Test that email addresses aren't updated if the setting is disabled.
   */
  public function testEnabledSettingOff() {
    $this->callApiSuccess('Setting', 'create', [
      'emailamender.email_amender_enabled' => FALSE,
    ]);
    $emailDetails = $this->createTestContact('john@yaho.com');
    $this->assertEquals('john@yaho.com', $emailDetails['email']);
    $this->assertEquals(0, $this->getCorrectedEmailAddressActivityCount($emailDetails['contact_id']), 'Found: ' . print_r(self::getCorrectedEmailAddressActivityCount($emailDetails['contact_id']), TRUE));
  }

  /**
   * Ensures that two corrections of the same type doesn't cause a problem.
   */
  public function testTwoCorrections() {
    $emailDetails = $this->createTestContact('john@yaho.com');
    $this->callApiSuccess('Email', 'create', [
      'contact_id' => $emailDetails['contact_id'],
      'location_type_id' => 1,
      'email' => 'john@yaho.com',
    ]);

    $getEmailResults = $this->callApiSuccess('Email', 'get', [
      'contact_id' => $emailDetails['contact_id'],
      'location_type_id' => 1,
      'email' => 'john@yahoo.com',
    ]);

    $this->assertEquals(2, $getEmailResults['count'], 'Wrong number of corrected emails found. Expected 2 found ' . $getEmailResults['count']);
    $this->assertEquals(2, $this->getCorrectedEmailAddressActivityCount($emailDetails['contact_id']));
  }

  /**
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public function testCsvImportCreateSingleContact() {
    $originalValues = [
      'first_name' => 'Bill',
      'last_name' => 'Gates',
      'email' => 'john@yaho.com',
    ];

    $fields = array_keys($originalValues);
    $values = array_values($originalValues);
    $parser = new CRM_Contact_Import_Parser_Contact($fields);
    $parser->_contactType = 'Individual';
    $parser->_onDuplicate = CRM_Import_Parser::DUPLICATE_NOCHECK;
    $parser->init();
    $this->assertEquals(
      CRM_Import_Parser::VALID,
      $parser->import(CRM_Import_Parser::DUPLICATE_NOCHECK, $values),
      'Return code from parser import was not as expected'
    );

    // Will assert if contact doesn't exist.
    $this->ids['Contact'][0] = $this->callApiSuccessGetSingle('Contact',  ['first_name' => 'Bill', 'last_name' => 'Gates'])['id'];

    $this->callApiSuccessGetSingle('Email', [
      'contact_id' => $this->ids['Contact'][0],
      'email' => 'john@yahoo.com',
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public function testCsvImportDuplicateActionTypes() {
    $this->ids['Contact'] = $this->ids['Contact'] ?? [];
    $duplicateActionTypes = [
      CRM_Import_Parser::DUPLICATE_SKIP,
      CRM_Import_Parser::DUPLICATE_REPLACE,
      CRM_Import_Parser::DUPLICATE_UPDATE,
      CRM_Import_Parser::DUPLICATE_FILL,
      CRM_Import_Parser::DUPLICATE_NOCHECK,
    ];

    foreach ($duplicateActionTypes as $duplicateActionType) {
      // Initialise the duplicate contact.
      $firstName = 'Bill ' . $duplicateActionType;
      $incorrectEmailAddress = 'bill' . $duplicateActionType . '@hotmial.com';
      $correctEmailAddress = 'bill' . $duplicateActionType . '@hotmail.com';

      // Create the duplicate contact.
      $this->callApiSuccess('Contact', 'create', [
        'contact_type' => 'Individual',
        'first_name' => $firstName,
        'last_name' => 'Gates',
        'email' => $correctEmailAddress,
      ])['id'];

      // Perform the import of duplicate contacts.
      $importValues = [
        'first_name' => $firstName,
        'last_name' => 'Gates',
        'email' => $incorrectEmailAddress,
      ];

      $fields = array_keys($importValues);
      $values = array_values($importValues);
      $parser = new CRM_Contact_Import_Parser_Contact($fields);
      $parser->_contactType = 'Individual';
      $parser->_onDuplicate = $duplicateActionType;
      $parser->init();
      $this->assertEquals(
        CRM_Import_Parser::VALID,
        $parser->import($duplicateActionType, $values),
        'Return code from parser import was not as expected'
      );

      // Check the results.
      // Currently the Email Address Corrector doesn't dedupe after changing the email address.
      // So we just check that both contacts exist and haven't broken on their way in.
      $getContacts = $this->callApiSuccess('Contact', 'get', ['first_name' => $firstName, 'last_name' => 'Gates', 'email' => $correctEmailAddress])['values'];
      $this->ids['Contact'] = array_merge(array_keys($getContacts), $this->ids['Contact']);
      $this->assertCount(2, $getContacts);
    }
  }

}
