<?php

/**
 * SystemSettings.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_email_amender_update_settings_spec(&$spec) {
//  $spec['magicword']['api.required'] = 1;
}

function _civicrm_api3_email_amender_update_settings_safely($key, &$params){
  // TODO move to common functionality file
  if (!array_key_exists($key, $params)) {
    throw new API_Exception('Missing Array Key '.$key, 1);
  } // TODO else that mysql_real_escapes everything
}

/**
 * SystemSettings.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_email_amender_update_settings($params) {
   // TODO why doesn't this work? _civicrm_api3_system_settings_update_safely('email_amender_enabled', $params);

    CRM_Core_BAO_Setting::setItem(mysql_real_escape_string($params['email_amender_enabled']), 'uk.org.futurefirst.networks.emailamender', 'email_amender_enabled');

    return civicrm_api3_create_success($returnValues, $params, 'EmailAmender', 'update_settings');

}
