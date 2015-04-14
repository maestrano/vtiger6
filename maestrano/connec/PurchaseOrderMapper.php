<?php

/**
* Map Connec Supplier Invoice representation to/from vTiger PurchaseOrder
*/
class PurchaseOrderMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Invoice';
    $this->local_entity_name = 'PurchaseOrder';
    $this->connec_resource_name = 'invoices';
    $this->connec_resource_endpoint = 'invoices';
  }

  // Return the PurchaseOrder local id
  protected function getId($purchase_order) {
    return $purchase_order->id;
  }

  // Return a local PurchaseOrder by id
  protected function loadModelById($local_id) {
    $purchase_order = CRMEntity::getInstance("PurchaseOrder");
    $purchase_order->retrieve_entity_info($local_id, "PurchaseOrder");
    vtlib_setup_modulevars("PurchaseOrder", $purchase_order);
    $purchase_order->id = $local_id;
    $purchase_order->mode = 'edit';
    return $purchase_order;
  }

  protected function validate($purchase_order_hash) {
    // Process only Customer PurchaseOrders
    return $purchase_order_hash['type'] == 'SUPPLIER';
  }

  // Map the Connec resource attributes onto the vTiger PurchaseOrder
  protected function mapConnecResourceToModel($purchase_order_hash, $purchase_order) {
    // TODO Map/Create Currency
    if(!$this->is_set($purchase_order->column_fields['currency_id'])) { $purchase_order->column_fields['currency_id'] = 1; }
    if(!$this->is_set($purchase_order->column_fields['conversion_rate'])) { $purchase_order->column_fields['conversion_rate'] = 1; }

    if($this->is_set($purchase_order_hash['title'])) {
      $purchase_order->column_fields['subject'] = $purchase_order_hash['title'];
    } else {
      $purchase_order->column_fields['subject'] = $purchase_order_hash['transaction_number'];
    }
    if($this->is_set($purchase_order_hash['transaction_number'])) { $purchase_order->column_fields['requisition_no'] = $purchase_order_hash['transaction_number']; }
    if($this->is_set($purchase_order_hash['public_note'])) { $purchase_order->column_fields['description'] = $purchase_order_hash['public_note']; }
    if($this->is_set($purchase_order_hash['deposit'])) { $purchase_order->column_fields['paid'] = $purchase_order_hash['deposit']; }
    if($this->is_set($purchase_order_hash['balance'])) { $purchase_order->column_fields['balance'] = $purchase_order_hash['balance']; }
    if($this->is_set($invoice_hash['due_date'])) { $invoice->column_fields['duedate'] = $this->format_date_to_php($invoice_hash['due_date']); }

    // Map status
    $status = $purchase_order_hash['status'];
    if($status == 'SUBMITTED') { $purchase_order->column_fields['invoicestatus'] = 'Sent'; }
    else if($status == 'AUTHORISED') { $purchase_order->column_fields['invoicestatus'] = 'Approved'; }
    else if($status == 'PAID') { $purchase_order->column_fields['invoicestatus'] = 'Paid'; }
    else { $purchase_order->column_fields['invoicestatus'] = 'Created'; }

    // Map Organization
    if($this->is_set($purchase_order_hash['organization_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($purchase_order_hash['organization_id'], 'ORGANIZATION', 'VENDORS');
      if($mno_id_map) { $purchase_order->column_fields['vendor_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map Contact
    if($this->is_set($purchase_order_hash['person_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($purchase_order_hash['person_id'], 'PERSON', 'CONTACTS');
      if($mno_id_map) { $purchase_order->column_fields['contact_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map PurchaseOrder lines
    // The class include/utils/InventoryUtils.php expects to find a $_REQUEST object with the invoice lines populated
    $_REQUEST = array();
    if(!empty($purchase_order_hash['invoice_lines'])) {
      $_REQUEST['subtotal'] = $purchase_order_hash['amount']['total_amount'];
      $_REQUEST['total'] = $purchase_order_hash['amount']['total_amount'];
      $_REQUEST['taxtype'] = 'individual';

      $line_count = 0;
      foreach($purchase_order_hash['invoice_lines'] as $purchase_order_line) {
        $line_count++;

        // Map item
        if(!empty($purchase_order_line['item_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($purchase_order_line['item_id'], 'PRODUCT');
          $_REQUEST['hdnProductId'.$line_count] = $mno_id_map['app_entity_id'];
        } else {
          // Set default service
          $service = $this->serviceMapper->defaultService();
          $_REQUEST['hdnProductId'.$line_count] = $service['serviceid'];
        }

        // Map attributes
        $_REQUEST['comment'.$line_count] = $purchase_order_line['description'];
        $_REQUEST['qty'.$line_count] = $purchase_order_line['quantity'];
        $_REQUEST['listPrice'.$line_count] = $purchase_order_line['unit_price']['net_amount'];

        if(isset($purchase_order_line['reduction_percent'])) {
          $_REQUEST['discount_type'.$line_count] = 'percentage';
          $_REQUEST['discount_percentage'.$line_count] = $purchase_order_line['reduction_percent'];
        } else {
          $_REQUEST['discount_type'.$line_count] = '';
          $_REQUEST['discount_percentage'.$line_count] = 0;
        }

        // Map PurchaseOrder Line Taxes
        $this->mapPurchaseOrderLineTaxes($purchase_order_line);
      }
      $_REQUEST['totalProductCount'] = $line_count;
    }
  }

  // Map the vTiger PurchaseOrder to a Connec resource hash
  protected function mapModelToConnecResource($purchase_order) {
    global $adb;

    $purchase_order_hash = array();

    // Missing invoice lines are considered as deleted by Connec!
    $purchase_order_hash['opts'] = array('sparse' => false);

    // Default invoice type to SUPPLIER on creation
    $purchase_order_hash['type'] = 'SUPPLIER';

    // Map attributes
    if($this->is_set($purchase_order->column_fields['subject'])) { $purchase_order_hash['title'] = $purchase_order->column_fields['subject']; }
    if($this->is_set($purchase_order->column_fields['requisition_no'])) { $purchase_order_hash['transaction_number'] = $purchase_order->column_fields['requisition_no']; }
    if($this->is_set($purchase_order->column_fields['description'])) { $purchase_order_hash['public_note'] = $purchase_order->column_fields['description']; }
    if($this->is_set($purchase_order->column_fields['paid'])) { $purchase_order_hash['deposit'] = $purchase_order->column_fields['paid']; }
    if($this->is_set($purchase_order->column_fields['balance'])) { $purchase_order_hash['balance'] = $purchase_order->column_fields['balance']; }
    if($this->is_set($invoice->column_fields['duedate'])) { $invoice_hash['due_date'] = $this->format_date_to_connec($invoice->column_fields['duedate']); }

    // Map status
    $status = $purchase_order->column_fields['invoicestatus'];
    if($status == 'Sent') { $purchase_order_hash['status'] = 'SUBMITTED'; }
    else if($status == 'Approved') { $purchase_order_hash['status'] = 'AUTHORISED'; }
    else if($status == 'Paid') { $purchase_order_hash['status'] = 'PAID'; }
    else { $purchase_order_hash['status'] = 'DRAFT'; }

    // Map Organization
    if($this->is_set($purchase_order->column_fields['vendor_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($purchase_order->column_fields['vendor_id'], 'VENDORS');
      if($mno_id_map) { $purchase_order_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map Contact
    if($this->is_set($purchase_order->column_fields['contact_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($purchase_order->column_fields['contact_id'], 'CONTACTS');
      if($mno_id_map) { $purchase_order_hash['person_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map invoice lines
    $purchase_order_hash['invoice_lines'] = array();
    $result = $adb->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id = ?", array($purchase_order->id));
    while($purchase_order_line_detail = $adb->fetch_array($result)) {
      $purchase_order_line = array();
      $productid = intval($purchase_order_line_detail['productid']);
      $line_number = intval($purchase_order_line_detail['sequence_no']);
      $quantity = intval($purchase_order_line_detail['quantity']);
      $listprice = floatval($purchase_order_line_detail['listprice']);
      $discount_percent = floatval($purchase_order_line_detail['discount_percent']);
      $discount_amount = floatval($purchase_order_line_detail['discount_amount']);
      $comment = $purchase_order_line_detail['comment'];
      $description = $purchase_order_line_detail['description'];

      // vTiger recreates the invoice lines on every save, so local IDs are not mappable
      // Use PurchaseOrderID#LineNumber instead
      $purchase_order_line_id = $purchase_order->id . "#" . $line_number;
      $mno_invoice_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($purchase_order_line_id, "INVOICE_LINE");
      if($mno_invoice_line_id) {
        // Reuse Connec PurchaseOrder Line ID
        $purchase_order_line_id_parts = explode("#", $mno_invoice_line_id['mno_entity_guid']);
        $purchase_order_line['id'] = $purchase_order_line_id_parts[1];
      }

      $purchase_order_line['line_number'] = $line_number;
      $purchase_order_line['description'] = $comment;
      $purchase_order_line['quantity'] = $quantity;
      $purchase_order_line['reduction_percent'] = $discount_percent;
      $purchase_order_line['unit_price'] = array('net_amount' => $listprice);

      // Line applicable tax (limit to one)
      $total_line_tax = 0;
      foreach ($purchase_order_line_detail as $key => $value) {
        if(preg_match('/^tax\d+/', $key) && !is_null($value) && $value > 0) {
          $tax = TaxMapper::getTaxByName($key);
          $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax['taxid'], 'TAXRECORD');
          if($mno_id_map) { $purchase_order_line['tax_code_id'] = $mno_id_map['mno_entity_guid']; break; }
        }
      }

      // Map item id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($productid, 'PRODUCTS');
      if($mno_id_map) { $purchase_order_line['item_id'] = $mno_id_map['mno_entity_guid']; }

      $purchase_order_hash['invoice_lines'][] = $purchase_order_line;
    }

    return $purchase_order_hash;
  }

  // Persist the vTiger PurchaseOrder
  protected function persistLocalModel($purchase_order, $purchase_order_hash) {
    $purchase_order->save("PurchaseOrder", $purchase_order->id, false);

    // Map invoice lines ids
    foreach ($purchase_order_hash['invoice_lines'] as $purchase_order_line) {
      $purchase_order_line_local_id = $purchase_order->id . "#" . $purchase_order_line['line_number'];
      $purchase_order_line_mno_id = $purchase_order_hash['id'] . "#" . $purchase_order_line['id'];
      $mno_invoice_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($purchase_order_line_local_id, "INVOICE_LINE");
      if($mno_invoice_line_id) {
        MnoIdMap::updateIdMapEntry($mno_invoice_line_id['mno_entity_guid'], $purchase_order_line_mno_id, "INVOICE_LINE");
      } else {
        MnoIdMap::addMnoIdMap($purchase_order_line_local_id, "INVOICE_LINE", $purchase_order_line_mno_id, "INVOICE_LINE");
      }
    }
  }

  protected function mapPurchaseOrderLineTaxes($line_hash) {
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