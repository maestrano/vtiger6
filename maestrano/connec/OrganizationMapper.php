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
    if($this->is_set($organization_hash['name'])) { $organization->column_fields['accountname'] = $organization_hash['name']; }
    if($this->is_set($organization_hash['description'])) { $organization->column_fields['description'] = $organization_hash['description']; }
    if($this->is_set($organization_hash['industry'])) { $organization->column_fields['industry'] = $organization_hash['industry']; }
    if($this->is_set($organization_hash['annual_revenue'])) { $organization->column_fields['annualrevenue'] = $organization_hash['annual_revenue']; }
    if($this->is_set($organization_hash['reference'])) { $organization->column_fields['siccode'] = $organization_hash['reference']; }

    if($this->is_set($organization_hash['reference'])) {
      
    }
  }

  // Map the vTiger Organization to a Connec resource hash
  protected function mapModelToConnecResource($organization) {
    $organization_hash = array();

    // Save as Customer
    $organization_hash['is_customer'] = true;

    // Map attributes
    if($this->is_set($organization->column_fields['accountname'])) { $organization_hash['name'] = $organization->column_fields['accountname']; }
    if($this->is_set($organization->column_fields['description'])) { $organization_hash['description'] = $organization->column_fields['description']; }
    if($this->is_set($organization->column_fields['industry'])) { $organization_hash['industry'] = $organization->column_fields['industry']; }
    if($this->is_set($organization->column_fields['annualrevenue'])) { $organization_hash['annual_revenue'] = $organization->column_fields['annualrevenue']; }
    if($this->is_set($organization->column_fields['siccode'])) { $organization_hash['reference'] = $organization->column_fields['siccode']; }
    if($this->is_set($organization->column_fields['employees'])) { $organization_hash['number_of_employees'] = $organization->column_fields['employees']; }

    return $organization_hash;
  }

  // Persist the vTiger Organization
  protected function persistLocalModel($organization, $resource_hash) {
    $organization->save("Accounts", '', false);
  }
}



// 'capital' => { field: 'capital', mapper: 'BigDecimalToNumber' },
// 'number_of_employees' => 'number_of_employees',

// # Party type
// 'party_type.customer' => 'is_customer',
// 'party_type.supplier' => 'is_supplier',
// 'party_type.lead' => 'is_lead',

// # AddressGroup transformation
// 'address' => 'address',

// # EmailGroup transformation
// 'email' => 'email',

// # WebsiteGroup transformation
// 'website' => 'website',

// # TelephoneGroup transformation
// 'telephone' => 'phone'