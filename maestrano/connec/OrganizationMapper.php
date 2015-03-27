<?php

/**
* Map Connec Organization representation to/from vTiger Organization
*/
class OrganizationMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Organization';
    $this->local_entity_name = 'Accounts';
    $this->connec_resource_name = 'organizations';
    $this->connec_resource_endpoint = 'organizations';
  }

  // Return the Organization local id
  protected function getId($organization) {
    return $organization->id;
  }

  // // Return a local Organization by id
  protected function loadModelById($local_id) {
    $organization = CRMEntity::getInstance("Accounts");
    $organization->retrieve_entity_info($local_id, "Accounts");
    vtlib_setup_modulevars("Accounts", $organization);
    $organization->id = $local_id;
    $organization->mode = 'edit';
    return $organization;
  }

  protected function validate($resource_hash) {
    // Process only Customers
    return $resource_hash['is_customer'];
  }

  // Map the Connec resource attributes onto the vTiger Organization
  protected function mapConnecResourceToModel($organization_hash, $organization) {
    // Map hash attributes to Organization
    if(!is_null($organization_hash['name'])) { $organization->column_fields['accountname'] = $organization_hash['name']; }
    if(!is_null($organization_hash['description'])) { $organization->column_fields['description'] = $organization_hash['description']; }
  }

  // Map the vTiger Organization to a Connec resource hash
  protected function mapModelToConnecResource($organization) {
    $organization_hash = array();

    // Map Organization to Connec hash
    $organization_hash['is_customer'] = true;

    if($this->is_set($organization->column_fields['accountname'])) { $organization_hash['name'] = $organization->column_fields['accountname']; }
    if($this->is_set($organization->column_fields['description'])) { $organization_hash['description'] = $organization->column_fields['description']; }

    return $organization_hash;
  }

  // Persist the vTiger Organization
  protected function persistLocalModel($organization, $resource_hash) {
    $organization->save("Accounts", '', false);
  }
}
