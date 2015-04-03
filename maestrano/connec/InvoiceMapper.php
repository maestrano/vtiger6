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
    if($this->is_set($invoice_hash['title'])) {
      $invoice->column_fields['subject'] = $invoice_hash['title'];
    } else {
      $invoice->column_fields['subject'] = $invoice_hash['transaction_number'];
    }
    if($this->is_set($invoice_hash['transaction_number'])) { $invoice->column_fields['customerno'] = $invoice_hash['transaction_number']; }
    if($this->is_set($invoice_hash['public_note'])) { $invoice->column_fields['notes'] = $invoice_hash['public_note']; }

    if($this->is_set($invoice_hash['transaction_date'])) { $invoice->column_fields['invoicedate'] = $invoice_hash['transaction_date']; }
    if($this->is_set($invoice_hash['due_date'])) { $invoice->column_fields['duedate'] = $invoice_hash['due_date']; }

    // Map status
    $status = $invoice_hash['status'];
    if($status == 'SUBMITTED') {
      $invoice->column_fields['invoicestatus'] = 'Sent';
    } else if($status == 'AUTHORISED') {
      $invoice->column_fields['invoicestatus'] = 'Approved';
    } else if($status == 'PAID') {
      $invoice->column_fields['invoicestatus'] = 'Paid';
    } else {
      $invoice->column_fields['invoicestatus'] = 'Created';
    }

    // Map Organization
    if($this->is_set($invoice_hash['organization_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($invoice_hash['organization_id'], 'ORGANIZATION', 'ACCOUNTS');
      if($mno_id_map) { $invoice->column_fields['account_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map Contact
    if($this->is_set($invoice_hash['person_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($invoice_hash['person_id'], 'PERSON', 'CONTACTS');
      if($mno_id_map) { $invoice->column_fields['contact_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map Invoice lines
    // The class include/utils/InventoryUtils.php expects to find a $_REQUEST object with the invoice lines populated
    $_REQUEST = array();
    if(!empty($invoice_hash['invoice_lines'])) {
      $_REQUEST['subtotal'] = $invoice_hash['amount']['total_amount'];
      $_REQUEST['total'] = $invoice_hash['amount']['total_amount'];
      $_REQUEST['taxtype'] = 'individual';

      $line_count = 0;
      foreach($invoice_hash['invoice_lines'] as $invoice_line) {
        $line_count++;
        $mno_invoice_line_id = $invoice_hash['id'] . "#" . $invoice_line['id'];
        $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($mno_invoice_line_id, 'INVOICE_LINE', 'INVOICE_LINE');
        // TODO: Check lines has not been deleted

        // Map item
        if(!empty($invoice_line['item_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($invoice_line['item_id'], 'PRODUCT', 'PRODUCTS');
          $_REQUEST['hdnProductId'.$line_count] = $mno_id_map['app_entity_id'];
        }

        // Map attributes
        $_REQUEST['comment'.$line_count] = $invoice_line['description'];
        $_REQUEST['qty'.$line_count] = $invoice_line['quantity'];
        $_REQUEST['listPrice'.$line_count] = $invoice_line['unit_price']['net_amount'];

        if(isset($invoice_line['reduction_percent'])) {
          $_REQUEST['discount_type'.$line_count] = 'percentage';
          $_REQUEST['discount_percentage'.$line_count] = $invoice_line['reduction_percent'];
        } else {
          $_REQUEST['discount_type'.$line_count] = '';
          $_REQUEST['discount_percentage'.$line_count] = 0;
        }

        // TODO Map Taxes
      }
      $_REQUEST['totalProductCount'] = $line_count;
    }
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
