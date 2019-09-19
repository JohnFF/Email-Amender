<?php

require_once 'emailamender.civix.php';

/**
 * Implements hook_civicrm_config().
 */
function emailamender_civicrm_config(&$config) {
  _emailamender_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 */
function emailamender_civicrm_xmlMenu(&$files) {
  _emailamender_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function emailamender_civicrm_install() {
  CRM_Core_BAO_OptionValue::ensureOptionValueExists([
    'label'        => 'Corrected Email Address',
    'name'         => 'corrected_email_address',
    'weight'       => '1',
    'description'  => 'Automatically corrected emails (by the Email Address Corrector extension).',
    'option_group_id' => 'activity_type',
  ]);
  return _emailamender_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function emailamender_civicrm_uninstall() {

  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE name LIKE 'emailamender%'");

  return _emailamender_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function emailamender_civicrm_enable() {
  return _emailamender_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function emailamender_civicrm_disable() {
  return _emailamender_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function emailamender_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _emailamender_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function emailamender_civicrm_managed(&$entities) {
  return _emailamender_civix_civicrm_managed($entities);
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

  $emailAmender = new CRM_Emailamender();
  $emailAmender->check_for_corrections($params->id, $params->contact_id, $params->email);
}

/**
 * Implements hook_civicrm_emailProcessorContact().
 */
function emailamender_civicrm_emailProcessorContact($email, $contactID, &$result) {
  CRM_Emailamender_Equivalentmatcher::processHook($email, $contactID, $result);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function emailamender_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _emailamender_civix_civicrm_alterSettingsFolders($metaDataFolders);
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

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function emailamender_civicrm_angularModules(&$angularModules) {
  _emailamender_civix_civicrm_angularModules($angularModules);
}
