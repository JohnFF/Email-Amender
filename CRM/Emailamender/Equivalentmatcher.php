<?php

/**
 * Class to hold the Equivalent domain matching functionality.
 *
 * @author John Kirk (http://github.com/JohnFF)
 * @author David Knoll (http://github.com/davidknoll)
 */
class CRM_Emailamender_Equivalentmatcher {
  /**
   * From the list of equivalent domain fragments, get the ones that
   * may apply to the address we've received.
   *
   * @param string $sDomainPart        The domain for which we want equivalents
   * @param array $aDomainEquivalents Array mapping possible equivalents to groups
   * @return array Possible equivalents for the supplied domain
   */
  private static function getEquivalentDomainsFor($sDomainPart, $aDomainEquivalents) {
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
  private static function tryEquivalentDomain($sEmailToTry) {
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
   * If the contact ID passed in is null and the e-mail address isn't,
   * try looking up equivalent email addresses to see if a contact
   * already exists with an equivalent of the supplied address.
   *
   * Note that a situation should never arise where we end up here but the
   * contact exists with the original given email address.
   *
   * @param string $email
   * @param int $contactID
   * @param array $result (two values, contactID and action).
   */
  public static function processHook($email, $contactID, &$result) {
    // Check for already valid contact ID
    if ($contactID) {
      // Leave the default behaviour, which (unless another implementation of the hook has changed it)
      // is to use this already-known contact ID
      return;
    }

    // explode the string into the local part and the domain part
    $aEmailParts = explode('@', $email);

    // load settings and init
    $aDomainEquivalents = Civi::settings()->get('emailamender.equivalent_domains');

    // Try equivalent e-mail domains, if there are any
    $aEquivalentDomainsToTry = self::getEquivalentDomainsFor($aEmailParts[1], $aDomainEquivalents);

    if (empty($aEquivalentDomainsToTry)) {
      return;
    }

    foreach ($aEquivalentDomainsToTry as $sEquivalentDomain) {
      // Replace the domain part with this possible equivalent
      $aEmailParts[1] = $sEquivalentDomain;
      $sEmailToTry = implode('@', $aEmailParts);

      $contactID = self::tryEquivalentDomain($sEmailToTry);
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
}
