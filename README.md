# DotRoll WHMCS domain registrar module

The DotRoll WHMCS domain registrar module is an open source plug-in that can be distributed free of charge. Focuses on integrating DotRoll as WHMCS domain registrar.
After the configuration, you can configure DotRoll as your default registrar and decide which services and TLDs to offer to your customers within the WHMCS administration area.

## Features

 - Supports registering, transferring and renewing domains
 - Updates (contact changes, nameserver changes, toggle lock)
 - Request EPP/Transfer/Auth code
 - Domain and Domain Transfer Sync support
 - DNSSEC support
 - Integrated DNS zone editor support
 - .HU domain registration policy (after 2025-03-19) https://www.domain.hu/domain-registration-policy/

## Minimum Requirements

 - WHMCS Version 7.7.1
 - PHP 7.2 (Tested version PHP 7.2, PHP 7.3 and 8.1)
 - PHP IDN Functions

## Pre requirements

 - Access to WHMCS admin area
 - DotRoll account with API access enabled on the desired environment

NOTE: DotRoll has a production as well as a test server environment. The test server environment is called Tryout. We urge you to test the WHMCS Registrar plug-in in our tryout environment, before pointing it to production. For more detailed information, please contact us at sales@dotroll.com.

## Installation

1. Download the module
2. Upload to  `<WHMCS directory>`
3. Navigate to Setup -> Products/Services -> Domain Registrars and activate DotRoll.
4. Click on Save
5. Navigate to Setup -> Products/Services -> Domain Pricing and select DotRoll as registrar for every TLD
6. Insert the following lines into the resources/domains/additionalfields.php file
   
   if (is_file(ROOTDIR . '/modules/registrars/dotroll/additionaldomainfields.php')) {
     include(ROOTDIR . '/modules/registrars/dotroll/additionaldomainfields.php');
   }

## Useful Resources

* [API Documentation](https://admin.dotroll.com/modules/addons/api/api.pdf)

## Associated repository

- [DotRoll WHMCS related tools](https://github.com/dotroll/dotroll-whmcs-tools)

[DotRoll Kft.](https://dotroll.com)
