<?php

require_once 'emailamender.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function emailamender_civicrm_config(&$config) {
  _emailamender_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function emailamender_civicrm_xmlMenu(&$files) {
  _emailamender_civix_civicrm_xmlMenu($files);
}

/*
 * create_activity_type_if_doesnt_exist
 * also updates the civicrm_setting entry
 * 
 */	
function emailamender_create_activity_type_if_doesnt_exist( $sActivityTypeLabel, $sActivityTypeDescription, $sSettingName ){

  $aActivityTypeCheck=civicrm_api("OptionValue","get", array ('version' => '3','sequential' =>'1', 'name' => $sActivityTypeLabel));

  if ($aActivityTypeCheck['count'] > 0){
  	print_r($aActivityTypeCheck, TRUE);
    CRM_Core_BAO_Setting::setItem(
      $aActivityTypeCheck['values'][0]['value'], 
      'uk.org.futurefirst.networks.emailamender', 
      $sSettingName
	  ); 	
	
  	return;
  }

  // create activity types
  $aEmailAmendedCreateResults = civicrm_api(
    "ActivityType",
    "create",
    array ('version' => '3',
      'sequential'   => '1', 
      'is_active'    => '1',
      'label'        => $sActivityTypeLabel, 
      'weight'       => '1', 
      'description'  => $sActivityTypeDescription,
    )
  );

  CRM_Core_BAO_Setting::setItem($aEmailAmendedCreateResults['values'][0]['value'], 'uk.org.futurefirst.networks.emailamender', $sSettingName); 	
}

/**
 * Implementation of hook_civicrm_install
 */
