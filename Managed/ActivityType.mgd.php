<?php

use CRM_Emailamender_ExtensionUtil as E;

return [
  [
    'name' => 'ActivityType - corrected_email_address',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'match' => ['option_group_id', 'name'],
      'values' => [
        'label' => E::ts('Corrected Email Address'),
        'name' => 'corrected_email_address',
        'description'  => 'Automatically corrected emails (by the Email Address Corrector extension).',
        'option_group_id:name' => 'activity_type',
        'filter' => 1,
      ],
    ],
  ],
];
