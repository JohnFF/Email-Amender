<?php

require_once 'CRM/Core/Page.php';

class CRM_Emailamender_Page_EmailAmenderSettings extends CRM_Core_Page {
	
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Email Amender Settings'));

    $this->assign('email_amender_enabled', CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'email_amender_enabled'));

    $this->assign('top_level_filter_settings', CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'top_level_domain_corrections'));
    $this->assign('second_level_filter_settings', CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'second_level_domain_corrections'));
    $this->assign('compound_top_level_domains', CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'compound_top_level_domains'));
        
    $this->assign('equivalent_domain_settings', CRM_Core_BAO_Setting::getItem( 'uk.org.futurefirst.networks.emailamender', 'equivalent_domains'));

    parent::run();
  }
}
