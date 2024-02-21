<?php

use Civi\Api4\Domain;
use CRM_Emailamender_ExtensionUtil as E;

$menuItems = [];
$domains = Domain::get(FALSE)
  ->addSelect('id')
  ->execute();
foreach ($domains as $domain) {
  $menuItems[] = [
    'name' => 'EmailAmenderSettings',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Email Address Corrector Settings'),
        'name' => 'EmailAmenderSettings',
        'url' => 'civicrm/emailamendersettings',
        'permission' => NULL,
        'permission_operator' => 'OR',
        'parent_id.name' => 'System Settings',
        'is_active' => TRUE,
        'has_separator' => 2,
        'weight' => 15,
        'domain_id' => $domain['id'],
      ],
      'match' => ['domain_id', 'name'],
    ],
  ];
}
return $menuItems;
