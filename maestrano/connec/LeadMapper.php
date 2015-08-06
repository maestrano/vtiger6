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
    if($this->is_set($lead_hash['title'])) { $lead->column_fields['salutationtype'] = $lead_hash['title']; }
    if($this->is_set($lead_hash['first_name'])) { $lead->column_fields['firstname'] = $lead_hash['first_name']; }
    if($this->is_set($lead_hash['last_name'])) { $lead->column_fields['lastname'] = $lead_hash['last_name']; }
    if($this->is_set($lead_hash['description'])) { $lead->column_fields['description'] = $lead_hash['description']; }
    if($this->is_set($lead_hash['lead_status'])) { $lead->column_fields['leadstatus'] = $lead_hash['lead_status']; }
    if($this->is_set($lead_hash['lead_source'])) { $lead->column_fields['leadsource'] = $lead_hash['lead_source']; }

    if($this->is_set($lead_hash['address_work']) && $this->is_set($lead_hash['address_work']['shipping'])) {
      $shipping_address = $lead_hash['address_work']['shipping'];
      if($this->is_set($shipping_address['line1'])) { $lead->column_fields['lane'] = $shipping_address['line1']; }
      if($this->is_set($shipping_address['line2'])) { $lead->column_fields['pobox'] = $shipping_address['line2']; }
      if($this->is_set($shipping_address['city'])) { $lead->column_fields['city'] = $shipping_address['city']; }
      if($this->is_set($shipping_address['region'])) { $lead->column_fields['state'] = $shipping_address['region']; }
      if($this->is_set($shipping_address['postal_code'])) { $lead->column_fields['code'] = $shipping_address['postal_code']; }
      if($this->is_set($shipping_address['country'])) { $lead->column_fields['country'] = $shipping_address['country']; }
    }

    if($this->is_set($lead_hash['phone_work'])) {
      if($this->is_set($lead_hash['phone_work']['landline'])) { $lead->column_fields['phone'] = $lead_hash['phone_work']['landline']; }
      if($this->is_set($lead_hash['phone_work']['mobile'])) { $lead->column_fields['mobile'] = $lead_hash['phone_work']['mobile']; }
      if($this->is_set($lead_hash['phone_work']['fax'])) { $lead->column_fields['fax'] = $lead_hash['phone_work']['fax']; }
    }

    if($this->is_set($lead_hash['email']['address'])) { $lead->column_fields['email'] = $lead_hash['email']['address']; }
    if($this->is_set($lead_hash['email']['address2'])) { $lead->column_fields['secondaryemail'] = $lead_hash['email']['address2']; }

    if($this->is_set($lead_hash['website']['url'])) { $lead->column_fields['website'] = $lead_hash['website']['url']; }

    // Map Organization name as Lead company
    if($this->is_set($lead_hash['organization_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($lead_hash['organization_id'], 'ORGANIZATION', 'ACCOUNTS');
      if($mno_id_map) {
        $account_id = $mno_id_map['app_entity_id'];
        $account = CRMEntity::getInstance("Accounts");
        $account->retrieve_entity_info($account_id, "Accounts");
        $lead->column_fields['company'] = $account->column_fields['accountname'];
      }
    }

    $mno_id_map = false;
    if ($lead_hash['assignee_type'] == "Entity::AppUser") {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($lead_hash['assignee_id'], 'AppUser');
    } else if ($lead_hash['assignee_type'] == "Entity::Team") {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($lead_hash['assignee_id'], 'Team');
    }
    if ($mno_id_map) { $lead->column_fields['assigned_user_id'] = $mno_id_map['app_entity_id']; }

  }

  // Map the vTiger Person to a Connec resource hash
  protected function mapModelToConnecResource($lead) {
    $lead_hash = array();

    // Save as Lead
    $lead_hash['is_lead'] = true;

    // Unset Customer flag when creating a new Lead
    if($this->is_new($lead)) { $lead_hash['is_customer'] = false; }

    // Map attributes
    $lead_hash['code'] = $lead->column_fields['lead_no'];
    $lead_hash['title'] = $lead->column_fields['salutationtype'];
    $lead_hash['first_name'] = $lead->column_fields['firstname'];
    $lead_hash['last_name'] = $lead->column_fields['lastname'];
    $lead_hash['description'] = $lead->column_fields['description'];
    $lead_hash['lead_status'] = $lead->column_fields['leadstatus'];
    $lead_hash['lead_source'] = $lead->column_fields['leadsource'];
    
    $address = array();
    $shipping_address = array();
    $shipping_address['line1'] = $lead->column_fields['lane'];
    $shipping_address['line2'] = $lead->column_fields['pobox'];
    $shipping_address['city'] = $lead->column_fields['city'];
    $shipping_address['region'] = $lead->column_fields['state'];
    $shipping_address['postal_code'] = $lead->column_fields['code'];
    $shipping_address['country'] = $lead->column_fields['country'];
    if(!empty($shipping_address)) { $address['shipping'] = $shipping_address; }
    if(!empty($address)) { $lead_hash['address_work'] = $address; }

    $phone_work_hash = array();
    $phone_work_hash['landline'] = $lead->column_fields['phone'];
    $phone_work_hash['mobile'] = $lead->column_fields['mobile'];
    $phone_work_hash['fax'] = $lead->column_fields['fax'];
    if(!empty($phone_work_hash)) { $lead_hash['phone_work'] = $phone_work_hash; }

    $email_hash = array();
    $email_hash['address'] = $lead->column_fields['email'];
    $email_hash['address2'] = $lead->column_fields['secondaryemail'];
    if(!empty($email_hash)) { $lead_hash['email'] = $email_hash; }

    // Map Organization by Name
    if($this->is_set($lead->column_fields['company'])) {
      $organization_fields = CustomerOrganizationMapper::findByName($lead->column_fields['company']);
      if($this->is_set($organization_fields)) {
        $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($organization_fields['accountid'], 'ACCOUNTS');
        if($mno_id_map) { $lead_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
      }
    }

    // Map Assigned User / team
    if($this->is_set($lead->column_fields['assigned_user_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($lead->column_fields['assigned_user_id'], 'USERS');
      if($mno_id_map) {
        $lead_hash['assignee_id'] = $mno_id_map['mno_entity_guid'];
        $lead_hash['assignee_type'] = "Entity::AppUser";
      }
      else {
        $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($lead->column_fields['assigned_user_id'], 'Groups');
        if($mno_id_map) {
          $lead_hash['assignee_id'] = $mno_id_map['mno_entity_guid'];
          $lead_hash['assignee_type'] = "Entity::Team";
        }
      }
    }    

    return $lead_hash;
  }

  // Persist the vTiger Person
  protected function persistLocalModel($lead, $resource_hash) {
    $lead->save("Leads", $lead->id, false);
  }
}