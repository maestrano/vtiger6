<?php

/**
* Map Connec Supplier Organization representation to/from vTiger Vendor
*/
class SupplierOrganizationMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Organization';
    $this->local_entity_name = 'Vendors';
    $this->connec_resource_name = 'organizations';
    $this->connec_resource_endpoint = 'organizations';
  }

  // Return the Organization local id
  protected function getId($organization) {
    return $organization->id;
  }

  // Return a local Organization by id
  protected function loadModelById($local_id) {
    $organization = CRMEntity::getInstance("Vendors");
    $organization->retrieve_entity_info($local_id, "Vendors");
    vtlib_setup_modulevars("Vendors", $organization);
    $organization->id = $local_id;
    $organization->mode = 'edit';
    return $organization;
  }

  protected function validate($resource_hash) {
    // Process only Suppliers
    return $resource_hash['is_supplier'];
  }

  // Map the Connec resource attributes onto the vTiger Organization
  protected function mapConnecResourceToModel($organization_hash, $organization) {
    // Map hash attributes to Organization
    if(array_key_exists('code', $organization_hash)) { $organization->column_fields['vendor_no'] = $organization_hash['code']; }
    if(array_key_exists('name', $organization_hash)) { $organization->column_fields['vendorname'] = $organization_hash['name']; }
    if(array_key_exists('description', $organization_hash)) { $organization->column_fields['description'] = $organization_hash['description']; }
    if(array_key_exists('industry', $organization_hash)) { $organization->column_fields['category'] = $organization_hash['industry']; }

    // Map address with precedence given to billing
    $address = nil;
    if(array_key_exists('address', $organization_hash) && array_key_exists('billing', $organization_hash['address'])) { $address = $organization_hash['address']['billing']; }
    else if(array_key_exists('address', $organization_hash) && array_key_exists('shipping', $organization_hash['address'])) { $address = $organization_hash['address']['shipping']; }
    if($this->is_set($address)) {
      if(array_key_exists('line1', $address)) { $organization->column_fields['street'] = $address['line1']; }
      if(array_key_exists('line2', $address)) { $organization->column_fields['pobox'] = $address['line2']; }
      if(array_key_exists('city', $address)) { $organization->column_fields['city'] = $address['city']; }
      if(array_key_exists('region', $address)) { $organization->column_fields['state'] = $address['region']; }
      if(array_key_exists('postal_code', $address)) { $organization->column_fields['postalcode'] = $address['postal_code']; }
      if(array_key_exists('country', $address)) { $organization->column_fields['country'] = $address['country']; }
    }

    if(array_key_exists('landline', $organization_hash['phone'])) { $organization->column_fields['phone'] = $organization_hash['phone']['landline']; }
    if(array_key_exists('address', $organization_hash['email'])) { $organization->column_fields['email'] = $organization_hash['email']['address']; }
    if(array_key_exists('url', $organization_hash['website'])) { $organization->column_fields['website'] = $organization_hash['website']['url']; }
  }

  // Map the vTiger Organization to a Connec resource hash
  protected function mapModelToConnecResource($organization) {
    $organization_hash = array();

    // Save as Supplier
    $organization_hash['is_supplier'] = true;

    // Unset Customer flag when creating a new Vendor
    if($this->is_new($organization)) { $organization_hash['is_customer'] = false; }

    // Map attributes
    $organization_hash['code'] = $organization->column_fields['vendor_no'];
    $organization_hash['name'] = $organization->column_fields['vendorname'];
    $organization_hash['description'] = $organization->column_fields['description'];
    $organization_hash['industry'] = $organization->column_fields['category'];

    $address = array();
    $address['line1'] = $organization->column_fields['street'];
    $address['line2'] = $organization->column_fields['pobox'];
    $address['city'] = $organization->column_fields['city'];
    $address['region'] = $organization->column_fields['state'];
    $address['postal_code'] = $organization->column_fields['postalcode'];
    $address['country'] = $organization->column_fields['country'];
    if(!empty($address)) { $organization_hash['address'] = array('shipping' => $address, 'billing' => $address); }

    
    $organization_hash['phone'] = array('landline' => $organization->column_fields['phone']);
    $organization_hash['phone'] = array('address' => $organization->column_fields['email1']);
    $organization_hash['website'] = array('url' => $organization->column_fields['website']);

    return $organization_hash;
  }

  // Persist the vTiger Organization
  protected function persistLocalModel($organization, $resource_hash) {
    $organization->save("Vendors", $organization->id, false);

    // Force Organization code on creation
    if($this->is_new($organization) && $this->is_set($resource_hash['code'])) {
      global $adb;
      $adb->pquery("UPDATE vtiger_vendor SET vendor_no = ? WHERE vendorid = ?", array($resource_hash['code'], $organization->id));
    }
  }
}