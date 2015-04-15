<?php

/**
* Map Connec Quote representation to/from vTiger Quote
*/
class QuoteMapper extends BaseMapper {
  protected $serviceMapper = null;

  private $quote_status_mapping = null;
  private $quote_status_mapping_reverse = null;

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Quote';
    $this->local_entity_name = 'Quotes';
    $this->connec_resource_name = 'quotes';
    $this->connec_resource_endpoint = 'quotes';

    $this->serviceMapper = new ServiceMapper();

    $this->quote_status_mapping = array('DRAFT' => 'Created', 'PENDING' => 'Delivered', 'REVIEWED' => 'Reviewed', 'ACCEPTED' => 'Accepted', 'REJECTED' => 'Rejected');
    $this->quote_status_mapping_reverse = array('Created' => 'DRAFT', 'Delivered' => 'PENDING', 'Reviewed' => 'REVIEWED', 'Accepted' => 'ACCEPTED', 'Rejected' => 'REJECTED');
  }

  // Return the Quote local id
  protected function getId($quote) {
    return $quote->id;
  }

  // Return a local Quote by id
  protected function loadModelById($local_id) {
    $quote = CRMEntity::getInstance("Quotes");
    $quote->retrieve_entity_info($local_id, "Quotes");
    vtlib_setup_modulevars("Quotes", $quote);
    $quote->id = $local_id;
    $quote->mode = 'edit';
    return $quote;
  }

  // Map the Connec resource attributes onto the vTiger Quote
  protected function mapConnecResourceToModel($quote_hash, $quote) {
    // TODO Map/Create Currency
    if(!$this->is_set($quote->column_fields['currency_id'])) { $quote->column_fields['currency_id'] = 1; }
    if(!$this->is_set($quote->column_fields['conversion_rate'])) { $quote->column_fields['conversion_rate'] = 1; }

    if($this->is_set($quote_hash['title'])) {
      $quote->column_fields['subject'] = $quote_hash['title'];
    } else {
      $quote->column_fields['subject'] = $quote_hash['transaction_number'];
    }
    if($this->is_set($quote_hash['public_note'])) { $quote->column_fields['notes'] = $quote_hash['public_note']; }
    if($this->is_set($quote_hash['deposit'])) { $quote->column_fields['received'] = $quote_hash['deposit']; }
    if($this->is_set($quote_hash['due_date'])) { $quote->column_fields['validtill'] = $this->format_date_to_php($quote_hash['due_date']); }

    // Map status: Created, Delivered, Reviewed, Accepted, Rejected
    $quote->column_fields['quotestage'] = $this->quote_status_mapping[$quote_hash['status']];

    // Map Organization
    if($this->is_set($quote_hash['organization_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($quote_hash['organization_id'], 'ORGANIZATION', 'ACCOUNTS');
      if($mno_id_map) { $quote->column_fields['account_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map Contact
    if($this->is_set($quote_hash['person_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($quote_hash['person_id'], 'PERSON', 'CONTACTS');
      if($mno_id_map) { $quote->column_fields['contact_id'] = $mno_id_map['app_entity_id']; }
    }

    if($this->is_set($quote_hash['billing_address'])) {
      $billing_address = $quote_hash['billing_address'];
      if($billing_address['line1']) { $quote->column_fields['bill_street'] = $billing_address['line1']; }
      if($billing_address['line2']) { $quote->column_fields['bill_pobox'] = $billing_address['line2']; }
      if($billing_address['city']) { $quote->column_fields['bill_city'] = $billing_address['city']; }
      if($billing_address['region']) { $quote->column_fields['bill_state'] = $billing_address['region']; }
      if($billing_address['postal_code']) { $quote->column_fields['bill_code'] = $billing_address['postal_code']; }
      if($billing_address['country']) { $quote->column_fields['bill_country'] = $billing_address['country']; }
    }

    if($this->is_set($quote_hash['shipping_address'])) {
      $shipping_address = $quote_hash['shipping_address'];
      if($shipping_address['line1']) { $quote->column_fields['ship_street'] = $shipping_address['line1']; }
      if($shipping_address['line2']) { $quote->column_fields['ship_pobox'] = $shipping_address['line2']; }
      if($shipping_address['city']) { $quote->column_fields['ship_city'] = $shipping_address['city']; }
      if($shipping_address['region']) { $quote->column_fields['ship_state'] = $shipping_address['region']; }
      if($shipping_address['postal_code']) { $quote->column_fields['ship_code'] = $shipping_address['postal_code']; }
      if($shipping_address['country']) { $quote->column_fields['ship_country'] = $shipping_address['country']; }
    }

    // Map Quote lines
    // The class include/utils/InventoryUtils.php expects to find a $_REQUEST object with the quote lines populated
    $_REQUEST = array();
    if(!empty($quote_hash['lines'])) {
      $_REQUEST['subtotal'] = $quote_hash['amount']['total_amount'];
      $_REQUEST['total'] = $quote_hash['amount']['total_amount'];
      $_REQUEST['taxtype'] = 'individual';

      $line_count = 0;
      foreach($quote_hash['lines'] as $quote_line) {
        $line_count++;

        // Map item
        if(!empty($quote_line['item_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($quote_line['item_id'], 'PRODUCT');
          $_REQUEST['hdnProductId'.$line_count] = $mno_id_map['app_entity_id'];
        } else {
          // Set default service
          $service = $this->serviceMapper->defaultService();
          $_REQUEST['hdnProductId'.$line_count] = $service['serviceid'];
        }

        // Map attributes
        $_REQUEST['comment'.$line_count] = $quote_line['description'];
        $_REQUEST['qty'.$line_count] = $quote_line['quantity'];
        $_REQUEST['listPrice'.$line_count] = $quote_line['unit_price']['net_amount'];

        if(isset($quote_line['reduction_percent'])) {
          $_REQUEST['discount_type'.$line_count] = 'percentage';
          $_REQUEST['discount_percentage'.$line_count] = $quote_line['reduction_percent'];
        } else {
          $_REQUEST['discount_type'.$line_count] = '';
          $_REQUEST['discount_percentage'.$line_count] = 0;
        }

        // Map Quote Line Taxes
        $this->mapQuoteLineTaxes($quote_line);
      }
      $_REQUEST['totalProductCount'] = $line_count;
    }
  }

  // Map the vTiger Quote to a Connec resource hash
  protected function mapModelToConnecResource($quote) {
    global $adb;

    $quote_hash = array();

    // Missing quote lines are considered as deleted by Connec!
    $quote_hash['opts'] = array('sparse' => false);

    // Map attributes
    if($this->is_set($quote->column_fields['subject'])) { $quote_hash['title'] = $quote->column_fields['subject']; }
    if($this->is_set($quote->column_fields['notes'])) { $quote_hash['public_note'] = $quote->column_fields['notes']; }
    if($this->is_set($quote->column_fields['validtill'])) { $quote_hash['due_date'] = $this->format_date_to_connec($quote->column_fields['validtill']); }

    // Map status: Created, Delivered, Reviewed, Accepted, Rejected
    $quote_hash['status'] = $this->quote_status_mapping_reverse[$quote->column_fields['quotestage']];

    // Map Organization
    if($this->is_set($quote->column_fields['account_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($quote->column_fields['account_id'], 'ACCOUNTS');
      if($mno_id_map) { $quote_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map Contact
    if($this->is_set($quote->column_fields['contact_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($quote->column_fields['contact_id'], 'CONTACTS');
      if($mno_id_map) { $quote_hash['person_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map address
    $quote_hash['billing_address'] = array(
      'line1' => $quote->column_fields['bill_street'],
      'line2' => $quote->column_fields['bill_pobox'],
      'city' => $quote->column_fields['bill_city'],
      'region' => $quote->column_fields['bill_state'],
      'postal_code' => $quote->column_fields['bill_code'],
      'country' => $quote->column_fields['bill_country']
    );

    $quote_hash['shipping_address'] = array(
      'line1' => $quote->column_fields['ship_street'],
      'line2' => $quote->column_fields['ship_pobox'],
      'city' => $quote->column_fields['ship_city'],
      'region' => $quote->column_fields['ship_state'],
      'postal_code' => $quote->column_fields['ship_code'],
      'country' => $quote->column_fields['ship_country']
    );

    // Map quote lines
    $quote_hash['lines'] = array();
    $result = $adb->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id = ?", array($quote->id));
    while($quote_line_detail = $adb->fetch_array($result)) {

      $quote_line = array();
      $productid = intval($quote_line_detail['productid']);
      $line_number = intval($quote_line_detail['sequence_no']);
      $quantity = intval($quote_line_detail['quantity']);
      $listprice = floatval($quote_line_detail['listprice']);
      $discount_percent = floatval($quote_line_detail['discount_percent']);
      $discount_amount = floatval($quote_line_detail['discount_amount']);
      $comment = $quote_line_detail['comment'];
      $description = $quote_line_detail['description'];

      // vTiger recreates the quote lines on every save, so local IDs are not mappable
      // Use QuoteID#LineNumber instead
      $quote_line_id = $quote->id . "#" . $line_number;
      $mno_quote_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($quote_line_id, "INVOICE_LINE");
      if($mno_quote_line_id) {
        // Reuse Connec Quote Line ID
        $quote_line_id_parts = explode("#", $mno_quote_line_id['mno_entity_guid']);
        $quote_line['id'] = $quote_line_id_parts[1];
      }

      $quote_line['line_number'] = $line_number;
      $quote_line['description'] = $comment;
      $quote_line['quantity'] = $quantity;
      $quote_line['reduction_percent'] = $discount_percent;
      $quote_line['unit_price'] = array('net_amount' => $listprice);

      // Line applicable tax (limit to one)
      $total_line_tax = 0;
      foreach ($quote_line_detail as $key => $value) {
        if(preg_match('/^tax\d+/', $key) && !is_null($value) && $value > 0) {
          $tax = TaxMapper::getTaxByName($key);
          $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax['taxid'], 'TAXRECORD');
          if($mno_id_map) { $quote_line['tax_code_id'] = $mno_id_map['mno_entity_guid']; break; }
        }
      }

      // Map item id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($productid, 'PRODUCTS');
      if($mno_id_map) { $quote_line['item_id'] = $mno_id_map['mno_entity_guid']; }

      $quote_hash['lines'][] = $quote_line;
    }

    return $quote_hash;
  }

  // Persist the vTiger Quote
  protected function persistLocalModel($quote, $quote_hash) {
    $quote->save("Quotes", $quote->id, false);

    // Map quote lines ids
    foreach ($quote_hash['lines'] as $quote_line) {
      $quote_line_local_id = $quote->id . "#" . $quote_line['line_number'];
      $quote_line_mno_id = $quote_hash['id'] . "#" . $quote_line['id'];
      $mno_quote_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($quote_line_local_id, "INVOICE_LINE");
      if($mno_quote_line_id) {
        MnoIdMap::updateIdMapEntry($mno_quote_line_id['mno_entity_guid'], $quote_line_mno_id, "INVOICE_LINE");
      } else {
        MnoIdMap::addMnoIdMap($quote_line_local_id, "INVOICE_LINE", $quote_line_mno_id, "INVOICE_LINE");
      }
    }
  }

  protected function mapQuoteLineTaxes($line_hash) {
    global $adb;

    // Set all taxes to 0 by default
    $result = $adb->pquery("SELECT * FROM vtiger_inventorytaxinfo WHERE deleted = 0");
    $numrow = $adb->num_rows($result);
    for($k=0; $k < $numrow; $k++) {
      $taxname = $adb->query_result($result, $k, 'taxname');
      $request_tax_name = $taxname."_percentage".$line_hash['line_number'];
      $_REQUEST[$request_tax_name] = 0;
    }

    // Apply tax for this quote line
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