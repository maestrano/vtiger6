<?php

/**
* Map Connec Tax Tax representation to/from vTiger Tax
*/
class TaxMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'TaxCode';
    $this->local_entity_name = 'TaxRecord';
    $this->connec_resource_name = 'tax_codes';
    $this->connec_resource_endpoint = 'tax_codes';
  }

  // Return the Tax local id
  protected function getId($tax) {
    return $tax->get('id');
  }

  // Return a local Tax by id
  protected function loadModelById($local_id) {
    return Settings_Vtiger_TaxRecord_Model::getInstanceById($local_id, Settings_Vtiger_TaxRecord_Model::PRODUCT_AND_SERVICE_TAX);
  }

  // Create a new TaxModel object
  protected function matchLocalModel($resource_hash) {
    return new Settings_Vtiger_TaxRecord_Model();
  }

  // Map the Connec resource attributes onto the vTiger Tax
  protected function mapConnecResourceToModel($tax_hash, $tax) {
    if(!$this->is_set($tax->getType())) { $tax->setType(Settings_Vtiger_TaxRecord_Model::PRODUCT_AND_SERVICE_TAX); }
    if($this->is_set($tax_hash['code'])) { $tax->set('taxname', $tax_hash['code']); }
    if($this->is_set($tax_hash['name'])) { $tax->set('taxlabel', $tax_hash['name']); }
    if($this->is_set($tax_hash['sale_tax_rate'])) { $tax->set('percentage', $tax_hash['sale_tax_rate']); }
  }

  // Map the vTiger Tax to a Connec resource hash
  protected function mapModelToConnecResource($tax) {
    $tax_hash = array();
    if($this->is_set($tax->get('taxname'))) { $tax_hash['code'] = $tax->get('taxname'); }
    if($this->is_set($tax->get('taxlabel'))) { $tax_hash['name'] = $tax->get('taxlabel'); }
    if($this->is_set($tax->get('percentage'))) { $tax_hash['sale_tax_rate'] = $tax->get('percentage'); }
    return $tax_hash;
  }

  // Persist the vTiger Tax
  protected function persistLocalModel($tax, $resource_hash) {
    $tax_id = $tax->save(false);
    $tax->set('id', $tax_id);
  }

  public static function getTaxByName($taxname) {
    global $adb;
    $result = $adb->pquery('SELECT * FROM vtiger_inventorytaxinfo WHERE taxname=? LIMIT 1', array($taxname));
    if($result) { return $result->fields; }
    return null;
  }
}