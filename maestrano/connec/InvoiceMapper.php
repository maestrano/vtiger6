<?php

/**
* Map Connec Invoice representation to/from vTiger Invoice
*/
class InvoiceMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Invoice';
    $this->local_entity_name = 'Invoice';
    $this->connec_resource_name = 'invoices';
    $this->connec_resource_endpoint = 'invoices';
  }

  // Return the Invoice local id
  protected function getId($invoice) {
    return $invoice->id;
  }

  // Return a local Invoice by id
  protected function loadModelById($local_id) {
    $invoice = CRMEntity::getInstance("Invoice");
    $invoice->retrieve_entity_info($local_id, "Invoice");
    vtlib_setup_modulevars("Invoice", $invoice);
    $invoice->id = $local_id;
    $invoice->mode = 'edit';
    return $invoice;
  }

  protected function validate($invoice_hash) {
    // Process only Customer Invoices
    return $invoice_hash['type'] == 'CUSTOMER';
  }

  // Map the Connec resource attributes onto the vTiger Invoice
  protected function mapConnecResourceToModel($invoice_hash, $invoice) {
    // Map hash attributes to Invoice
    if($this->is_set($invoice_hash['title'])) { $invoice->column_fields['subject'] = $invoice_hash['title']; }
    if($this->is_set($invoice_hash['transaction_number'])) { $invoice->column_fields['customerno'] = $invoice_hash['transaction_number']; }
    if($this->is_set($invoice_hash['public_note'])) { $invoice->column_fields['notes'] = $invoice_hash['public_note']; }
  }

  // Map the vTiger Invoice to a Connec resource hash
  protected function mapModelToConnecResource($invoice) {
    $invoice_hash = array();

    // Default invoice type to CUSTOMER on creation
    if($this->is_new($invoice)) { $invoice_hash['type'] = 'CUSTOMER'; }

    // Map attributes
    if($this->is_set($invoice->column_fields['subject'])) { $invoice_hash['title'] = $invoice->column_fields['subject']; }
    if($this->is_set($invoice->column_fields['customerno'])) { $invoice_hash['transaction_number'] = $invoice->column_fields['customerno']; }
    if($this->is_set($invoice->column_fields['notes'])) { $invoice_hash['public_note'] = $invoice->column_fields['notes']; }

    return $invoice_hash;
  }

  // Persist the vTiger Invoice
  protected function persistLocalModel($invoice, $invoice_hash) {
    $invoice->save("Invoice", $invoice->id, false);
  }
}
