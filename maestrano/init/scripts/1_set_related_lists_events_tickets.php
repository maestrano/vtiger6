<?php

// Create related lists links between modules
createRelateList('EventManagement', 'Contacts', 'Contacts');
createRelateList('EventManagement', 'Leads', 'Leads');
createRelateList('EventTicket', 'Contacts', 'Contacts');
createRelateList('EventTicket', 'Leads', 'Leads');
createRelateList('Contacts', 'EventManagement', 'EventManagement');
createRelateList('Leads', 'EventManagement', 'EventManagement');
createRelateList('Contacts', 'EventTicket', 'EventTicket');
createRelateList('Leads', 'EventTicket', 'EventTicket');

// Initialise numbering sequence
global $adb;
$adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'EventManagement', 'EV' ,1 ,1 ,1));
$adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'EventTicket', 'TI' ,1 ,1 ,1));

function createRelateList($tabname, $targetModule, $relationLabel) {
  $moduleInstance = Vtiger_Module::getInstance($tabname);
  $targetModuleInstance = Vtiger_Module::getInstance($targetModule);
  $relationLabel = $relationLabel;
  $moduleInstance->unsetRelatedList($targetModuleInstance);
  $moduleInstance->setRelatedList($targetModuleInstance, $relationLabel, Array('SELECT'));
}