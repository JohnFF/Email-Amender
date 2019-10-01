<?php

/**
 * EmailAmender.find_candidatesAPI specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_email_amender_find_candidates_spec(&$spec) {

}

/**
 * EmailAmender.find_candidatesAPI specification
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws \CiviCRM_API3_Exception
 * @throws \API_Exception
 *
 * @see civicrm_api3_create_success
 */
function civicrm_api3_email_amender_find_candidates($params) {
  $topLevel = Civi::settings()->get('emailamender.top_level_domain_corrections');
  $secondLevel = Civi::settings()->get('emailamender.second_level_domain_corrections');

  $options = _civicrm_api3_get_options_from_params($params);
  $topLevelDomainList = CRM_Utils_Type::escape(implode('|', array_filter(array_keys($topLevel))), 'String');
  $secondLevelDomainList = CRM_Utils_Type::escape(implode('|', array_filter(array_keys($secondLevel))), 'String');
  // Can't use api due to lack of regex support.
  // Also - I'm not hunting out subdomains at this stage - it's great that John handled
  // edge cases like gmai@gmai.gmai.com but it feels like it can stay out of scope of this
  // api at this stage.
  $values = CRM_Core_DAO::executeQuery("
    SELECT id, contact_id, email FROM civicrm_email 
    WHERE email REGEXP '\.({$topLevelDomainList})$'
    OR email REGEXP '@({$secondLevelDomainList})\\\.'
    LIMIT " . $options['limit']
  );
  $return = [];
  while ($values->fetch()) {
    $return[$values->id] = ['email' => $values->email, 'id' => $values->id, 'contact_id' => $values->contact_id];
  }

  return civicrm_api3_create_success($return);
}

