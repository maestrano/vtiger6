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
error_log("MAP CONNECRESOURCE BACK");
    $date_format = $this->date_format();
error_log("DATE FORMAT: " . json_encode($date_format));
    // TODO Map/Create Currency
    if(!$this->is_set($invoice->column_fields['currency_id'])) { $invoice->column_fields['currency_id'] = 1; }
    if(!$this->is_set($invoice->column_fields['conversion_rate'])) { $invoice->column_fields['conversion_rate'] = 1; }

    if($this->is_set($invoice_hash['title'])) {
      $invoice->column_fields['subject'] = $invoice_hash['title'];
    } else {
      $invoice->column_fields['subject'] = $invoice_hash['transaction_number'];
    }
    if($this->is_set($invoice_hash['transaction_number'])) { $invoice->column_fields['customerno'] = $invoice_hash['transaction_number']; }
    if($this->is_set($invoice_hash['public_note'])) { $invoice->column_fields['notes'] = $invoice_hash['public_note']; }
    if($this->is_set($invoice_hash['deposit'])) { $invoice->column_fields['received'] = $invoice_hash['deposit']; }
    if($this->is_set($invoice_hash['balance'])) { $invoice->column_fields['balance'] = $invoice_hash['balance']; }

    if($this->is_set($invoice_hash['transaction_date'])) { $invoice->column_fields['invoicedate'] = date($date_format, strtotime($invoice_hash['transaction_date'])); }
    if($this->is_set($invoice_hash['due_date'])) { $invoice->column_fields['duedate'] = date($date_format, strtotime($invoice_hash['due_date'])); }

    // Map status
    $status = $invoice_hash['status'];
    if($status == 'SUBMITTED') { $invoice->column_fields['invoicestatus'] = 'Sent'; }
    else if($status == 'AUTHORISED') { $invoice->column_fields['invoicestatus'] = 'Approved'; }
    else if($status == 'PAID') { $invoice->column_fields['invoicestatus'] = 'Paid'; }
    else { $invoice->column_fields['invoicestatus'] = 'Created'; }

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

    if($this->is_set($invoice_hash['billing_address'])) {
      $billing_address = $invoice_hash['billing_address'];
      if($billing_address['line1']) { $invoice->column_fields['bill_street'] = $billing_address['line1']; }
      if($billing_address['line2']) { $invoice->column_fields['bill_pobox'] = $billing_address['line2']; }
      if($billing_address['city']) { $invoice->column_fields['bill_city'] = $billing_address['city']; }
      if($billing_address['region']) { $invoice->column_fields['bill_state'] = $billing_address['region']; }
      if($billing_address['postal_code']) { $invoice->column_fields['bill_code'] = $billing_address['postal_code']; }
      if($billing_address['country']) { $invoice->column_fields['bill_country'] = $billing_address['country']; }
    }

    if($this->is_set($invoice_hash['shipping_address'])) {
      $shipping_address = $invoice_hash['shipping_address'];
      if($shipping_address['line1']) { $invoice->column_fields['ship_street'] = $shipping_address['line1']; }
      if($shipping_address['line2']) { $invoice->column_fields['ship_pobox'] = $shipping_address['line2']; }
      if($shipping_address['city']) { $invoice->column_fields['ship_city'] = $shipping_address['city']; }
      if($shipping_address['region']) { $invoice->column_fields['ship_state'] = $shipping_address['region']; }
      if($shipping_address['postal_code']) { $invoice->column_fields['ship_code'] = $shipping_address['postal_code']; }
      if($shipping_address['country']) { $invoice->column_fields['ship_country'] = $shipping_address['country']; }
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

        // Map item
        if(!empty($invoice_line['item_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($invoice_line['item_id'], 'PRODUCT');
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

        // Map Invoice Line Taxes
        $this->mapInvoiceLineTaxes($invoice_line);
      }
      $_REQUEST['totalProductCount'] = $line_count;
    }
  }

  // Map the vTiger Invoice to a Connec resource hash
  protected function mapModelToConnecResource($invoice) {
    global $adb;

    $invoice_hash = array();

    // Missing invoice lines are considered as deleted by Connec!
    $invoice_hash['opts'] = array('sparse' => false);

    // Default invoice type to CUSTOMER on creation
    $invoice_hash['type'] = 'CUSTOMER';

    // Map attributes
    if($this->is_set($invoice->column_fields['subject'])) { $invoice_hash['title'] = $invoice->column_fields['subject']; }
    if($this->is_set($invoice->column_fields['customerno'])) { $invoice_hash['transaction_number'] = $invoice->column_fields['customerno']; }
    if($this->is_set($invoice->column_fields['notes'])) { $invoice_hash['public_note'] = $invoice->column_fields['notes']; }
    if($this->is_set($invoice->column_fields['received'])) { $invoice_hash['deposit'] = $invoice->column_fields['received']; }
    if($this->is_set($invoice->column_fields['balance'])) { $invoice_hash['balance'] = $invoice->column_fields['balance']; }

    if($this->is_set($invoice->column_fields['invoicedate'])) {
error_log('PARSE ' . json_encode($invoice->column_fields['invoicedate']));
      $transaction_date = DateTime::createFromFormat('d-m-Y', $invoice->column_fields['invoicedate']);
error_log('PARSED ' . json_encode($transaction_date));
      $invoice_hash['transaction_date'] = $transaction_date->format('c');
    }
    if($this->is_set($invoice->column_fields['duedate'])) {
      $due_date = DateTime::createFromFormat('d-m-Y', $invoice->column_fields['duedate']);
      $invoice_hash['due_date'] = $due_date->format('c');
    }

    // Map status
    $status = $invoice->column_fields['invoicestatus'];
    if($status == 'Sent') { $invoice_hash['status'] = 'SUBMITTED'; }
    else if($status == 'Approved') { $invoice_hash['status'] = 'AUTHORISED'; }
    else if($status == 'Paid') { $invoice_hash['status'] = 'PAID'; }
    else { $invoice_hash['status'] = 'DRAFT'; }

    // Map Organization
    if($this->is_set($invoice->column_fields['account_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($invoice->column_fields['account_id'], 'ACCOUNTS');
      if($mno_id_map) { $invoice_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map Contact
    if($this->is_set($invoice->column_fields['contact_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($invoice->column_fields['contact_id'], 'CONTACTS');
      if($mno_id_map) { $invoice_hash['person_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map address
    $invoice_hash['billing_address'] = array(
      'line1' => $invoice->column_fields['bill_street'],
      'line2' => $invoice->column_fields['bill_pobox'],
      'city' => $invoice->column_fields['bill_city'],
      'region' => $invoice->column_fields['bill_state'],
      'postal_code' => $invoice->column_fields['bill_code'],
      'country' => $invoice->column_fields['bill_country']
    );

    $invoice_hash['shipping_address'] = array(
      'line1' => $invoice->column_fields['ship_street'],
      'line2' => $invoice->column_fields['ship_pobox'],
      'city' => $invoice->column_fields['ship_city'],
      'region' => $invoice->column_fields['ship_state'],
      'postal_code' => $invoice->column_fields['ship_code'],
      'country' => $invoice->column_fields['ship_country']
    );

    // Map invoice lines
    $invoice_hash['invoice_lines'] = array();
    $result = $adb->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id = ?", array($invoice->id));
    while($invoice_line_detail = $adb->fetch_array($result)) {

      $invoice_line = array();
      $productid = intval($invoice_line_detail['productid']);
      $line_number = intval($invoice_line_detail['sequence_no']);
      $quantity = intval($invoice_line_detail['quantity']);
      $listprice = floatval($invoice_line_detail['listprice']);
      $discount_percent = floatval($invoice_line_detail['discount_percent']);
      $discount_amount = floatval($invoice_line_detail['discount_amount']);
      $comment = $invoice_line_detail['comment'];
      $description = $invoice_line_detail['description'];

      // vTiger recreates the invoice lines on every save, so local IDs are not mappable
      // Use InvoiceID#LineNumber instead
      $invoice_line_id = $invoice->id . "#" . $line_number;
      $mno_invoice_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($invoice_line_id, "INVOICE_LINE");
      if($mno_invoice_line_id) {
        // Reuse Connec Invoice Line ID
        $invoice_line_id_parts = explode("#", $mno_invoice_line_id['mno_entity_guid']);
        $invoice_line['id'] = $invoice_line_id_parts[1];
      }

      $invoice_line['line_number'] = $line_number;
      $invoice_line['description'] = $comment;
      $invoice_line['quantity'] = $quantity;
      $invoice_line['reduction_percent'] = $discount_percent;
      $invoice_line['unit_price'] = array('net_amount' => $listprice);

      // Line applicable tax (limit to one)
      $total_line_tax = 0;
      foreach ($invoice_line_detail as $key => $value) {
        if(preg_match('/^tax\d+/', $key) && !is_null($value) && $value > 0) {
          $tax = TaxMapper::getTaxByName($key);
          $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax['taxid'], 'TAXRECORD');
          if($mno_id_map) { $invoice_line['tax_code_id'] = $mno_id_map['mno_entity_guid']; break; }
        }
      }

      // Map item id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($productid, 'PRODUCTS');
      if($mno_id_map) { $invoice_line['item_id'] = $mno_id_map['mno_entity_guid']; }

      $invoice_hash['invoice_lines'][] = $invoice_line;
    }

    return $invoice_hash;
  }

  // Persist the vTiger Invoice
  protected function persistLocalModel($invoice, $invoice_hash) {
    $invoice->save("Invoice", $invoice->id, false);

    // Map invoice lines ids
    foreach ($invoice_hash['invoice_lines'] as $invoice_line) {
      $invoice_line_local_id = $invoice->id . "#" . $invoice_line['line_number'];
      $invoice_line_mno_id = $invoice_hash['id'] . "#" . $invoice_line['id'];
      $mno_invoice_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($invoice_line_local_id, "INVOICE_LINE");
      if($mno_invoice_line_id) {
        MnoIdMap::updateIdMapEntry($mno_invoice_line_id['mno_entity_guid'], $invoice_line_mno_id, "INVOICE_LINE");
      } else {
        MnoIdMap::addMnoIdMap($invoice_line_local_id, "INVOICE_LINE", $invoice_line_mno_id, "INVOICE_LINE");
      }
    }
  }

  protected function mapInvoiceLineTaxes($line_hash) {
    global $adb;

    // Set all taxes to 0 by default
    $result = $adb->pquery("SELECT * FROM vtiger_inventorytaxinfo WHERE deleted = 0");
    $numrow = $adb->num_rows($result);
    for($k=0; $k < $numrow; $k++) {
      $taxname = $adb->query_result($result, $k, 'taxname');
      $request_tax_name = $taxname."_percentage".$line_hash['line_number'];
      $_REQUEST[$request_tax_name] = 0;
    }

    // Apply tax for this invoice line
    if($this->is_set($line_hash['tax_code_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($line_hash['tax_code_id'], 'TAXCODE');
      $tax = Settings_Vtiger_TaxRecord_Model::getInstanceById($mno_id_map['app_entity_id'], Settings_Vtiger_TaxRecord_Model::PRODUCT_AND_SERVICE_TAX);
      if(isset($tax)) {
        $request_tax_name = $tax->get('taxname')."_percentage".$line_hash['line_number'];
        $_REQUEST[$request_tax_name] = $tax->get('percentage');
      }
    }
  }
}