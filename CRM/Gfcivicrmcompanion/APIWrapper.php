<?php

class CRM_Gfcivicrmcompanion_APIWrapper implements API_Wrapper {
  
  public function fromApiInput($apiRequest) {
    $params = $this->getParams($apiRequest);
    $entity = is_object($apiRequest) ? $apiRequest->getEntityName() : $apiRequest['entity'];
    $action = is_object($apiRequest) ? $apiRequest->getActionName() : $apiRequest['action'];
    
    $signature = $params['_gf_sig'] ?? null;
    $timestamp = $params['_gf_ts'] ?? null;

    // CRITICAL for APIv4: Clean up the parameters so they don't cause "Unknown parameter" errors
    unset($apiRequest['params']['_gf_sig']);
    unset($apiRequest['params']['_gf_ts']);
    if (isset($_POST['params'])) {
        $data = json_decode($_POST['params'], TRUE);
        if (isset($data['_gf_sig'])) {
            unset($data['_gf_sig'], $data['_gf_ts']);
            $_POST['params'] = json_encode($data);
            $_REQUEST['params'] = json_encode($data);
        }
    }

    if (!$signature || !$timestamp) {
      return $apiRequest;
    }

    $do_original_checks = false;

    // Validates API call comes from GF-CiviCRM. If not, fallback to normal permission checks
    if ($this->validateSignature($entity, $action, $signature, $timestamp)) {
      if (CRM_Core_Permission::check('access AJAX API')) {
        $this->disablePermissionCheck($apiRequest);
      } else {
        $do_original_checks = true;
      }
    } else {
      Civi::log()->error('GF-CiviCRM: Invalid security signature.');
      $do_original_checks = true;
    }

    // If we have an invalid signature, perform original permission checks, without the additional 
    // 'access AJAX API' custom permission
    if ($do_original_checks) {
      $entityKey = \CRM_Utils_String::convertStringToSnakeCase($entity);
      $actionKey = strtolower($action);
      
      $allPermissions = \CRM_Core_Permission::getEntityActionPermissions();
  
      // Check for the snake_case version, falling back to the original if not found
      $permissions = $allPermissions[$entityKey][$actionKey] 
                      ?? $allPermissions[$entity][$actionKey];

      if (!CRM_Core_Permission::check($permissions)) {
        throw new \Civi\API\Exception\UnauthorizedException(ts('Insufficient permission to access this API resource.'));
      }
    }
    
    return $apiRequest;
  }

  private function validateSignature($entity, $action, $receivedSig, $timestamp) {
    // Prevent replay attacks (5-minute window)
    if (abs(time() - $timestamp) > 300) return false;

    $secret = Civi::settings()->get('gf_civicrm_secret');

    if (!$secret) {
        throw new \CRM_Core_Exception("GF-CiviCRM Secret is not configured.");
    }

    $message = $entity . $action . $timestamp;

    $expectedSig = hash_hmac('sha256', $message, $secret);

    return hash_equals($expectedSig, $receivedSig);
  }

  /**
   * Universal setter to disable permission checks for v3 and v4
   */
  private function disablePermissionCheck(&$apiRequest) {
    if (is_object($apiRequest)) {
      // APIv4: The request is an Action object
      $apiRequest->setCheckPermissions(false);
    } else {
      // APIv3: The request is an array
      $apiRequest['params']['check_permissions'] = 0;
    }
  }

  private function getParams($apiRequest) {
    return is_object($apiRequest) ? $apiRequest->getParams() : $apiRequest['params'];
  }

  public function toApiOutput($apiRequest, $result) { return $result; }
}