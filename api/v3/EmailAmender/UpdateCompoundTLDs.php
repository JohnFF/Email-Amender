<?php

/**
 * EmailAmenderCompoundTLDs.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_email_amender_update_compound_t_l_ds_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * EmailAmenderCompoundTLDs.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_email_amender_update_compound_t_l_ds($params) { 

  $aEscapedCompoundTLDs = array();
  $sCivicrmSettingsKey = "";

  foreach( $params['compound_tlds'] as $compoundTLDs ){
    $aEscapedCorrections[] = mysql_real_escape_string($compoundTLDs);
  }

  CRM_Core_BAO_Setting::setItem($aEscapedCorrections, 'uk.org.futurefirst.networks.emailamender', 'compound_top_level_domains');

  return civicrm_api3_create_success($returnValues, $params, 'EmailAmenderCompoundTLDs', 'Update');
}