function emailamender_civicrm_install() {

  // initialise data
  $aTopLevelDomainCorrections = array(
    'con'  => 'com',
    'couk' => 'co.uk',
    'cpm'  => 'com',
    'orguk'  => 'org.uk',
  );

  $aSecondLevelDomainCorrections = array(
    'gmai'     => 'gmail',
    'gamil'    => 'gmail',
    'gmial'    => 'gmail',
    'hotmai'   => 'hotmail',
    'hotmal'   => 'hotmail',
    'hotmil'   => 'hotmail',
    'hotmial'  => 'hotmail',
    'htomail'  => 'hotmail',
    'tiscalli' => 'tiscali',
    'yaho'     => 'yahoo',
  );

  $aCompoundTopLevelDomains = array(
    '.ac.uk',
    '.co.uk',
    '.org.uk',
  );

  $aDomainEquivalents = array(
    'gmail.com'        => 'GMail',
    'googlemail.com'   => 'GMail',
    'gmail.co.uk'      => 'GMail UK',
    'googlemail.co.uk' => 'GMail UK',
  );

  CRM_Core_BAO_Setting::setItem($aTopLevelDomainCorrections, 'uk.org.futurefirst.networks.emailamender', 'top_level_domain_corrections');
  CRM_Core_BAO_Setting::setItem($aSecondLevelDomainCorrections, 'uk.org.futurefirst.networks.emailamender', 'second_level_domain_corrections');
  CRM_Core_BAO_Setting::setItem($aCompoundTopLevelDomains, 'uk.org.futurefirst.networks.emailamender', 'compound_top_level_domains');
  CRM_Core_BAO_Setting::setItem($aDomainEquivalents, 'uk.org.futurefirst.networks.emailamender', 'equivalent_domains');
  CRM_Core_BAO_Setting::setItem('false', 'uk.org.futurefirst.networks.emailamender', 'email_amender_enabled'); 

  // create activity types
  emailamender_create_activity_type_if_doesnt_exist( 'Amended Email', 'Automatically amended emails (by the Email Amender extension).', 'email_amended_activity_type_id' );

  return _emailamender_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function emailamender_civicrm_uninstall() {

  $dao = CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE group_name = 'uk.org.futurefirst.networks.emailamender'");

  return _emailamender_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function emailamender_civicrm_enable() {
  return _emailamender_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function emailamender_civicrm_disable() {
  return _emailamender_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function emailamender_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _emailamender_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function emailamender_civicrm_managed(&$entities) {
  return _emailamender_civix_civicrm_managed($entities);
}

/**
 * Do the main processing of the domain part of the email address
 *
 * 
 */
function emailamender_performreplacement( &$sDomainFragment, &$aCorrections ){
  if (array_key_exists($sDomainFragment, $aCorrections)){
    $sDomainFragment = $aCorrections[$sDomainFragment];
    return TRUE;
  }

  return FALSE;
}

/**
 *  For instances like john@gmai.co.uk where the last two parts are treated like one part of the URL. 
 *  We want to correct gmai not co 
 *
 */
function emailamender_get_second_domain_part_index( $sDomainPart, &$aCompoundTopLevelDomains ){
  foreach ($aCompoundTopLevelDomains as $sCompoundTld){
    if (substr( $sDomainPart, -strlen($sCompoundTld) ) == $sCompoundTld){
      RETURN 2;
    }
  }

  RETURN 1;
}

/**
 * Parse a raw email address
 * Sets variables by reference with the pieces.
 */
function emailamender_parse_email_byref($sRawEmail, &$aEmailPieces, &$aDomainPartPieces) {
  // Explode the string into the local part and the domain part
  $aEmailPieces = explode('@', $sRawEmail);

  // Break up the domain part
  // - this is done in reverse order to make processing far easier than 
  // - attempting to ignore subdomains in an instance like gmai.hotmai.ibm.com
  $aDomainPartPieces = explode('.', $aEmailPieces[1]);
  $aDomainPartPieces = array_reverse( $aDomainPartPieces );
}

/**
 * Reassembles an email address from the pieces
 */
function emailamender_reassemble_email($aEmailPieces, $aDomainPartPieces) {
  $aDomainPartPieces = array_reverse( $aDomainPartPieces );
  $aEmailPieces[1] = implode('.', $aDomainPartPieces);
  $sCleanedEmail = mysql_real_escape_string(implode('@', $aEmailPieces));
  return $sCleanedEmail;
}

/**
 * Implementation of hook_civicrm_post( $op, $objectName, $id, &$params )
 *
 * Amends the emails after creation according to the stored amender settings. 
 */
function emailamender_civicrm_post( $op, $objectName, $id, &$params ){
  
  // 1. ignore all operations other than adding an email address
  if ($objectName != "Email"){
    return;
  }

  if ($op != "create"){
    return;
  }

  // 2. check that email amending is enabled. If it's not, bail
  if ( 'false' == CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'email_amender_enabled' ) ){
  	return;
  }

  // 3. init vars
  $iEmailId    = $params->id;
  $iContactId  = $params->contact_id;
  $sRawEmail   = $params->email;

  // 4. check that it has only one '@' - shouldn't need to do this but just in case
  if (substr_count($sRawEmail, "@") != 1){
    return;
  }
 
  // 5. explode the string into the local part and the domain part
  emailamender_parse_email_byref($sRawEmail, $aEmailPieces, $aDomainPartPieces);

  // 6. load settings and init
  $aTopLevelFilterSettings    = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'top_level_domain_corrections' );
  $aSecondLevelFilterSettings = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'second_level_domain_corrections' );
  $aCompoundTopLevelDomains   = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'compound_top_level_domains' );
  $iSecondLevelDomainFragmentIndex = emailamender_get_second_domain_part_index($aEmailPieces[1], $aCompoundTopLevelDomains);

  // 7. break it up and process it
  $bTopLevelChanged = emailamender_performreplacement( $aDomainPartPieces[0], $aTopLevelFilterSettings );
  $bSecondLevelChanged = emailamender_performreplacement( $aDomainPartPieces[$iSecondLevelDomainFragmentIndex], $aSecondLevelFilterSettings);

  // 8. bail if nothing changed
  if ( !($bTopLevelChanged || $bSecondLevelChanged) ){
    return; 
  }

  // 9. recreate the fixed email address
  $sCleanedEmail = emailamender_reassemble_email($aEmailPieces, $aDomainPartPieces);

  // 10. update the email address
  $updateParam = array(
    "version" => 3,
    "id" => $iEmailId,
    "email" => $sCleanedEmail
  );
  
  civicrm_api("Email", "update", $updateParam);

  // 11. record everything
  $iActivityTypeId = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'email_amended_activity_type_id' );
  $results=civicrm_api("Activity", "create", array (
    'version' => '3', 
    'sequential' => '1', 
    'activity_type_id' => $iActivityTypeId, 
    'source_contact_id' => $iContactId, 
    'source_record_id' => $iContactId, 
    'target_contact_id' => $iContactId, 
    'assignee_contact_id' => $iContactId,
    'subject' => "Amended Email from $sRawEmail to $sCleanedEmail",
    'details' => "Amended Email from $sRawEmail to $sCleanedEmail",
  ));
}

/**
 * From the list of equivalent domain fragments, get the ones that
 * may apply to the address we've received.
 *
 * @param  string $sDomainPart        The domain for which we want equivalents
 * @param  array  $aDomainEquivalents Array mapping possible equivalents to groups
 * @return array  Possible equivalents for the supplied domain
 */
