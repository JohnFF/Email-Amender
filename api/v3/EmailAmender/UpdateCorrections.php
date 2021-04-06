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
  $aEscapedCorrections = [];

  foreach ($params['correction_keys'] as $index => $correctionKey) {
    $aEscapedCorrections[CRM_Core_DAO::escapeString($correctionKey)] = CRM_Core_DAO::escapeString($params['correction_values'][$index]);
  }
  switch ($params['domain_level']) {
    case 'top_level_domain':
      Civi::settings()->set('emailamender.top_level_domain_corrections', $aEscapedCorrections);
      break;

    case 'second_level_domain':
      Civi::settings()->set('emailamender.second_level_domain_corrections', $aEscapedCorrections);
      break;

    case 'equivalent_domain':
      Civi::settings()->set('emailamender.equivalent_domains', $aEscapedCorrections);
      break;

    default:
      throw new API_Exception(ts('Invalid domain amender setting update'));
  }

  return civicrm_api3_create_success([], $params, 'EmailAmender', 'UpdateCorrections');
}

