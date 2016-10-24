<?php

class CRM_Emailamender {
  /**
   * Do the main processing of the domain part of the email address
   *
   * 
   */
  static function emailamender_performreplacement( &$sDomainFragment, &$aCorrections ){
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
  static function emailamender_get_second_domain_part_index( $sDomainPart, &$aCompoundTopLevelDomains ){
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
  static function emailamender_parse_email_byref($sRawEmail, &$aEmailPieces, &$aDomainPartPieces) {
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
  static function emailamender_reassemble_email($aEmailPieces, $aDomainPartPieces) {
    $aDomainPartPieces = array_reverse( $aDomainPartPieces );
    $aEmailPieces[1] = implode('.', $aDomainPartPieces);
    $sCleanedEmail = CRM_Core_DAO::escapeString(implode('@', $aEmailPieces));
    return $sCleanedEmail;
  }
  
  static function check_for_corrections($iEmailId, $iContactId, $sRawEmail) {
    
    if ( 'false' == CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'emailamender.email_amender_enabled' ) ){
      return;
    }
    
    // 4. check that it has only one '@' - shouldn't need to do this but just in case
    if (substr_count($sRawEmail, "@") != 1){
      return;
    }

    // 5. explode the string into the local part and the domain part
    emailamender_parse_email_byref($sRawEmail, $aEmailPieces, $aDomainPartPieces);

    // 6. load settings and init
    $aTopLevelFilterSettings    = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'emailamender.top_level_domain_corrections' );
    $aSecondLevelFilterSettings = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'emailamender.second_level_domain_corrections' );
    $aCompoundTopLevelDomains   = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'emailamender.compound_top_level_domains' );
    $iSecondLevelDomainFragmentIndex = self::emailamender_get_second_domain_part_index($aEmailPieces[1], $aCompoundTopLevelDomains);

    // 7. break it up and process it
    $bTopLevelChanged = self::emailamender_performreplacement( $aDomainPartPieces[0], $aTopLevelFilterSettings );
    $bSecondLevelChanged = self::emailamender_performreplacement( $aDomainPartPieces[$iSecondLevelDomainFragmentIndex], $aSecondLevelFilterSettings);

    // 8. bail if nothing changed
    if ( !($bTopLevelChanged || $bSecondLevelChanged) ){
      return; 
    }

    // 9. recreate the fixed email address
    $sCleanedEmail = self::emailamender_reassemble_email($aEmailPieces, $aDomainPartPieces);

    // 10. update the email address
    $updateParam = array(
      "version" => 3,
      "id" => $iEmailId,
      "email" => $sCleanedEmail
    );

    civicrm_api("Email", "update", $updateParam);

    // 11. record everything
    $iActivityTypeId = CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'emailamender.email_amended_activity_type_id' );
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
}
