<?php

use CRM_Emailamender_ExtensionUtil as E;

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 1/10/18
 * Time: 4:30 PM
 */
return [
  'emailamender.email_amender_enabled' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'emailamender.email_amender_enabled',
    'type' => 'Bool',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => FALSE,
    'description' => E::ts('Enable realtime email fixing for new emails'),
    'title' => 'Automatic Email amending enabled',
    'help_text' => E::ts('Enable this to allow new emails to be amended on save according to configured rules. Existing updates not amended'),
    'html_type' => 'Checkbox',
    'quick_form_type' => '',
  ],
  'emailamender.top_level_domain_corrections' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'emailamender.top_level_domain_corrections',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of top level domains to correct'),
    'title' => E::ts('Top level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
    'serialize' => CRM_Core_DAO::SERIALIZE_PHP,
    'default' => [
      'con'  => 'com',
      'couk' => 'co.uk',
      'cpm'  => 'com',
      'orguk'  => 'org.uk',
    ],
  ],
  'emailamender.second_level_domain_corrections' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'emailamender.second_level_domain_corrections',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of second level domains to correct'),
    'title' => E::ts('Second level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
    'serialize' => CRM_Core_DAO::SERIALIZE_PHP,
    'default' => [
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
    ],
  ],
  'emailamender.compound_top_level_domains' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'emailamender.compound_top_level_domains',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of compound top level domains'),
    'title' => E::ts('Compound top level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
    'serialize' => CRM_Core_DAO::SERIALIZE_PHP,
    'default' => [
      '.ac.uk',
      '.co.uk',
      '.org.uk',
    ],
  ],
  'emailamender.equivalent_domains' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'equivalent_domains',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of equivalent domains'),
    'title' => E::ts('Equivalent domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
    'serialize' => CRM_Core_DAO::SERIALIZE_PHP,
    'default' => [
      'gmail.com'        => 'GMail',
      'googlemail.com'   => 'GMail',
      'gmail.co.uk'      => 'GMail UK',
      'googlemail.co.uk' => 'GMail UK',
    ],
  ],
];