<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'gfcivicrmcompanion.civix.php';
// phpcs:enable

use CRM_Gfcivicrmcompanion_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function gfcivicrmcompanion_civicrm_config(\CRM_Core_Config $config): void {
  _gfcivicrmcompanion_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function gfcivicrmcompanion_civicrm_install(): void {
  _gfcivicrmcompanion_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function gfcivicrmcompanion_civicrm_enable(): void {
  _gfcivicrmcompanion_civix_civicrm_enable();
}

/**
 * APIv3: Allow 'access AJAX API' to reach the kernel for specific actions.
 * 
 * Opens the door so CiviCRM does not immediately reject the call.
 */
function gfcivicrmcompanion_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (!isset($params['_gf_sig']) || !isset($params['_gf_ts'])) {
    return;
  }

  $allowed_actions = [ 'get', 'getfields', 'getcount', 'getsingle', 'validate', 'export' ];
  $custom_permission = 'access AJAX API';
  
  // First modify entities for an OR permission check (e.e. 'administer CiviCRM' OR 'access AJAX API')
  $entities = [
    'setting', 
    'contact_checksum', 
    'contact', 
    'group', 
    'option_group', 
    'country', 
    'saved_search', 
    'form_processor', 
    'form_processor_instance', 
    'form_processor_defaults', 
    'payment_processor', 
    'payment_token',
  ]; // Add entities used by GF CiviCRM

  if (in_array($entity, $entities) && in_array($action, $allowed_actions)) {
    $existing = $permissions[$entity][$action] ?: ['administer CiviCRM']; // Default to 'administer CiviCRM' if missing

    // Ensure $existing is an array (sometimes it's just a string)
    $existing = (array) $existing; 

    // Add the permission to the required list
    if (!in_array($custom_permission, $existing)) {
        $existing[] = $custom_permission;
    }

    // Is an OR check
    $permissions[$entity][$action] = [$existing];
  }
}

/**
 * DEVNOTE: CMRF (Wordpress Connector to CiviCRM with CiviMcRestFace) currently doesn't support apiv4.
 * This is UNTESTED at this stage.
 * 
 * APIv4: Allow 'access AJAX API' to reach the kernel.
 * 
 * Opens the door so CiviCRM does not immediately reject the call.
 */
/*function gfcivicrmcompanion_civicrm_alterApiRoutePermissions(&$permissions, $entity, $action) {
  $entities = ['Setting', 'ContactChecksum', 'Contact', 'Group', 'OptionGroup', 'Country', 'SavedSearch', 'FormProcessor', 'FormProcessorInstance', 'FormProcessorDefaults', 'PaymentProcessor', 'PaymentToken'];
  if (in_array($entity, $entities)) {
    $customPerm = 'access AJAX API';

    $existing = $permissions[$entity][$action] ?? ['administer CiviCRM'];

    // Ensure $existing is an array (sometimes it's just a string)
    $existing = (array) $existing; 

    // Add the permission to the required list
    if (!in_array($customPerm, $existing)) {
        $existing[] = $customPerm;
    }

    // Is an OR check
    $permissions[$entity][$action] = [$existing];
  }
}*/

/**
 * Implements hook_civicrm_apiWrappers().
 * 
 * Responsible for validating the call comes from gf-civicrm. If not, it will reject the call.
 */
function gfcivicrmcompanion_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if (!interface_exists('API_Wrapper')) return;

  $wrappers[] = new CRM_Gfcivicrmcompanion_APIWrapper();
}