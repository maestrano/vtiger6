<?php

/**
* Map Connec Company representation to/from vTiger Company
*/
class CompanyMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Company';
    $this->local_entity_name = 'Company';
    $this->connec_resource_name = 'company';
    $this->connec_resource_endpoint = 'company';
  }

  public function getId($company) {
    return $company['organization_id'];
  }

  // Find by local id
  public function loadModelById($local_id) {
    global $adb;
    $result = $adb->pquery('SELECT * FROM vtiger_organizationdetails LIMIT 1');
    return $result->fields;
  }

  // Return first company
  public function matchLocalModel($company_hash) {
    global $adb;
    $result = $adb->pquery('SELECT * FROM vtiger_organizationdetails LIMIT 1');
    return $result->fields;
  }

  // Map the Connec resource attributes onto the vTiger Company
  protected function mapConnecResourceToModel($company_hash, $company) {
    global $adb;

    $organizationname = $company_hash['name'];
    $vatid = $company_hash['tax_number'];
    $address = $company_hash['address']['shipping']['line1'];
    $city = $company_hash['address']['shipping']['city'];
    $state = $company_hash['address']['shipping']['region'];
    $code = $company_hash['address']['shipping']['postal_code'];
    $country = $company_hash['address']['shipping']['country'];
    $phone = $company_hash['phone']['landline'];
    $fax = $company_hash['phone']['fax'];
    $website = $company_hash['website']['url'];

    $query = "UPDATE vtiger_organizationdetails SET organizationname = ?, address = ?, city = ?, state = ?, code = ?, country = ?, phone = ?, fax = ?, website = ?, vatid = ?";
    $params = array($organizationname, $address, $city, $state, $code, $country, $phone, $fax, $website, $vatid);
    $adb->pquery($query, $params);

    $this->saveLogo($company_hash['logo']['logo']);
    $this->saveCurrency($company_hash['currency']);
  }

  // Map the vTiger Company to a Connec resource hash
  protected function mapModelToConnecResource($company) {
    $company_hash = array();

    // Map Company to Connec hash
    $company_hash['name'] = $company['organizationname'];
    $company_hash['tax_number'] = $company['vatid'];

    $company_hash['address'] = array('shipping' => array());
    $company_hash['address']['shipping']['line1'] = $company['address'];
    $company_hash['address']['shipping']['city'] = $company['city'];
    $company_hash['address']['shipping']['region'] = $company['state'];
    $company_hash['address']['shipping']['postal_code'] = $company['code'];
    $company_hash['address']['shipping']['country'] = $company['country'];

    $company_hash['phone'] = array();
    $company_hash['phone']['landline'] = $company['phone'];
    $company_hash['phone']['fax'] = $company['fax'];

    $company_hash['website'] = array('url' => $company['website']);

    return $company_hash;
  }

  // Persist the vTiger Company
  protected function persistLocalModel($company, $resource_hash) {
    $company->Save(true, false, false);
  }

  protected function saveLogo($logo_url) {
    global $root_directory;
    global $adb;

    if(isset($logo_url)) {
      // Save logo file locally
      $path = $root_directory . "test/logo/";
      $filename = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . '.jpg';
      $tmpLogoFilePath = $path . $filename;
      file_put_contents($tmpLogoFilePath, file_get_contents($logo_url));

      $sql="UPDATE vtiger_organizationdetails SET logoname = ?";
      $params = array($filename);
      $adb->pquery($sql, $params);
    }
  }

  protected function saveCurrency($currency) {
    global $adb;

    $result = $adb->pquery("SELECT id FROM vtiger_currency_info WHERE currency_code=?", array($currency));
    if($result->_numOfRows > 0) {
      error_log("currency " . json_encode($currency) . " already exists");
    } else {
      // Fetch currency details
      $result_currency = $adb->pquery("SELECT * FROM vtiger_currencies WHERE currency_code=?", array($currency));
      if($result_currency->_numOfRows > 0) {
        $currencyid = $adb->query_result($result_currency,0,'currencyid');
        $currency_name = $adb->query_result($result_currency,0,'currency_name');
        $currency_code = $adb->query_result($result_currency,0,'currency_code');
        $currency_symbol = $adb->query_result($result_currency,0,'currency_symbol');

        // Insert new company currency
        $sql = "INSERT INTO vtiger_currency_info (id, currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted) VALUES(?,?,?,?,?,?,?,?)";
        $params = array($adb->getUniqueID("vtiger_currency_info"), $currency_name, $currency_code, $currency_symbol, 1, 'Active','0','0');
        $adb->pquery($sql, $params);
      } else {
        error_log("currency with code " . json_encode($currency) . " not found in vTiger");
      }
    }
  }
}