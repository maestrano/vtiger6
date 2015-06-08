<?php

/**
* Map Connec Customer Organization representation to/from vTiger Account
*/
class CustomerOrganizationMapper extends BaseMapper {
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

  // Return a local Organization by id
  protected function loadModelById($local_id) {
    $organization = CRMEntity::getInstance("Accounts");
    $organization->retrieve_entity_info($local_id, "Accounts");
    vtlib_setup_modulevars("Accounts", $organization);
    $organization->id = $local_id;
    $organization->mode = 'edit';
    return $organization;
  }

  // Return any existing Organization with same name
  public function matchLocalModel($organization_hash) {
    global $adb;
    $result = $adb->pquery('SELECT accountid FROM vtiger_account WHERE accountname = ? LIMIT 1', array($organization_hash['name']));
    if($result->fields) { return $this->loadModelById($result->fields['accountid']); }
    return null;
  }

  protected function validate($resource_hash) {
    // Process only Customers
    return $resource_hash['is_customer'];
  }

  // Map the Connec resource attributes onto the vTiger Organization
  protected function mapConnecResourceToModel($organization_hash, $organization) {
    // Default type to Customer
    if($organization->column_fields['accounttype'] == '') { $organization->column_fields['accounttype'] = 'Customer'; }

    // Map hash attributes to Organization
    if($this->is_set($organization_hash['code'])) { $organization->column_fields['account_no'] = $organization_hash['code']; }
    if($this->is_set($organization_hash['name'])) { $organization->column_fields['accountname'] = $organization_hash['name']; }
    if($this->is_set($organization_hash['description'])) { $organization->column_fields['description'] = $organization_hash['description']; }
    if($this->is_set($organization_hash['industry'])) { $organization->column_fields['industry'] = $organization_hash['industry']; }
    if($this->is_set($organization_hash['annual_revenue'])) { $organization->column_fields['annualrevenue'] = $organization_hash['annual_revenue']; }
    if($this->is_set($organization_hash['reference'])) { $organization->column_fields['siccode'] = $organization_hash['reference']; }
    if($this->is_set($organization_hash['number_of_employees'])) { $organization->column_fields['employees'] = $organization_hash['number_of_employees']; }

    if($this->is_set($organization_hash['address'])) {
      if($this->is_set($organization_hash['address']['billing'])) {
        $billing_address = $organization_hash['address']['billing'];
        if($this->is_set($billing_address['line1'])) { $organization->column_fields['bill_street'] = $billing_address['line1']; }
        if($this->is_set($billing_address['line2'])) { $organization->column_fields['bill_pobox'] = $billing_address['line2']; }
        if($this->is_set($billing_address['city'])) { $organization->column_fields['bill_city'] = $billing_address['city']; }
        if($this->is_set($billing_address['region'])) { $organization->column_fields['bill_state'] = $billing_address['region']; }
        if($this->is_set($billing_address['postal_code'])) { $organization->column_fields['bill_code'] = $billing_address['postal_code']; }
        if($this->is_set($billing_address['country'])) { $organization->column_fields['bill_country'] = $billing_address['country']; }
      }

      if($this->is_set($organization_hash['address']['shipping'])) {
        $shipping_address = $organization_hash['address']['shipping'];
        if($this->is_set($shipping_address['line1'])) { $organization->column_fields['ship_street'] = $shipping_address['line1']; }
        if($this->is_set($shipping_address['line2'])) { $organization->column_fields['ship_pobox'] = $shipping_address['line2']; }
        if($this->is_set($shipping_address['city'])) { $organization->column_fields['ship_city'] = $shipping_address['city']; }
        if($this->is_set($shipping_address['region'])) { $organization->column_fields['ship_state'] = $shipping_address['region']; }
        if($this->is_set($shipping_address['postal_code'])) { $organization->column_fields['ship_code'] = $shipping_address['postal_code']; }
        if($this->is_set($shipping_address['country'])) { $organization->column_fields['ship_country'] = $shipping_address['country']; }
      }
    }

    if($this->is_set($organization_hash['phone'])) {
      if($this->is_set($organization_hash['phone']['landline'])) { $organization->column_fields['phone'] = $organization_hash['phone']['landline']; }
      if($this->is_set($organization_hash['phone']['landline2'])) { $organization->column_fields['otherphone'] = $organization_hash['phone']['landline2']; }
      if($this->is_set($organization_hash['phone']['fax'])) { $organization->column_fields['fax'] = $organization_hash['phone']['fax']; }
    }

    if($this->is_set($organization_hash['email']['address'])) { $organization->column_fields['email1'] = $organization_hash['email']['address']; }
    if($this->is_set($organization_hash['email']['address2'])) { $organization->column_fields['email2'] = $organization_hash['email']['address2']; }
    if($this->is_set($organization_hash['website']['url'])) { $organization->column_fields['website'] = $organization_hash['website']['url']; }
  }

