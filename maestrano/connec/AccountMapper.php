<?php

/**
* Map Connec Account Account representation to/from vTiger General Ledger Account
* Note that there is not Module for General Ledger Accounts, we use a generic PHP object
*/
class AccountMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Account';
    $this->local_entity_name = 'GLAccount';
    $this->connec_resource_name = 'accounts';
    $this->connec_resource_endpoint = 'accounts';
  }

  // Return the Account local id
  protected function getId($account) {
    return $account->id;
  }

  // Initialize a generic Object with ID
  protected function loadModelById($local_id) {
    $account = (object) array();
    $account->id = $local_id;
    return $account;
  }

  // Initialize a generic Object without ID
  protected function matchLocalModel($resource_hash) {
    return (object) array();
  }

  // Map the Connec resource attributes onto the vTiger Account
  protected function mapConnecResourceToModel($account_hash, $account) {
    // Map hash attributes to Account
    if($this->is_set($account_hash['name'])) { $account->name = $account_hash['name']; }
  }

  // Map the vTiger Account to a Connec resource hash
  protected function mapModelToConnecResource($account) {
    $account_hash = array();

    // Map attributes
    if($this->is_set($account->name)) { $account_hash['name'] = $account->name; }

    return $account_hash;
  }

  // Persist the vTiger Account
  protected function persistLocalModel($account, $resource_hash) {
    global $adb;
    if(is_null($account->id)) {
      // Save the account as a picklist value
      $picklist_value = getUniquePicklistID();
      
      // Pick list order
      $result = $adb->pquery('SELECT max(sortorderid) as maxsequence FROM vtiger_glacct');
      $sequence = $adb->query_result($result, 0, 'maxsequence');
      if(is_null($sequence)) { $sequence = 0; }
      
      // Insert new value
      $adb->pquery("INSERT INTO vtiger_glacct (glacct, presence, picklist_valueid, sortorderid) VALUES ('".$account->name."',1,".$picklist_value.",".++$sequence.")");

      // Map generated ID
      $result = $adb->pquery("SELECT max(glacctid) as id FROM vtiger_glacct");
      $id = $result->fields['id'];
      $account->id = $id;

      // Add picklist values
      $result = $adb->pquery("SELECT picklistid FROM vtiger_picklist WHERE name=?", array('glacct'));
      $picklistid = $result->fields['picklistid'];

      $result = $adb->pquery("SELECT roleid FROM vtiger_role");
      $numrow = $adb->num_rows($result);
      for($k=0; $k < $numrow; $k ++) {
        $roleid = $adb->query_result($result, $k, 'roleid');
        $params = array($roleid, $picklist_value, $picklistid, $sequence);
        $adb->pquery("INSERT INTO vtiger_role2picklist VALUES(?,?,?,?)", $params);
      }
    } else {
      // Update account
      $query = "UPDATE vtiger_glacct SET name = '".$account->name."' WHERE glacctid = ".$account->id;  
      $adb->pquery($query);
    }
  }

  public static function getAccountById($account_id) {
    global $adb;
    $result = $adb->pquery('SELECT * FROM vtiger_glacct WHERE glacctid=? LIMIT 1', array($account_id));
    if($result) { return $result->fields; }
    return null;
  }

  public static function getAccountByName($account_name) {
    global $adb;
    $result = $adb->pquery('SELECT * FROM vtiger_glacct WHERE glacct=? LIMIT 1', array($account_name));
    if($result) { return $result->fields; }
    return null;
  }
}