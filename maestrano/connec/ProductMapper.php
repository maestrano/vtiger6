<?php

/**
* Map Connec Product representation to/from vTiger Product
*/
class ProductMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Product';
    $this->local_entity_name = 'Products';
    $this->connec_resource_name = 'items';
    $this->connec_resource_endpoint = 'items';
  }

  // Return the Product local id
  protected function getId($product) {
    return $product->id;
  }

  // Return a local Product by id
  protected function loadModelById($local_id) {
    $product = CRMEntity::getInstance("Products");
    $product->retrieve_entity_info($local_id, "Products");
    vtlib_setup_modulevars("Products", $product);
    $product->id = $local_id;
    $product->mode = 'edit';
    return $product;
  }

  protected function validate($resource_hash) {
    // Process only Products
    return $resource_hash['type'] != 'SERVICE';
  }

  // Map the Connec resource attributes onto the vTiger Product
  protected function mapConnecResourceToModel($product_hash, $product) {
    // Map hash attributes to Product
    if(!$this->is_set($product->column_fields['discontinued'])) { $product->column_fields['discontinued'] = 1; }
    if($this->is_set($product_hash['code'])) { $product->column_fields['product_no'] = $product_hash['code']; }
    if($this->is_set($product_hash['name'])) { $product->column_fields['productname'] = $product_hash['name']; }
    if($this->is_set($product_hash['description'])) { $product->column_fields['description'] = $product_hash['description']; }
    if($this->is_set($product_hash['reference'])) { $product->column_fields['productcode'] = $product_hash['reference']; }
    if($this->is_set($product_hash['unit'])) { $product->column_fields['qty_per_unit'] = $product_hash['unit']; }
    if($this->is_set($product_hash['unit_type'])) { $product->column_fields['usageunit'] = $product_hash['unit_type']; }

    if($this->is_set($product_hash['sale_price']) && $this->is_set($product_hash['sale_price']['net_amount'])) {
      $product->column_fields['unit_price'] = $product_hash['sale_price']['net_amount'];
    }
  }

  // Map the vTiger Product to a Connec resource hash
  protected function mapModelToConnecResource($product) {
    $product_hash = array();

    // Default product type to PURCHASED on creation
    if($this->is_new($product)) { $product_hash['type'] = 'PURCHASED'; }

    // Map attributes
    if($this->is_set($product->column_fields['product_no'])) { $product_hash['code'] = $product->column_fields['product_no']; }
    if($this->is_set($product->column_fields['productname'])) { $product_hash['name'] = $product->column_fields['productname']; }
    if($this->is_set($product->column_fields['description'])) { $product_hash['description'] = $product->column_fields['description']; }
    if($this->is_set($product->column_fields['productcode'])) { $product_hash['reference'] = $product->column_fields['productcode']; }
    if($this->is_set($product->column_fields['qty_per_unit'])) { $product_hash['unit'] = $product->column_fields['qty_per_unit']; }
    if($this->is_set($product->column_fields['usageunit'])) { $product_hash['unit_type'] = $product->column_fields['usageunit']; }

    if($this->is_set($product->column_fields['unit_price'])) { $product_hash['sale_price'] = array('net_amount' => $product->column_fields['unit_price']); }

    ProductMapper::mapTaxToConnecResource($product, $product_hash);
    ProductMapper::mapAccountToConnecResource($product, $product_hash);

    return $product_hash;
  }

  // Persist the vTiger Product
  protected function persistLocalModel($product, $product_hash) {
    ProductMapper::mapConnecAccountToProduct($product_hash, $product);

    $product->save("Products", $product->id, false);

    ProductMapper::mapConnecTaxToProduct($product_hash, $product);
  }

  // Save sales tax against product
  public static function mapConnecTaxToProduct($product_hash, &$product) {
    global $adb;

    if(!array_key_exists('sale_tax_code_id', $product_hash)) { return null; }

    $sale_tax_code_id = $product_hash['sale_tax_code_id'];
    $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($sale_tax_code_id, 'TAXCODE');
    if($mno_id_map) {
      $tax_id = $mno_id_map['app_entity_id'];
      $tax = Settings_Vtiger_TaxRecord_Model::getInstanceById($tax_id, Settings_Vtiger_TaxRecord_Model::PRODUCT_AND_SERVICE_TAX);
      
      // Delete existing Tax
      $query = "DELETE FROM vtiger_producttaxrel WHERE productid=? AND taxid=?";
      $adb->pquery($query, array($product->id, $tax_id));

      // Insert Tax for this product
      $query = "INSERT INTO vtiger_producttaxrel VALUES(?,?,?)";
      $adb->pquery($query, array($product->id, $tax_id, $tax->getTax()));
    }
  }

  // Save product account
  public static function mapConnecAccountToProduct($product_hash, &$product) {
    global $adb;
    if(!array_key_exists('sale_account_id', $product_hash)) { return null; }

    $account_mno_id = $product_hash['sale_account_id'];
    $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($account_mno_id, 'ACCOUNT');
    if($mno_id_map) {
      $account_id = $mno_id_map['app_entity_id'];
      $account = AccountMapper::getAccountById($account_id);
      $product->column_fields['glacct'] = $account['glacct'];
    }
  }

  // Add tax to product hash
  public static function mapTaxToConnecResource($product, &$product_hash) {
    global $adb;

    // Select first product tax
    $query = "SELECT * FROM vtiger_producttaxrel WHERE productid=? LIMIT 1";
    $result = $adb->pquery($query, array($product->id));
    if($result) {
      $tax_id = $result->fields['taxid'];
      // Map connec tax id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($tax_id, 'TAXRECORD');
      if($mno_id_map) { $product_hash['sale_tax_code_id'] = $mno_id_map['mno_entity_guid']; }
    }
  }

  // Add account to product hash
  public static function mapAccountToConnecResource($product, &$product_hash) {
    global $adb;

    // Find account
    $account = AccountMapper::getAccountByName($product->column_fields['glacct']);
    if($account) {
      // Map connec account id
      $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($account['glacctid'], 'GLACCOUNT');
      if($mno_id_map) { $product_hash['sale_account_id'] = $mno_id_map['mno_entity_guid']; }
    }
  }
}
