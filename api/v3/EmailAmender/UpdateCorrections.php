<?php

/**
 * EmailAmenderSettings.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */

function _civicrm_api3_email_amender_update_corrections_spec(&$spec) {
  $spec['domain_level']['api.required'] = 1;
}

/**
 * EmailAmenderSettings.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_email_amender_update_corrections($params) {

  $sCivicrmSettingsKey = "";

  switch($params['domain_level']){
    case 'top_level_domain':
      $sCivicrmSettingsKey = 'emailamender.top_level_domain_corrections';
      break;

    case 'second_level_domain':
      $sCivicrmSettingsKey = 'emailamender.second_level_domain_corrections';
      break;

    case 'equivalent_domain':
      $sCivicrmSettingsKey = 'emailamender.equivalent_domains';
      break;

    default:
      return civicrm_api3_create_error(ts("Couldn't update settings - invalid domain level"),
        array('error_code' => '1', 'field' => 'domain_level')
      );
  }

  $aEscapedCorrections = array();

  foreach( $params['correction_keys'] as $index => $correctionKey ){
    $aEscapedCorrections[CRM_Core_DAO::escapeString($correctionKey)] = CRM_Core_DAO::escapeString($params['correction_values'][$index]);
  }

  CRM_Core_BAO_Setting::setItem($aEscapedCorrections, 'uk.org.futurefirst.networks.emailamender', $sCivicrmSettingsKey);

  return civicrm_api3_create_success(array(), $params, 'EmailAmender', 'UpdateCorrections');
}

