<?php

require_once 'emailamender.civix.php';

use CRM_Emailamender_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 */
function emailamender_civicrm_config(&$config) {
  _emailamender_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 */
function emailamender_civicrm_install() {
  CRM_Core_BAO_OptionValue::ensureOptionValueExists([
    'label'        => E::ts('Corrected Email Address'),
    'name'         => 'corrected_email_address',
    'weight'       => '1',
    'description'  => 'Automatically corrected emails (by the Email Address Corrector extension).',
    'option_group_id' => 'activity_type',
    'filter' => 1,
  ]);
  return _emailamender_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function emailamender_civicrm_uninstall() {

  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE name LIKE 'emailamender%'");

  return;
}

/**
 * Implements hook_civicrm_enable().
 */
function emailamender_civicrm_enable() {
  return _emailamender_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_post().
 *
 * Amends the emails after creation according to the stored amender settings.
 */
function emailamender_civicrm_post($op, $objectName, $id, &$params) {
  // 1. ignore all operations other than adding an email address
  if ($objectName !== 'Email' || $op !== 'create' || !Civi::settings()->get('emailamender.email_amender_enabled')) {
    return;
  }

  $emailAmender = CRM_Emailamender::singleton();
  $emailAmender->fixEmailAddress($params->id, $params->contact_id, $params->email);
}

/**
 * Implements hook_civicrm_emailProcessorContact().
 */
function emailamender_civicrm_emailProcessorContact($email, $contactID, &$result) {
  CRM_Emailamender_Equivalentmatcher::processHook($email, $contactID, $result);
}

/**
 * civicrm_civicrm_navigationMenu
 *
 * implementation of civicrm_civicrm_navigationMenu
 *
 */
function emailamender_civicrm_navigationMenu(&$params) {
  $sAdministerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  $sSystemSettingsMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'System Settings', 'id', 'name');

  //  Get the maximum key of $params
  $maxKey = max(array_keys($params));

  $params[$sAdministerMenuId]['child'][$sSystemSettingsMenuId]['child'][$maxKey + 1] = array(
    'attributes' => array(
      'label'      => 'Email Address Corrector Settings',
      'name'       => 'EmailAmenderSettings',
      'url'        => 'civicrm/emailamendersettings',
      'permission' => NULL,
      'operator'   => NULL,
      'separator'  => NULL,
      'parentID'   => $sSystemSettingsMenuId,
      'navID'      => $maxKey + 1,
      'active'     => 1,
    ),
  );
}

/**
 * Implements hook_civicrm_searchTasks().
 */
function emailamender_civicrm_searchTasks($objectType, &$tasks) {
  if ($objectType === 'contact') {
    $tasks[] = array(
      'title'  => ts('Email - correct email addresses'),
      'class'  => 'CRM_Emailamender_Form_Task_Correctemailaddresses',
      'result' => TRUE,
    );
  }
}
