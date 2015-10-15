<?php

require_once '../init.php';

// Set default user for entities creation
global $current_user;
if(is_null($current_user)) { $current_user = (object) array(); }
if(!isset($current_user->id)) {
  $current_user->id = '1';
  $current_user->date_format = 'Y-m-d';
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
    case "TAXCODES":
      $taxMapper = new TaxMapper();
      $taxMapper->fetchConnecResource($entity_id);
      break;
    case "ACCOUNTS":
      $accountMapper = new AccountMapper();
      $accountMapper->fetchConnecResource($entity_id);
      break;
    case "ORGANIZATIONS":
      $organizationMapper = new CustomerOrganizationMapper();
      $organizationMapper->fetchConnecResource($entity_id);
      $organizationMapper = new SupplierOrganizationMapper();
      $organizationMapper->fetchConnecResource($entity_id);
      break;
    case "PERSONS":
      $contactMapper = new ContactMapper();
      $contactMapper->fetchConnecResource($entity_id);
      $leadMapper = new LeadMapper();
      $leadMapper->fetchConnecResource($entity_id);
      break;
    case "ITEMS":
      $productMapper = new ProductMapper();
      $productMapper->fetchConnecResource($entity_id);
      $serviceMapper = new ServiceMapper();
      $serviceMapper->fetchConnecResource($entity_id);
      break;
    case "INVOICES":
      $invoiceMapper = new InvoiceMapper();
      $invoiceMapper->fetchConnecResource($entity_id);
      break;
    case "QUOTES":
      $quoteMapper = new QuoteMapper();
      $quoteMapper->fetchConnecResource($entity_id);
      break;
    case "PURCHASEORDERS":
      $purchaseOrderMapper = new PurchaseOrderMapper();
      $purchaseOrderMapper->fetchConnecResource($entity_id);
      break;
    case "SALESORDERS":
      $salesOrderMapper = new SalesOrderMapper();
      $salesOrderMapper->fetchConnecResource($entity_id);
      break;
    case "EVENTS":
      $eventMapper = new EventMapper();
      $eventMapper->fetchConnecResource($entity_id);
      break;
    case "EVENTORDERS":
      $eventOrderMapper = new EventOrderMapper();
      $eventOrderMapper->fetchConnecResource($entity_id);
      break;
    case "OPPORTUNITIES":
      $opportunityMapper = new OpportunityMapper();
      $opportunityMapper->fetchConnecResource($entity_id);
      break;
    case "APPUSERS":
      $userMapper = new UserMapper();
      $userMapper->fetchConnecResource($entity_id);
      break;
    case "TEAMS":
      $teamMapper = new TeamMapper();
      $teamMapper->fetchConnecResource($entity_id);
      break;
  }
} catch (Exception $e) {
  error_log("Caught exception in subscribe " . json_encode($e->getMessage()));
}
