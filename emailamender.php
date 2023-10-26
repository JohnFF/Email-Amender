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
 * Implements hook_civicrm_uninstall().
 */
function emailamender_civicrm_uninstall() {
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE name LIKE 'emailamender%'");
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
 * Implements hook_civicrm_searchTasks().
 */
function emailamender_civicrm_searchTasks($objectType, &$tasks) {
  if ($objectType === 'contact') {
    $tasks[] = [
      'title'  => ts('Email - correct email addresses'),
      'class'  => 'CRM_Emailamender_Form_Task_Correctemailaddresses',
      'result' => TRUE,
    ];
  }
}

/**
 * Implements hook_civicrm_permission().
 *
 * @see CRM_Utils_Hook::permission()
 * @param array $permissions
 */
function emailamender_civicrm_permission(&$permissions) {
  $permissions['administer_email_amender'] = [
    E::ts('Email Amender'),
    E::ts('administer email corrections'),
  ];
}

function emailamender_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['email_amender']['default'] = 'administer_email_amender';
}
