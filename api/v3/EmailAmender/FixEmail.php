<?php

/**
 * EmailAmender.fix_emailAPI specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_email_amender_fix_email_spec(&$spec) {

}

/**
 * EmailAmender.fix_emailAPI specification
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws \CiviCRM_API3_Exception
 *
 * @see civicrm_api3_create_success
 */
function civicrm_api3_email_amender_fix_email($params) {
  $emailAmender = CRM_Emailamender::singleton();
  $return = [];
  if ($emailAmender->fixEmailAddress($params['id'], $params['contact_id'], $params['email'])) {
    $return[$params['id']] = ['id' => $params['id'], 'contact_id' => $params['contact_id']];
  }

  return civicrm_api3_create_success($return);
}
