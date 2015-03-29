<?php

require_once '../init.php';

// Set default user for entities creation
global $current_user;
if(is_null($current_user)) { $current_user = array(); }
if(!isset($current_user->id)) {
  $current_user->id = "1";
}

try {
  if(!Maestrano::param('connec.enabled')) { return false; }

  $client = new Maestrano_Connec_Client();

  $notification = json_decode(file_get_contents('php://input'), false);
  $entity_name = strtoupper(trim($notification->entity));
  $entity_id = $notification->id;

  error_log("Received notification = ". json_encode($notification));

  switch ($entity_name) {
    case "COMPANYS":
      $companyMapper = new CompanyMapper();
      $companyMapper->fetchConnecResource($entity_id);
      break;
    case "ORGANIZATIONS":
      $organizationMapper = new OrganizationMapper();
      $organizationMapper->fetchConnecResource($entity_id);
      break;
  }
} catch (Exception $e) {
  error_log("Caught exception in subscribe " . json_encode($e->getMessage()));
}

?>
