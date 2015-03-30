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

    // Map attributes
    if($this->is_set($product->column_fields['product_no'])) { $product_hash['code'] = $product->column_fields['product_no']; }
    if($this->is_set($product->column_fields['productname'])) { $product_hash['name'] = $product->column_fields['productname']; }
    if($this->is_set($product->column_fields['description'])) { $product_hash['description'] = $product->column_fields['description']; }
    if($this->is_set($product->column_fields['productcode'])) { $product_hash['reference'] = $product->column_fields['productcode']; }
    if($this->is_set($product->column_fields['qty_per_unit'])) { $product_hash['unit'] = $product->column_fields['qty_per_unit']; }
    if($this->is_set($product->column_fields['usageunit'])) { $product_hash['unit_type'] = $product->column_fields['usageunit']; }

    if($this->is_set($product->column_fields['unit_price'])) { $product_hash['sale_price'] = array('net_amount' => $product->column_fields['unit_price']); }

    return $product_hash;
  }

  // Persist the vTiger Product
  protected function persistLocalModel($product, $resource_hash) {
    $product->save("Products", $product->id, false);
  }
}
