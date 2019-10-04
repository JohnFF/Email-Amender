<?php

class CRM_Emailamender {

  /**
   * Singleton instance.
   *
   * @var \CRM_Emailamender
   */
  private static $singleton;
  private $_aTopLevelFilterSettings;
  private $_aSecondLevelFilterSettings;
  private $_aCompoundTopLevelDomains;

  public function __construct() {
    $this->_aTopLevelFilterSettings = (array) Civi::settings()->get('emailamender.top_level_domain_corrections');
    $this->_aSecondLevelFilterSettings = (array) Civi::settings()->get('emailamender.second_level_domain_corrections');
    $this->_aCompoundTopLevelDomains = (array) Civi::settings()->get('emailamender.compound_top_level_domains');
  }

  /**
   * Do the main processing of the domain part of the email address
   * @array string $sDomainFragment e.g. gmai
   * @array array $aCorrections list of corrections
   * @return bool replaced or not
   */
  private static function perform_replacement(&$sDomainFragment, $aCorrections) {
    if (array_key_exists($sDomainFragment, $aCorrections)) {
      $sDomainFragment = $aCorrections[$sDomainFragment];
      return TRUE;
    }

    return FALSE;
  }

  /**
   *  For instances like john@gmai.co.uk where the last two parts are treated like one part of the URL.
   *  We want to correct 'gmai' not 'co'
   * @param string $sDomainPart (e.g. gmai.co.uk)
   * @return index from end (either 1 or 2).
   */
  private function get_second_domain_part_index($sDomainPart) {
    foreach ($this->_aCompoundTopLevelDomains as $sCompoundTld) {
      if (substr($sDomainPart, -strlen($sCompoundTld)) == $sCompoundTld) {
        return 2;
      }
    }

    return 1;
  }

  /**
   * Parse a raw email address
   * Sets variables by reference with the pieces.
   */
  private static function parse_email_byref($sRawEmail, &$aEmailPieces, &$aDomainPartPieces) {
    // Explode the string into the local part and the domain part
    $aEmailPieces = explode('@', $sRawEmail);

    // Break up the domain part
    // - this is done in reverse order to make processing far easier than
    // - attempting to ignore subdomains in an instance like gmai.hotmai.ibm.com
    $aDomainPartPieces = explode('.', $aEmailPieces[1]);
    $aDomainPartPieces = array_reverse($aDomainPartPieces);
  }

  /**
   * Reassembles an email address from the pieces
   * @param array $aEmailPieces
   * @param array $aDomainPartPieces
   * @return string $sCleanedEmail corrected email e.g. john@hotmail.com
   */
  private static function reassemble_email($aEmailPieces, $aDomainPartPieces) {
    $aDomainPartPieces = array_reverse($aDomainPartPieces);
    $aEmailPieces[1] = implode('.', $aDomainPartPieces);
    $sCleanedEmail = CRM_Core_DAO::escapeString(implode('@', $aEmailPieces));
    return $sCleanedEmail;
  }

  /**
   * Returns if the setting to auto correct email addresses is enabled.
   * @return bool
   */
  public function is_autocorrect_enabled() {
    return Civi::settings()->get('emailamender.email_amender_enabled');
  }

  /**
   * Check and perform corrections.
   *
   * @param int $iEmailId
   * @param int $iContactId
   * @param string $sRawEmail
   *
   * @return bool correction took place
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function fixEmailAddress($iEmailId, $iContactId, $sRawEmail) {

    // 1. Check that the email address has only one '@' - shouldn't need to do this but just in case.
    if (substr_count($sRawEmail, '@') !== 1) {
      return FALSE;
    }

    // 2. Explode the string into the local part and the domain part.
    self::parse_email_byref($sRawEmail, $aEmailPieces, $aDomainPartPieces);

    // 3. Load the settings and initialise.
    $iSecondLevelDomainFragmentIndex = $this->get_second_domain_part_index($aEmailPieces[1]);

    // 4. Break it up and process it.
    $bTopLevelChanged = self::perform_replacement($aDomainPartPieces[0], $this->_aTopLevelFilterSettings);
    $bSecondLevelChanged = self::perform_replacement($aDomainPartPieces[$iSecondLevelDomainFragmentIndex], $this->_aSecondLevelFilterSettings);

    // 5. Bail if nothing changed.
    if (!($bTopLevelChanged || $bSecondLevelChanged)) {
      return FALSE;
    }

    // 6. Recreate the fixed email address.
    $sCleanedEmail = self::reassemble_email($aEmailPieces, $aDomainPartPieces);

    // 7. Update the email address.
    $updateParam = [
      'id' => $iEmailId,
      'email' => $sCleanedEmail,
      // Take it off hold, taken from CRM_Core_BAO_Email.
      'on_hold' => FALSE,
      'hold_date' => NULL,
      'reset_date' => date('YmdHis'),
    ];

    try {
      civicrm_api3('Email', 'create', $updateParam);
      // Recalculate display name.
      civicrm_api3('Contact', 'create', ['id' => $iContactId]);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Session::setStatus(ts("Error when correcting email - contact ID $iContactId"), ts('Email Address Corrector'), 'error');
      throw $e;
    }

    // 8. Record the change by an activity.
    $createActivityOutput = civicrm_api('Activity', 'create', array(
      'version' => '3',
      'sequential' => '1',
      'activity_type_id' => 'corrected_email_address',
      'source_contact_id' => $iContactId,
      'target_contact_id' => $iContactId,
      'assignee_contact_id' => $iContactId,
      'subject' => "Amended Email from $sRawEmail to $sCleanedEmail",
      'details' => "Amended Email from $sRawEmail to $sCleanedEmail",
    ));

    if ($createActivityOutput['is_error']) {
      CRM_Core_Session::setStatus(ts("Error when creating activity  - contact ID $iContactId"), ts('Email Address Corrector'), 'error');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Get singleton instance.
   *
   * @return \CRM_Emailamender
   */
  public static function singleton() {
    if (self::$singleton) {
      return self::$singleton;
    }
    self::$singleton = new CRM_Emailamender();
    return self::$singleton;
  }

}
