<?php

class CRM_Gfcivicrmcompanion_APIWrapper implements API_Wrapper {
  
  public function fromApiInput($apiRequest) {
    $params = $this->getParams($apiRequest);
    
    $entity = is_object($apiRequest) ? $apiRequest->getEntityName() : $apiRequest['entity'];
    $action = is_object($apiRequest) ? $apiRequest->getActionName() : $apiRequest['action'];
    
    $signature = $params['_gf_sig'] ?? null;
    $timestamp = $params['_gf_ts'] ?? null;

    if (!$signature || !$timestamp) {
      return $apiRequest;
    }

    if ($this->validateSignature($entity, $action, $signature, $timestamp)) {
      if (CRM_Core_Permission::check('access AJAX API')) {
        $this->disablePermissionCheck($apiRequest);
      } else {
        // This handles 'SavedSearch' -> 'saved_search' and 'PaymentToken' -> 'payment_token'
        $entityKey = \CRM_Utils_String::convertStringToSnakeCase($entity);
        $actionKey = strtolower($action); // Actions are just lowercase
        
        $allPermissions = \CRM_Core_Permission::getEntityActionPermissions();
    
        // Check for the snake_case version, falling back to the original if not found
        $permissions = $allPermissions[$entityKey][$actionKey] 
                        ?? $allPermissions[$entity][$actionKey];

        if (!CRM_Core_Permission::check($permissions)) {
          throw new \Civi\API\Exception\UnauthorizedException(ts('Insufficient permission to access this API resource.'));
        }
      }
    } else {
      throw new \CRM_Core_Exception('GF-CiviCRM: Invalid security signature.');
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