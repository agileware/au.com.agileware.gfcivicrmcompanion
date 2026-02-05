# GF CiviCRM Companion (au.com.agileware.gfcivicrmcompanion)

CiviCRM extension developed by Agileware as a companion for the [GF CiviCRM WordPress plugin](https://github.com/agileware/gf-civicrm). Enables 'access AJAX API' as a permission for remote API calls made by the plugin.

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [GPL-2.0](LICENSE.txt).

## Connector to CiviCRM with CiviMcRestFace (CMRF) connection validation

CMRF connection profiles will attempt to validate the connection to CiviCRM using the API user's credentials. It does this using the Entity.get API call, which requires the 'administer CiviCRM' permission.

To validate the connection, give the API user the 'administer CiviCRM' permission. Then after enabling this extension, change to 'access AJAX API'.

## Usage

This extension requires the use of a secure secret key for HMAC (Hash-based Message Authentication Code) Signing. This key must be added to both CiviCRM and the remote WordPress website.

1. Generate a secure secret key.
1. In `civicrm.settings.php`, add the setting `$civicrm_setting['external_integration']['gf_civicrm_secret'] = 'secure-secret-key';`
1. In `wp-config.php`, add the constant `define('GF_CIVICRM_SECRET', 'secure-secret-key');`
1. Configure the Remote API User's user role to have the *'access AJAX API'* permission.
1. Test remote connections using the pre-flight checks in Gravity Forms' CiviCRM REST Connection Profile settings.

### Known Issues

**Retrieval of PaymentTokens is not currently supported.** Recommend managing these on the CiviCRM site.


# About the Authors

This CiviCRM extension was developed by the team at [Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact Agileware](https://agileware.com.au/contact) today!


![Agileware](logo/agileware-logo.png)
