<?php

/**
* Map Connec Quote representation to/from vTiger Quote
*/
class QuoteMapper extends TransactionMapper {
  private $quote_status_mapping = null;
  private $quote_status_mapping_reverse = null;

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Quote';
    $this->local_entity_name = 'Quotes';
    $this->connec_resource_name = 'quotes';
    $this->connec_resource_endpoint = 'quotes';

    $this->quote_status_mapping = array('DRAFT' => 'Created', 'PENDING' => 'Delivered', 'REVIEWED' => 'Reviewed', 'ACCEPTED' => 'Accepted', 'REJECTED' => 'Rejected');
    $this->quote_status_mapping_reverse = array('Created' => 'DRAFT', 'Delivered' => 'PENDING', 'Reviewed' => 'REVIEWED', 'Accepted' => 'ACCEPTED', 'Rejected' => 'REJECTED');
  }

  // Map the Connec resource attributes onto the vTiger Quote
  protected function mapConnecResourceToModel($quote_hash, $quote) {
    parent::mapConnecResourceToModel($quote_hash, $quote);

    if($this->is_set($quote_hash['deposit'])) { $quote->column_fields['received'] = $quote_hash['deposit']; }
    if($this->is_set($quote_hash['due_date'])) { $quote->column_fields['validtill'] = $this->format_date_to_php($quote_hash['due_date']); }

    // Map status: Created, Delivered, Reviewed, Accepted, Rejected
    $quote->column_fields['quotestage'] = $this->quote_status_mapping[$quote_hash['status']];
  }

  // Map the vTiger Quote to a Connec resource hash
  protected function mapModelToConnecResource($quote) {
    $quote_hash = parent::mapModelToConnecResource($quote);

    // Map attributes
    if($this->is_set($quote->column_fields['validtill'])) { $quote_hash['due_date'] = $this->format_date_to_connec($quote->column_fields['validtill']); }

    // Map status: Created, Delivered, Reviewed, Accepted, Rejected
    $quote_hash['status'] = $this->quote_status_mapping_reverse[$quote->column_fields['quotestage']];

    return $quote_hash;
  }
}