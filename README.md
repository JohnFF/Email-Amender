# Email Address Corrector
A CiviCRM extension that automatically corrects email addresses as they're added according to common misspellings.
Includes a handy interface to add new correction rules, as well as support to correct spellings in current email addresses.
This can increase the reach of your mass mailings and can prevent your organisation's mailings from being flagged as spam by email providers.

## Features

### 1. Automatic correction of new email addresses.
* Offers automatic correction of new email addresses according to common misspellings.
* Common rules are inclued by default, but you can add, update, and delete these rules.
* Automatic correction of email addresses is disabled by default, to give you a chance to review the rules before enabling it.
* These settings are available under Administrator -> System Settings -> Email Address Corrector Settings.

### 2. Mass correction of existing email addresses.
* This extension also offers mass updating of existing email through the "Email - correct email addresses" advanced search task.
* Uses the correction rules set under Administrator -> System Settings -> Email Address Corrector Settings.

### 3. Records what was corrected
* A new Activity "Corrected Email Address" is added to a contact when its email address is corrected.

### 4. Domain equivalents.
* Normally, if a contact emails you from a googlemail.com address, and they are already in your CRM under the gmail.com address equivalent, a new contact will be created for them.
* This extension allows you to record domain equivalents to prevent a new contact from being created.
