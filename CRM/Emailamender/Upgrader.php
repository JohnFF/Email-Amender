<?php

/**
 * Collection of upgrade steps
 */
class CRM_Emailamender_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Create the default equivalent domain settings if the setting does not
   * already exist.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');

    // Check if the setting is already present
    $aDomainEquivalents = Civi::settings()->get('emailamender.equivalent_domains');
    if ($aDomainEquivalents) {
      return TRUE;
    }

    // Create some defaults
    $aDomainEquivalents = array(
      'gmail.com'        => 'GMail',
      'googlemail.com'   => 'GMail',
      'gmail.co.uk'      => 'GMail UK',
      'googlemail.co.uk' => 'GMail UK',
    );
    Civi::settings()->set('equivalent_domains', $aDomainEquivalents);

    // Check if the setting is now present (as setItem returns void)
    $aDomainEquivalents = Civi::settings()->get('equivalent_domains');
    if ($aDomainEquivalents) {
      return TRUE;
    }

    throw new Exception('Could not create the equivalent domains setting');
  }

}
