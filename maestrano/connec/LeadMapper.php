<?php

/**
* Map Connec Lead Person representation to/from vTiger Lead
*/
class LeadMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Person';
    $this->local_entity_name = 'Leads';
    $this->connec_resource_name = 'people';
    $this->connec_resource_endpoint = 'people';
  }

  // Return the Person local id
  protected function getId($lead) {
    return $lead->id;
  }

  // Return a local Person by id
  protected function loadModelById($local_id) {
    $lead = CRMEntity::getInstance("Leads");
    $lead->retrieve_entity_info($local_id, "Leads");
    vtlib_setup_modulevars("Leads", $lead);
    $lead->id = $local_id;
    $lead->mode = 'edit';
    return $lead;
  }

  protected function validate($resource_hash) {
    // Process only Customers
    return $resource_hash['is_lead'];
  }

  // Map the Connec resource attributes onto the vTiger Person
  protected function mapConnecResourceToModel($lead_hash, $lead) {
    // Map hash attributes to Person
    if($this->is_set($lead_hash['code'])) { $lead->column_fields['lead_no'] = $lead_hash['code']; }
    if($this->is_set($lead_hash['title'])) { $lead->column_fields['salutation'] = $lead_hash['title']; }
    if($this->is_set($lead_hash['first_name'])) { $lead->column_fields['firstname'] = $lead_hash['first_name']; }
    if($this->is_set($lead_hash['last_name'])) { $lead->column_fields['lastname'] = $lead_hash['last_name']; }
    if($this->is_set($lead_hash['description'])) { $lead->column_fields['description'] = $lead_hash['description']; }

    // if($this->is_set($lead_hash['address_work'])) {
    //   if($this->is_set($lead_hash['address_work']['billing'])) {
    //     $billing_address = $lead_hash['address_work']['billing'];
    //     if($this->is_set($billing_address['line1'])) { $lead->column_fields['otherstreet'] = $billing_address['line1']; }
    //     if($this->is_set($billing_address['line2'])) { $lead->column_fields['otherpobox'] = $billing_address['line2']; }
    //     if($this->is_set($billing_address['city'])) { $lead->column_fields['othercity'] = $billing_address['city']; }
    //     if($this->is_set($billing_address['region'])) { $lead->column_fields['otherstate'] = $billing_address['region']; }
    //     if($this->is_set($billing_address['postal_code'])) { $lead->column_fields['otherzip'] = $billing_address['postal_code']; }
    //     if($this->is_set($billing_address['country'])) { $lead->column_fields['othercountry'] = $billing_address['country']; }
    //   }

    //   if($this->is_set($lead_hash['address_work']['shipping'])) {
    //     $shipping_address = $lead_hash['address_work']['shipping'];
    //     if($this->is_set($shipping_address['line1'])) { $lead->column_fields['mailingstreet'] = $shipping_address['line1']; }
    //     if($this->is_set($shipping_address['line2'])) { $lead->column_fields['mailingpobox'] = $shipping_address['line2']; }
    //     if($this->is_set($shipping_address['city'])) { $lead->column_fields['mailingcity'] = $shipping_address['city']; }
    //     if($this->is_set($shipping_address['region'])) { $lead->column_fields['mailingstate'] = $shipping_address['region']; }
    //     if($this->is_set($shipping_address['postal_code'])) { $lead->column_fields['mailingzip'] = $shipping_address['postal_code']; }
    //     if($this->is_set($shipping_address['country'])) { $lead->column_fields['mailingcountry'] = $shipping_address['country']; }
    //   }
    // }

    // if($this->is_set($lead_hash['phone_work'])) {
    //   if($this->is_set($lead_hash['phone_work']['landline'])) { $lead->column_fields['phone'] = $lead_hash['phone_work']['landline']; }
    //   if($this->is_set($lead_hash['phone_work']['landline2'])) { $lead->column_fields['otherphone'] = $lead_hash['phone_work']['landline2']; }
    // }

    // if($this->is_set($lead_hash['phone_home']) && $this->is_set($lead_hash['phone_home']['landline'])) {
    //   $lead->column_fields['homephone'] = $lead_hash['phone_home']['landline'];
    // }

    // if($this->is_set($lead_hash['email']['address'])) { $lead->column_fields['email'] = $lead_hash['email']['address']; }
    // if($this->is_set($lead_hash['email']['address2'])) { $lead->column_fields['secondaryemail'] = $lead_hash['email']['address2']; }

    // // Map Organization
    // if($this->is_set($lead_hash['organization_id'])) {
    //   $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($lead_hash['organization_id'], 'ORGANIZATION');
    //   if($mno_id_map) { $lead_hash['account_id'] = $mno_id_map['app_entity_id']; }
    // }
  }

  // Map the vTiger Person to a Connec resource hash
  protected function mapModelToConnecResource($lead) {
    $lead_hash = array();

    // Save as Customer
    $lead_hash['is_lead'] = true;

    // Unset Customer flag when creating a new Lead
    if($this->is_new($lead)) { $lead_hash['is_customer'] = false; }

    // Map attributes
    if($this->is_set($lead->column_fields['lead_no'])) { $lead_hash['code'] = $lead->column_fields['lead_no']; }
    if($this->is_set($lead->column_fields['salutation'])) { $lead_hash['title'] = $lead->column_fields['salutation']; }
    if($this->is_set($lead->column_fields['firstname'])) { $lead_hash['first_name'] = $lead->column_fields['firstname']; }
    if($this->is_set($lead->column_fields['lastname'])) { $lead_hash['last_name'] = $lead->column_fields['lastname']; }
    if($this->is_set($lead->column_fields['description'])) { $lead_hash['description'] = $lead->column_fields['description']; }
    
    // $address = array();
    // $billing_address = array();
    // if($this->is_set($lead->column_fields['otherstreet'])) { $billing_address['line1'] = $lead->column_fields['otherstreet']; }
    // if($this->is_set($lead->column_fields['otherpobox'])) { $billing_address['line2'] = $lead->column_fields['otherpobox']; }
    // if($this->is_set($lead->column_fields['othercity'])) { $billing_address['city'] = $lead->column_fields['othercity']; }
    // if($this->is_set($lead->column_fields['otherstate'])) { $billing_address['region'] = $lead->column_fields['otherstate']; }
    // if($this->is_set($lead->column_fields['otherzip'])) { $billing_address['postal_code'] = $lead->column_fields['otherzip']; }
    // if($this->is_set($lead->column_fields['othercountry'])) { $billing_address['country'] = $lead->column_fields['othercountry']; }
    // if(!empty($billing_address)) { $address['billing'] = $billing_address; }

    // $shipping_address = array();
    // if($this->is_set($lead->column_fields['mailingstreet'])) { $shipping_address['line1'] = $lead->column_fields['mailingstreet']; }
    // if($this->is_set($lead->column_fields['mailingpobox'])) { $shipping_address['line2'] = $lead->column_fields['mailingpobox']; }
    // if($this->is_set($lead->column_fields['mailingcity'])) { $shipping_address['city'] = $lead->column_fields['mailingcity']; }
    // if($this->is_set($lead->column_fields['mailingstate'])) { $shipping_address['region'] = $lead->column_fields['mailingstate']; }
    // if($this->is_set($lead->column_fields['mailingzip'])) { $shipping_address['postal_code'] = $lead->column_fields['mailingzip']; }
    // if($this->is_set($lead->column_fields['mailingcountry'])) { $shipping_address['country'] = $lead->column_fields['mailingcountry']; }
    // if(!empty($shipping_address)) { $address['shipping'] = $shipping_address; }
    // if(!empty($address)) { $lead_hash['address_work'] = $address; }

    // $phone_work_hash = array();
    // if($this->is_set($lead->column_fields['phone'])) { $phone_work_hash['landline'] = $lead->column_fields['phone']; }
    // if($this->is_set($lead->column_fields['otherphone'])) { $phone_work_hash['landline2'] = $lead->column_fields['otherphone']; }
    // if(!empty($phone_work_hash)) { $lead_hash['phone_work'] = $phone_work_hash; }

    // if($this->is_set($lead->column_fields['homephone'])) {$lead_hash['phone_home'] = array('landline' => $lead->column_fields['homephone']); }

    // $email_hash = array();
    // if($this->is_set($lead->column_fields['email'])) { $email_hash['address'] = $lead->column_fields['email']; }
    // if($this->is_set($lead->column_fields['secondaryemail'])) { $email_hash['address2'] = $lead->column_fields['secondaryemail']; }
    // if(!empty($email_hash)) { $lead_hash['email'] = $email_hash; }

    // // Map Organization
    // if($this->is_set($lead->column_fields['account_id'])) {
    //   $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($lead->column_fields['account_id'], 'ACCOUNTS');
    //   if($mno_id_map) { $lead_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    // }

    return $lead_hash;
  }

  // Persist the vTiger Person
  protected function persistLocalModel($lead, $resource_hash) {
    $lead->save("Leads", $lead->id, false);
  }
}
