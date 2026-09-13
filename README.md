# GF CiviCRM Companion (au.com.agileware.gfcivicrmcompanion)

CiviCRM extension developed by Agileware as a companion for the [GF CiviCRM WordPress plugin](https://github.com/agileware/gf-civicrm), which lets Gravity Forms on a remote WordPress site read and write CiviCRM data over the CiviCRM REST/AJAX API.

By default, CiviCRM's AJAX API endpoint is only reachable by logged-in CiviCRM users, which isn't available to a remote WordPress site. This extension solves that problem by:

* Allowing a small, fixed set of API entities and actions (see [Usage](#usage) below) to be granted to the *'access AJAX API'* permission, instead of requiring the much broader *'administer CiviCRM'* permission.
* Verifying that inbound API calls carrying this relaxed permission genuinely originate from the GF CiviCRM plugin, by checking an HMAC signature and timestamp sent with each request. Calls that fail this check fall back to CiviCRM's normal permission checks.

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [GPL-2.0](LICENSE.txt).

## Usage

This extension has no settings page, menu item, or other UI of its own — it works silently in the background once configured (see [Special Configuration Requirements](#special-configuration-requirements) below) to authorise and validate API requests made by the GF CiviCRM plugin.

* **Permission relaxation** — for the `get`, `getfields`, `getcount`, `getsingle`, `validate` and `export` actions on the `Setting`, `ContactChecksum`, `Contact`, `Group`, `OptionGroup`, `Country`, `SavedSearch`, `FormProcessor`, `FormProcessorInstance`, `FormProcessorDefaults`, `PaymentProcessor` and `PaymentToken` APIv3 entities, the extension adds *'access AJAX API'* as an alternative to whatever permission(s) CiviCRM already requires (i.e. the caller needs *'administer CiviCRM'* **or** *'access AJAX API'*, not both).
* **Signature validation** — every APIv3 call is inspected for `_gf_sig` (an HMAC-SHA256 signature) and `_gf_ts` (a timestamp) parameter, sent by the GF CiviCRM plugin. If both are present and valid (and no more than 5 minutes old, to prevent replay attacks), CiviCRM's permission checks are skipped for that call provided the caller already holds *'access AJAX API'*. If the signature is missing, invalid, or expired, the extension logs an error and falls back to CiviCRM's normal permission checks for the request.

There are no CiviRule actions, scheduled jobs, or API entities/actions of its own — this extension only alters how *existing* CiviCRM APIs authorise incoming requests.

### Known Issues

**Retrieval of PaymentTokens is not currently supported.** Recommend managing these on the CiviCRM site.

**APIv4 is not currently supported.** The code contains an untested, disabled implementation of the equivalent APIv4 permission hook (`hook_civicrm_alterApiRoutePermissions`); only APIv3 requests are handled at this time.

#### Connector to CiviCRM with CiviMcRestFace (CMRF) connection validation

CMRF connection profiles will attempt to validate the connection to CiviCRM using the API user's credentials. It does this using the Entity.get API call, which requires the 'administer CiviCRM' permission.

To validate the connection, give the API user the 'administer CiviCRM' permission. Then after enabling this extension, change to 'access AJAX API'.

## Special Configuration Requirements

This extension requires the use of a secure secret key for HMAC (Hash-based Message Authentication Code) Signing. This key must be added to both CiviCRM and the remote WordPress website. There is no CiviCRM admin UI for this — the key is configured directly in each site's configuration file.

1. Generate a secure secret key.
1. In `civicrm.settings.php`, add the setting `$civicrm_setting['external_integration']['gf_civicrm_secret'] = 'secure-secret-key';`
1. In `wp-config.php`, add the constant `define('GF_CIVICRM_SECRET', 'secure-secret-key');`
1. Configure the Remote API User's user role to have the *'access AJAX API'* permission.
1. Test remote connections using the pre-flight checks in Gravity Forms' CiviCRM REST Connection Profile settings.

Without this secret key configured identically on both ends, signature validation will always fail, and the API user account will need the broader *'administer CiviCRM'* permission (or the specific per-entity permissions CiviCRM already requires) to continue functioning.

## Requirements

* PHP v8.0+
* CiviCRM 6.9+
* The [GF CiviCRM WordPress plugin](https://github.com/agileware/gf-civicrm), installed on the remote WordPress site making the API calls

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin
Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

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