  // Map the vTiger Organization to a Connec resource hash
  protected function mapModelToConnecResource($organization) {
    $organization_hash = array();

    // Save as Customer
    $organization_hash['is_customer'] = true;

    // Map attributes
    $organization_hash['code'] = $organization->column_fields['account_no'];
    $organization_hash['name'] = $organization->column_fields['accountname'];
    $organization_hash['description'] = $organization->column_fields['description'];
    $organization_hash['industry'] = $organization->column_fields['industry'];
    $organization_hash['annual_revenue'] = $organization->column_fields['annualrevenue'];
    $organization_hash['reference'] = $organization->column_fields['siccode'];
    $organization_hash['number_of_employees'] = $organization->column_fields['employees'];
    
    $address = array();
    $billing_address = array();
    $billing_address['line1'] = $organization->column_fields['bill_street'];
    $billing_address['line2'] = $organization->column_fields['bill_pobox'];
    $billing_address['city'] = $organization->column_fields['bill_city'];
    $billing_address['region'] = $organization->column_fields['bill_state'];
    $billing_address['postal_code'] = $organization->column_fields['bill_code'];
    $billing_address['country'] = $organization->column_fields['bill_country'];
    if(!empty($billing_address)) { $address['billing'] = $billing_address; }

    $shipping_address = array();
    $shipping_address['line1'] = $organization->column_fields['ship_street'];
    $shipping_address['line2'] = $organization->column_fields['ship_pobox'];
    $shipping_address['city'] = $organization->column_fields['ship_city'];
    $shipping_address['region'] = $organization->column_fields['ship_state'];
    $shipping_address['postal_code'] = $organization->column_fields['ship_code'];
    $shipping_address['country'] = $organization->column_fields['ship_country'];
    if(!empty($shipping_address)) { $address['shipping'] = $shipping_address; }
    if(!empty($address)) { $organization_hash['address'] = $address; }

    
    $phone_hash = array();
    $phone_hash['landline'] = $organization->column_fields['phone'];
    $phone_hash['landline2'] = $organization->column_fields['otherphone'];
    $phone_hash['fax'] = $organization->column_fields['fax'];
    if(!empty($phone_hash)) { $organization_hash['phone'] = $phone_hash; }

    $email_hash = array();
    $email_hash['address'] = $organization->column_fields['email1'];
    $email_hash['address2'] = $organization->column_fields['email2'];
    if(!empty($email_hash)) { $organization_hash['email'] = $email_hash; }

    if($this->is_set($organization->column_fields['website'])) { $organization_hash['website'] = array('url' => $organization->column_fields['website']); }

    return $organization_hash;
  }

  // Persist the vTiger Organization
  protected function persistLocalModel($organization, $resource_hash) {
    $organization->save("Accounts", $organization->id, false);

    // Force Organization code on creation
    if($this->is_new($organization) && $this->is_set($resource_hash['code'])) {
      global $adb;
      $adb->pquery("UPDATE vtiger_account SET account_no = ? WHERE accountid = ?", array($resource_hash['code'], $organization->id));
    }
  }

  // Find an Account entity by name
  public static function findByName($accountname) {
    global $adb;
    $result = $adb->pquery("SELECT * from vtiger_account WHERE accountname = '".$accountname."'");
    if($result) { return $result->fields; }
    return null;
  }
}