function emailamender_getequivalentsfor($sDomainPart, $aDomainEquivalents) {
  // Is the supplied domain listed as one that may have equivalents?
  if (!array_key_exists($sDomainPart, $aDomainEquivalents)) {
    return NULL;
  }

  // Get an identifier for the group it is part of
  $group = $aDomainEquivalents[$sDomainPart];

  // Get all the equivalents in that group
  return array_keys($aDomainEquivalents, $group);
}

/**
 * Check whether a contact exists with a given e-mail address,
 * and if so get their contact ID.
 *
 * @param  string $sEmailToTry A complete e-mail address to look up contacts for.
 * @return int    The contact ID with that address if one exists, else NULL.
 */
function emailamender_try_equivalent($sEmailToTry) {
  // This is copied from what CRM_Utils_Mail_Incoming::getContactID
  // does before calling the hook emailProcessorContact.
  $dao = CRM_Contact_BAO_Contact::matchContactOnEmail($sEmailToTry, 'Individual');
  $contactID = NULL;
  if ($dao) {
    $contactID = $dao->contact_id;
  }
  return $contactID;
}

/**
 * Implementation of hook_civicrm_emailProcessorContact( $email, $contactID, &$result )
 *
 * If the contact ID passed in is null and the e-mail address isn't,
 * try looking up equivalent email addresses to see if a contact
 * already exists with an equivalent of the supplied address.
 *
 * @param string $email     the email address
 * @param int    $contactID the contactID that matches this email address, IF it exists
 * @param array  $result    (reference) has two fields
 *                 contactID - the new (or same) contactID
 *                 action    - 3 possible values:
 *                   CRM_Utils_Mail_Incoming::EMAILPROCESSOR_CREATE_INDIVIDUAL - create a new contact record
 *                   CRM_Utils_Mail_Incoming::EMAILPROCESSOR_OVERRIDE          - use the new contactID
 *                   CRM_Utils_Mail_Incoming::EMAILPROCESSOR_IGNORE            - skip this email address
 */
function emailamender_civicrm_emailProcessorContact($email, $contactID, &$result) {
  // Check for already valid contact ID
  if ($contactID) {
    // Leave the default behaviour, which (unless another implementation of the hook has changed it)
    // is to use this already-known contact ID
    return;
  }

  // explode the string into the local part and the domain part
  $aEmailParts = explode('@', $email);

  // load settings and init
  $aDomainEquivalents = CRM_Core_BAO_Setting::getItem('uk.org.futurefirst.networks.emailamender', 'equivalent_domains');

  // Try equivalent e-mail domains, if there are any
  $aEquivalentsToTry = emailamender_getequivalentsfor($aEmailParts[1], $aDomainEquivalents);
  foreach ($aEquivalentsToTry as $sEquivalent) {
    // Replace the domain part with this possible equivalent
    $aEmailParts[1] = $sEquivalent;
    $sEmailToTry = implode('@', $aEmailParts);

    $contactID = emailamender_try_equivalent($sEmailToTry);
    if ($contactID) {
      // If we found one, stop looking
      CRM_Core_Error::debug_log_message("emailamender_civicrm_emailProcessorContact: Found equivalent e-mail address $sEmailToTry for $email, with contact ID $contactID");
      $result = array('contactID' => $contactID, 'action' => CRM_Utils_Mail_Incoming::EMAILPROCESSOR_OVERRIDE);
      return;
    }
  }

  // No existing contact ID with an equivalent e-mail address was found
  // Leave the default behaviour, which (unless another implementation of the hook has changed it)
  // is to create a new contact with this e-mail address
}

/**
 * civicrm_civicrm_navigationMenu
 * 
 * implementation of civicrm_civicrm_navigationMenu
 * 
 */
function emailamender_civicrm_navigationMenu( &$params ) {
  $sAdministerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  $sSystemSettingsMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'System Settings', 'id', 'name');
    
  //  Get the maximum key of $params
  $maxKey = ( max( array_keys($params) ) );	
	
  $params[$sAdministerMenuId]['child'][$sSystemSettingsMenuId]['child'][$maxKey +1] = array (
    'attributes' => array (
       'label'      => 'Email Amender Settings',
       'name'       => 'EmailAmenderSettings',
       'url'        => 'civicrm/emailamendersettings',
       'permission' => null,
       'operator'   => null,
       'separator'  => null,
       'parentID'   => $sSystemSettingsMenuId,
       'navID'      => $maxKey +1,
       'active'     => 1
    )
  );
}
