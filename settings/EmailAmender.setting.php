<?php

use CRM_Emailamender_ExtensionUtil as E;

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 1/10/18
 * Time: 4:30 PM
 */
return [
  'email_amender_enabled' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'email_amender_enabled',
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
  'top_level_domain_corrections' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'top_level_domain_corrections',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of top level domains to correct'),
    'title' => E::ts('Top level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
  ],
  'second_level_domain_corrections' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'second_level_domain_corrections',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of second level domains to correct'),
    'title' => E::ts('Second level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
  ],
  'compound_top_level_domains' => [
    'group_name' => 'Email Amender',
    'group' => 'email_amender',
    'name' => 'compound_top_level_domains',
    'type' => 'String',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('List of compound top level domains'),
    'title' => E::ts('Compound top level domains'),
    'help_text' => '',
    'html_type' => '',
    'quick_form_type' => '',
  ],
  'equivalent_domains' => [
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
  ],
];
