<?php

/**
* Map Connec Invoice representation to/from vTiger Invoice
*/
class InvoiceMapper extends TransactionMapper {
  protected $sales_order_mapper = null;

  private $invoice_status_mapping = null;
  private $invoice_status_mapping_reverse = null;

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Invoice';
    $this->local_entity_name = 'Invoice';
    $this->connec_resource_name = 'invoices';
    $this->connec_resource_endpoint = 'invoices';

    $this->sales_order_mapper = new SalesOrderMapper();

    $this->invoice_status_mapping = array('DRAFT' => 'Created', 'SUBMITTED' => 'Sent', 'AUTHORISED' => 'Approved', 'PAID' => 'Paid');
    $this->invoice_status_mapping_reverse = array('Created' => 'DRAFT', 'Sent' => 'SUBMITTED', 'Approved' => 'AUTHORISED', 'Paid' => 'PAID');
  }

  protected function validate($invoice_hash) {
    // Process only Customer Invoices
    return $invoice_hash['type'] == 'CUSTOMER';
  }

  // Map the Connec resource attributes onto the vTiger Invoice
  protected function mapConnecResourceToModel($invoice_hash, $invoice) {
    parent::mapConnecResourceToModel($invoice_hash, $invoice);

    if($this->is_set($invoice_hash['transaction_number'])) { $invoice->column_fields['customerno'] = $invoice_hash['transaction_number']; }
    if($this->is_set($invoice_hash['deposit'])) { $invoice->column_fields['received'] = $invoice_hash['deposit']; }
    if($this->is_set($invoice_hash['balance'])) { $invoice->column_fields['balance'] = $invoice_hash['balance']; }
    if($this->is_set($invoice_hash['transaction_date'])) { $invoice->column_fields['invoicedate'] = $this->format_date_to_php($invoice_hash['transaction_date']); }
    if($this->is_set($invoice_hash['due_date'])) { $invoice->column_fields['duedate'] = $this->format_date_to_php($invoice_hash['due_date']); }

    // Map status
    $invoice->column_fields['invoicestatus'] = $this->invoice_status_mapping[$invoice_hash['status']];

    // Map Sales Order reference
    if(array_key_exists('sales_order_id', $invoice_hash)) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($invoice_hash['sales_order_id'], 'SALESORDER');
      if($mno_id_map) { $invoice->column_fields['salesorder_id'] = $mno_id_map['app_entity_id']; }
    }
  }

  // Map the vTiger Invoice to a Connec resource hash
  protected function mapModelToConnecResource($invoice) {
    $invoice_hash = parent::mapModelToConnecResource($invoice);

    // Default invoice type to CUSTOMER on creation
    $invoice_hash['type'] = 'CUSTOMER';

    // Map attributes
    $invoice_hash['transaction_number'] = $invoice->column_fields['customerno'];
    $invoice_hash['deposit'] = $invoice->column_fields['received'];
    $invoice_hash['balance'] = $invoice->column_fields['balance'];
    if($this->is_set($invoice->column_fields['invoicedate'])) { $invoice_hash['transaction_date'] = $this->format_date_to_connec($invoice->column_fields['invoicedate']); }
    if($this->is_set($invoice->column_fields['duedate'])) { $invoice_hash['due_date'] = $this->format_date_to_connec($invoice->column_fields['duedate']); }
    $invoice_hash['status'] = $this->invoice_status_mapping_reverse[$invoice->column_fields['invoicestatus']];

    // Map Sales Order reference
    if(array_key_exists('salesorder_id', $invoice->column_fields)) {
      $sales_order_id = $this->sales_order_mapper->findConnecIdByLocalId($invoice->column_fields['salesorder_id']);
      if($sales_order_id) { $invoice_hash['sales_order_id'] = $sales_order_id; }
    } else {
      $invoice_hash['sales_order_id'] = '';
    }

    return $invoice_hash;
  }
}