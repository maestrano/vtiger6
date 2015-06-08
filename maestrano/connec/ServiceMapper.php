<?php

/**
* Map Connec Service representation to/from vTiger Service
*/
class ServiceMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Service';
    $this->local_entity_name = 'Services';
    $this->connec_resource_name = 'items';
    $this->connec_resource_endpoint = 'items';
  }

  // Return the Service local id
  protected function getId($service) {
    return $service->id;
  }

  // Return a local Service by id
  protected function loadModelById($local_id) {
    $service = CRMEntity::getInstance("Services");
    $service->retrieve_entity_info($local_id, "Services");
    vtlib_setup_modulevars("Services", $service);
    $service->id = $local_id;
    $service->mode = 'edit';
    return $service;
  }

  protected function validate($service_hash) {
    // Process only Services
    return $service_hash['type'] == 'SERVICE';
  }

  // Map the Connec resource attributes onto the vTiger Service
  protected function mapConnecResourceToModel($service_hash, $service) {
    // Map hash attributes to Service
    if(is_null($service->column_fields['discontinued'])) { $service->column_fields['discontinued'] = 1; }
    if($this->is_set($service_hash['code'])) { $service->column_fields['service_no'] = $service_hash['code']; }
    if($this->is_set($service_hash['name'])) { $service->column_fields['servicename'] = $service_hash['name']; }
    if($this->is_set($service_hash['description'])) { $service->column_fields['description'] = $service_hash['description']; }
    if($this->is_set($service_hash['reference'])) { $service->column_fields['servicecode'] = $service_hash['reference']; }
    if($this->is_set($service_hash['unit'])) { $service->column_fields['qty_per_unit'] = $service_hash['unit']; }
    if($this->is_set($service_hash['unit_type'])) { $service->column_fields['service_usageunit'] = $service_hash['unit_type']; }

    if($this->is_set($service_hash['sale_price']) && $this->is_set($service_hash['sale_price']['net_amount'])) {
      $service->column_fields['unit_price'] = $service_hash['sale_price']['net_amount'];
    }
  }

  // Map the vTiger Service to a Connec resource hash
  protected function mapModelToConnecResource($service) {
    $service_hash = array();

    // Default service type to PURCHASED on creation
    if($this->is_new($service)) { $service_hash['type'] = 'SERVICE'; }

    // Map attributes
    $service_hash['code'] = $service->column_fields['service_no'];
    $service_hash['name'] = $service->column_fields['servicename'];
    $service_hash['description'] = $service->column_fields['description'];
    $service_hash['reference'] = $service->column_fields['servicecode'];
    $service_hash['unit'] = $service->column_fields['qty_per_unit'];
    $service_hash['unit_type'] = $service->column_fields['service_usageunit'];

    $service_hash['sale_price'] = array('net_amount' => $service->column_fields['unit_price']);

    ProductMapper::mapTaxToConnecResource($service, $service_hash);
    ProductMapper::mapAccountToConnecResource($service, $service_hash);

    return $service_hash;
  }

  // Persist the vTiger Service
  protected function persistLocalModel($service, $service_hash) {
    ProductMapper::mapConnecAccountToProduct($service_hash, $service);

    $service->save("Services", $service->id, false);

    // Force service code on creation
    if($this->is_new($service) && $this->is_set($service_hash['code'])) {
      global $adb;
      $adb->pquery("UPDATE vtiger_service SET service_no = ? WHERE serviceid = ?", array($service_hash['code'], $service->id));
    }

    // Add tax to product
    ProductMapper::mapConnecTaxToProduct($service_hash['sale_tax_code_id'], $service->id);
  }

  // Find or create a default service
  // This is used by Invoices lines not referring to any Item or Service
  public function defaultService() {
    global $adb;
    $default_service_no = 'CONNEC_DEFAULT';

    $result = $adb->pquery("SELECT * FROM vtiger_service WHERE service_no=?", array($default_service_no));
    if($result->_numOfRows > 0) {
      return $result->fields;
    } else {
      $service_hash = array('code' => $default_service_no, 'name' => 'Comment', 'description' => 'Default invoice line entry', 'type' => 'SERVICE');
      $this->saveConnecResource($service_hash);

      $result = $adb->pquery("SELECT * FROM vtiger_service WHERE service_no=?", array($default_service_no));
      return $result->fields;
    }
  }
}
