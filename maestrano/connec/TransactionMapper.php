<?php

/**
* Base mapper for transactions (quote, transaction, sales order, purchase order)
*/
class TransactionMapper extends BaseMapper {
  protected $serviceMapper = null;

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Transaction';
    $this->local_entity_name = 'Transaction';
    $this->connec_resource_name = 'transactions';
    $this->connec_resource_endpoint = 'transactions';

    $this->serviceMapper = new ServiceMapper();
  }

  // Return the Transaction local id
  protected function getId($transaction) {
    return $transaction->id;
  }

  // Return a local Transaction by id
  protected function loadModelById($local_id) {
    $transaction = CRMEntity::getInstance($this->local_entity_name);
    $transaction->retrieve_entity_info($local_id, $this->local_entity_name);
    vtlib_setup_modulevars($this->local_entity_name, $transaction);
    $transaction->id = $local_id;
    $transaction->mode = 'edit';
    return $transaction;
  }

  // Map the Connec resource attributes onto the vTiger Transaction
  protected function mapConnecResourceToModel($transaction_hash, $transaction) {
    // TODO Map/Create Currency
    if(!$this->is_set($transaction->column_fields['currency_id'])) { $transaction->column_fields['currency_id'] = 1; }
    if(!$this->is_set($transaction->column_fields['conversion_rate'])) { $transaction->column_fields['conversion_rate'] = 1; }

    if($this->is_set($transaction_hash['title'])) {
      $transaction->column_fields['subject'] = $transaction_hash['title'];
    } else {
      $transaction->column_fields['subject'] = $transaction_hash['transaction_number'];
    }
    if($this->is_set($transaction_hash['public_note'])) { $transaction->column_fields['notes'] = $transaction_hash['public_note']; }

    // Map Organization
    if($this->is_set($transaction_hash['organization_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($transaction_hash['organization_id'], 'ORGANIZATION', 'ACCOUNTS');
      if($mno_id_map) { $transaction->column_fields['account_id'] = $mno_id_map['app_entity_id']; }

      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($transaction_hash['organization_id'], 'ORGANIZATION', 'VENDORS');
      if($mno_id_map) { $transaction->column_fields['vendor_id'] = $mno_id_map['app_entity_id']; }
    }

    // Map Contact
    if($this->is_set($transaction_hash['person_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($transaction_hash['person_id'], 'PERSON', 'CONTACTS');
      if($mno_id_map) { $transaction->column_fields['contact_id'] = $mno_id_map['app_entity_id']; }
    }

    if($this->is_set($transaction_hash['billing_address'])) {
      $billing_address = $transaction_hash['billing_address'];
      if($billing_address['line1']) { $transaction->column_fields['bill_street'] = $billing_address['line1']; }
      if($billing_address['line2']) { $transaction->column_fields['bill_pobox'] = $billing_address['line2']; }
      if($billing_address['city']) { $transaction->column_fields['bill_city'] = $billing_address['city']; }
      if($billing_address['region']) { $transaction->column_fields['bill_state'] = $billing_address['region']; }
      if($billing_address['postal_code']) { $transaction->column_fields['bill_code'] = $billing_address['postal_code']; }
      if($billing_address['country']) { $transaction->column_fields['bill_country'] = $billing_address['country']; }
    }

    if($this->is_set($transaction_hash['shipping_address'])) {
      $shipping_address = $transaction_hash['shipping_address'];
      if($shipping_address['line1']) { $transaction->column_fields['ship_street'] = $shipping_address['line1']; }
      if($shipping_address['line2']) { $transaction->column_fields['ship_pobox'] = $shipping_address['line2']; }
      if($shipping_address['city']) { $transaction->column_fields['ship_city'] = $shipping_address['city']; }
      if($shipping_address['region']) { $transaction->column_fields['ship_state'] = $shipping_address['region']; }
      if($shipping_address['postal_code']) { $transaction->column_fields['ship_code'] = $shipping_address['postal_code']; }
      if($shipping_address['country']) { $transaction->column_fields['ship_country'] = $shipping_address['country']; }
    }

    // Map Transaction lines
    // The class include/utils/InventoryUtils.php expects to find a $_REQUEST object with the transaction lines populated
    if(is_null($_REQUEST)) { $_REQUEST = array(); }
    
    if(!empty($transaction_hash['lines'])) {
      $_REQUEST['subtotal'] = $transaction_hash['amount']['total_amount'];
      $_REQUEST['total'] = $transaction_hash['amount']['total_amount'];
      
      // Force tax type to individual only for new Transactions
      if(!$this->is_set($_REQUEST['taxtype'])) { $_REQUEST['taxtype'] = 'individual'; }

      $line_count = 0;
      foreach($transaction_hash['lines'] as $transaction_line) {
        $line_count++;

        // Map item
        if(!empty($transaction_line['item_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($transaction_line['item_id'], 'PRODUCT');
          $product_id = $mno_id_map['app_entity_id'];
          $_REQUEST['hdnProductId'.$line_count] = $product_id;

          // Add tax to item
          ProductMapper::mapConnecTaxToProduct($transaction_line['tax_code_id'], $product_id);
        } else {
          // Set default service
          $service = $this->serviceMapper->defaultService();
          $_REQUEST['hdnProductId'.$line_count] = $service['serviceid'];

          // Add tax to item
          ProductMapper::mapConnecTaxToProduct($transaction_line['tax_code_id'], $service['serviceid']);
        }

        // Map attributes
        $_REQUEST['comment'.$line_count] = $transaction_line['description'];
        $_REQUEST['qty'.$line_count] = $transaction_line['quantity'];
        $_REQUEST['listPrice'.$line_count] = $transaction_line['unit_price']['net_amount'];

        if(isset($transaction_line['reduction_percent'])) {
          $_REQUEST['discount_type'.$line_count] = 'percentage';
          $_REQUEST['discount_percentage'.$line_count] = $transaction_line['reduction_percent'];
        } else {
          $_REQUEST['discount_type'.$line_count] = '';
          $_REQUEST['discount_percentage'.$line_count] = 0;
        }

        // Map Transaction Line Taxes
        $this->mapTransactionLineTaxes($transaction_line);
      }
      $_REQUEST['totalProductCount'] = $line_count;
    }
  }

  // Map the vTiger Transaction to a Connec resource hash
  protected function mapModelToConnecResource($transaction) {
    global $adb;

    $transaction_hash = array();

    // Missing transaction lines are considered as deleted by Connec!
    $transaction_hash['opts'] = array('sparse' => false);

    // Map attributes
    if($this->is_set($transaction->column_fields['subject'])) { $transaction_hash['title'] = $transaction->column_fields['subject']; }
    if($this->is_set($transaction->column_fields['notes'])) { $transaction_hash['public_note'] = $transaction->column_fields['notes']; }

    // Map Organization
    if($this->is_set($transaction->column_fields['account_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($transaction->column_fields['account_id'], 'ACCOUNTS');
      if($mno_id_map) { $transaction_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map Vendor
    if($this->is_set($transaction->column_fields['vendor_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($transaction->column_fields['vendor_id'], 'VENDORS');
      if($mno_id_map) { $transaction_hash['organization_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map Contact
    if($this->is_set($transaction->column_fields['contact_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($transaction->column_fields['contact_id'], 'CONTACTS');
      if($mno_id_map) { $transaction_hash['person_id'] = $mno_id_map['mno_entity_guid']; }
    }

    // Map address
    $transaction_hash['billing_address'] = array(
      'line1' => $transaction->column_fields['bill_street'],
      'line2' => $transaction->column_fields['bill_pobox'],
      'city' => $transaction->column_fields['bill_city'],
      'region' => $transaction->column_fields['bill_state'],
      'postal_code' => $transaction->column_fields['bill_code'],
      'country' => $transaction->column_fields['bill_country']
    );

    $transaction_hash['shipping_address'] = array(
      'line1' => $transaction->column_fields['ship_street'],
      'line2' => $transaction->column_fields['ship_pobox'],
      'city' => $transaction->column_fields['ship_city'],
      'region' => $transaction->column_fields['ship_state'],
      'postal_code' => $transaction->column_fields['ship_code'],
      'country' => $transaction->column_fields['ship_country']
    );

    // Map transaction lines
    $transaction_hash['lines'] = array();
    $result = $adb->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id = ?", array($transaction->id));
    while($transaction_line_detail = $adb->fetch_array($result)) {
      $transaction_line = array();
      $productid = intval($transaction_line_detail['productid']);
      $line_number = intval($transaction_line_detail['sequence_no']);
      $quantity = floatval($transaction_line_detail['quantity']);
      $listprice = floatval($transaction_line_detail['listprice']);
      $discount_percent = floatval($transaction_line_detail['discount_percent']);
      $discount_amount = floatval($transaction_line_detail['discount_amount']);
      $comment = $transaction_line_detail['comment'];
      $description = $transaction_line_detail['description'];

      // vTiger recreates the transaction lines on every save, so local IDs are not mappable
      // Use TransactionID#LineNumber instead
      $transaction_line_id = $transaction->id . "#" . $line_number;
      $mno_transaction_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($transaction_line_id, "TRANSACTION_LINE");
      if($mno_transaction_line_id) {
        // Reuse Connec Transaction Line ID
        $transaction_line_id_parts = explode("#", $mno_transaction_line_id['mno_entity_guid']);
        $transaction_line['id'] = $transaction_line_id_parts[1];
      }

      $transaction_line['status'] = 'ACTIVE';
      $transaction_line['line_number'] = $line_number;
      $transaction_line['description'] = $comment;
      $transaction_line['quantity'] = $quantity;
      $transaction_line['reduction_percent'] = $discount_percent;
      $transaction_line['unit_price'] = array('net_amount' => $listprice);

      // Line applicable tax (limit to one)
      if($_REQUEST['taxtype'] == 'individual') {
        foreach ($transaction_line_detail as $key => $value) {
          if(preg_match('/^tax\d+/', $key) && !is_null($value) && $value > 0) {
            $tax = TaxMapper::getTaxByName($key);
            $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax['taxid'], 'TAXRECORD');
            if($mno_id_map) {
              $transaction_line['tax_code_id'] = $mno_id_map['mno_entity_guid'];
              $individual_tax = true;
              break;
            }
          }
        }
      }

      if($_REQUEST['taxtype'] == 'group') {
        foreach ($transaction_line_detail as $key => $value) {
          if(preg_match('/^tax\d+/', $key) && !is_null($value) && $value > 0) {
            $tax = TaxMapper::getTaxByName($key);
            $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax['taxid'], 'TAXRECORD');
            if($mno_id_map) {
              $transaction_line['tax_code_id'] = $mno_id_map['mno_entity_guid'];
              $individual_tax = true;
              break;
            }
          }
        }
      }

      // Map item id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($productid, 'PRODUCTS');
      if($mno_id_map) { $transaction_line['item_id'] = $mno_id_map['mno_entity_guid']; }

      $transaction_hash['lines'][] = $transaction_line;
    }

    return $transaction_hash;
  }

  // Persist the vTiger Transaction
  protected function persistLocalModel($transaction, $transaction_hash) {
    $transaction->save($this->local_entity_name, $transaction->id, false);

    // Map transaction lines ids
    foreach ($transaction_hash['lines'] as $transaction_line) {
      $transaction_line_local_id = $transaction->id . "#" . $transaction_line['line_number'];
      $transaction_line_mno_id = $transaction_hash['id'] . "#" . $transaction_line['id'];
      $mno_transaction_line_id = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($transaction_line_local_id, "TRANSACTION_LINE");
      if($mno_transaction_line_id) {
        MnoIdMap::updateIdMapEntry($mno_transaction_line_id['mno_entity_guid'], $transaction_line_mno_id, "TRANSACTION_LINE");
      } else {
        MnoIdMap::addMnoIdMap($transaction_line_local_id, "TRANSACTION_LINE", $transaction_line_mno_id, "TRANSACTION_LINE");
      }
    }
  }

  protected function mapTransactionLineTaxes($line_hash) {
    global $adb;

    // Set all taxes to 0 by default
    $result = $adb->pquery("SELECT * FROM vtiger_inventorytaxinfo WHERE deleted = 0");
    $numrow = $adb->num_rows($result);
    for($k=0; $k < $numrow; $k++) {
      $taxname = $adb->query_result($result, $k, 'taxname');
      $request_tax_name = $taxname."_percentage".$line_hash['line_number'];
      $_REQUEST[$request_tax_name] = 0;
    }

    // Apply tax for this transaction line
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