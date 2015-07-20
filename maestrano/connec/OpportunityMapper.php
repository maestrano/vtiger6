<?php

/**
* Map Connec Opportunity representation to/from vTiger Potential
*/
class OpportunityMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Opportunity';
    $this->local_entity_name = 'Potentials';
    $this->connec_resource_name = 'opportunities';
    $this->connec_resource_endpoint = 'opportunities';
  }

  // Return the Potential local id
  protected function getId($potential) {
    return $potential->id;
  }

  // Return a local Potential by id
  protected function loadModelById($local_id) {
    $potential = CRMEntity::getInstance("Potentials");
    $potential->retrieve_entity_info($local_id, "Potentials");
    vtlib_setup_modulevars("Potentials", $potential);
    $potential->id = $local_id;
    $potential->mode = 'edit';
    return $potential;
  }

  // Map the Connec resource attributes onto the vTiger Potential
  protected function mapConnecResourceToModel($opportunity_hash, $potential) {
    // Map hash attributes to Potential
    if($this->is_set($opportunity_hash['name'])) { $potential->column_fields['potentialname'] = $opportunity_hash['name']; }
    if($this->is_set($opportunity_hash['code'])) { $potential->column_fields['potential_no'] = $opportunity_hash['code']; }
    if($this->is_set($opportunity_hash['description'])) { $potential->column_fields['description'] = $opportunity_hash['description']; }
    if($this->is_set($opportunity_hash['sales_stage'])) { $potential->column_fields['sales_stage'] = $opportunity_hash['sales_stage']; }
    if($this->is_set($opportunity_hash['type'])) { $potential->column_fields['opportunity_type'] = $opportunity_hash['type']; }
    if($this->is_set($opportunity_hash['next_step'])) { $potential->column_fields['nextstep'] = $opportunity_hash['next_step']; }
    if($this->is_set($opportunity_hash['probability'])) { $potential->column_fields['probability'] = number_format($opportunity_hash['probability'],2,'.',''); }
    if($this->is_set($opportunity_hash['amount'])) { $potential->column_fields['amount'] = number_format($opportunity_hash['amount']['total_amount'],2,'.',''); }
    if($this->is_set($opportunity_hash['expected_close_date'])) { $potential->column_fields['closingdate'] = $this->format_date_to_php($opportunity_hash['expected_close_date']); }

    // Map Lead entity by id
    if($this->is_set($opportunity_hash['lead_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($opportunity_hash['lead_id'], 'PERSON', 'CONTACTS');
      if($mno_id_map) {
        $contact_id = $mno_id_map['app_entity_id'];
        $contact = CRMEntity::getInstance("Contacts");
        $contact->retrieve_entity_info($contact_id, "Contacts");
        $potential->column_fields['contact_id'] = $contact->column_fields['record_id'];
        $potential->column_fields['leadsource'] = $contact->column_fields['leadsource'];
        if($this->is_set($contact->column_fields['account_id'])) {
          $potential->column_fields['related_to'] = $contact->column_fields['account_id'];
        }
      }
    }
  }

  // Map the vTiger Potential to a Connec Opportunity hash
  protected function mapModelToConnecResource($potential) {
    $opportunity_hash = array();

    // Map attributes
    $opportunity_hash['name'] = $potential->column_fields['potentialname'];
    $opportunity_hash['code'] = $potential->column_fields['potential_no'];
    $opportunity_hash['description'] = $potential->column_fields['description'];
    $opportunity_hash['sales_stage'] = $potential->column_fields['sales_stage'];
    $opportunity_hash['type'] = $potential->column_fields['opportunity_type'];
    $opportunity_hash['next_step'] = $potential->column_fields['nextstep'];
    $opportunity_hash['probability'] = $potential->column_fields['probability'];
    if($this->is_set($potential->column_fields['amount'])) {
      $amount = array();
      $amount['total_amount'] = str_replace(',', '', $potential->column_fields['amount']);
      $opportunity_hash['amount'] = $amount;
    }
    if($this->is_set($potential->column_fields['closingdate'])) {
      $opportunity_hash['expected_close_date'] = $this->format_date_to_connec($potential->column_fields['closingdate']);
    }

    // Map Lead by Id
    if($this->is_set($potential->column_fields['contact_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($potential->column_fields['contact_id'], 'CONTACTS');
      if($mno_id_map) { $opportunity_hash['lead_id'] = $mno_id_map['mno_entity_guid']; }
    }

    return $opportunity_hash;
  }

  // Persist the vTiger Potential
  protected function persistLocalModel($potential, $resource_hash) {
    $potential->save("Potentials", $potential->id, false);
  }
}