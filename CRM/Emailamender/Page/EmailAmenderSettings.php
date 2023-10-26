<?php


class CRM_Emailamender_Page_EmailAmenderSettings extends CRM_Core_Page {

  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Email Amender Settings'));

    $this->assign('email_amender_enabled', Civi::settings()->get('emailamender.email_amender_enabled'));

    $this->assign('top_level_filter_settings', Civi::settings()->get('emailamender.top_level_domain_corrections'));
    $this->assign('second_level_filter_settings', Civi::settings()->get('emailamender.second_level_domain_corrections'));
    $this->assign('compound_top_level_domains', Civi::settings()->get('emailamender.compound_top_level_domains'));

    $this->assign('equivalent_domain_settings', Civi::settings()->get('emailamender.equivalent_domains'));
    $this->assign('hasEditPermission', CRM_Core_Permission::check('administer_email_amender'));
    $this->assign('hasEnablePermission', CRM_Core_Permission::check('administer CiviCRM'));
    parent::run();
  }
}
