<?php

/**
 * EmailAmender.BatchUpdate API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_email_amender_batch_update_spec(&$spec) {
}

/**
 * EmailAmender.BatchUpdate API specification
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 */
function civicrm_api3_email_amender_batch_update($params) {
  $candidates = civicrm_api3('EmailAmender', 'find_candidates', $params)['values'];
  $result = [];
  foreach ($candidates as $candidate) {
    $result += civicrm_api3('EmailAmender', 'fix_email', $candidate)['values'];
  }
  return civicrm_api3_create_success($result, $params);
}
