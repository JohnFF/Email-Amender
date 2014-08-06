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
//  $spec['magicword']['api.required'] = 1;
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

  if (!array_key_exists('domain_level', $params)){
    throw new API_Exception(/*errorMessage*/'Missing array key domain level', /*errorCode*/ 1);
  }

  $aEscapedCorrections = array();
  $sCivicrmSettingsKey = "";
  
  switch($params['domain_level']){
    case 'top_level_domain':
      $sCivicrmSettingsKey = 'top_level_domain_corrections';
      break;

    case 'second_level_domain':
      $sCivicrmSettingsKey = 'second_level_domain_corrections';
      break;

    case 'equivalent_domain':
      $sCivicrmSettingsKey = 'equivalent_domains';
      break;

    default:
      return civicrm_api3_create_error(ts("Couldn't update settings - invalid domain level"), 
        array('error_code' => '2', 'field' => 'domain_level')
      );
  }

  foreach( $params['correction_keys'] as $index => $correctionKey ){
    $aEscapedCorrections[mysql_real_escape_string($correctionKey)] = mysql_real_escape_string($params['correction_values'][$index]);
  }

  CRM_Core_BAO_Setting::setItem($aEscapedCorrections, 'uk.org.futurefirst.networks.emailamender', $sCivicrmSettingsKey);

  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  // return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  return civicrm_api3_create_success($returnValues, $params, 'EmailAmender', 'UpdateCorrections');
}

